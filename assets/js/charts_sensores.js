// assets/js/charts_sensores.js
document.addEventListener('DOMContentLoaded', () => {
  const baseApi         = `${BASE_PATH}/app`;
  const deviceIdInput   = document.getElementById('deviceId');
  const currentDeviceId = deviceIdInput ? deviceIdInput.value : null;
  const fechaInput      = document.getElementById('fecha');
  const today           = new Date().toISOString().slice(0,10);

  if (fechaInput) fechaInput.value = today;

  // configuración de todos los gráficos
  const chartsConfig = [
    {
      // DHT22 → JSON tempHum → dos series (Temp/Hum)
      canvasId:   'chartDHT',
      sensorType: 'tempHum',
      datasets: [
        { label:'Temp (°C)', data:[], fill:false },
        { label:'Hum (%)',   data:[], fill:false }
      ]
    },
    {
      // MQ135 → JSON mq135 → cuatro series (CO₂/Met/But/Prop)
      canvasId:   'chartMQ135',
      sensorType: 'mq135',
      datasets: [
        { label:'CO₂ (ppm)',    data:[], fill:false },
        { label:'Metano (ppm)', data:[], fill:false },
        { label:'Butano (ppm)', data:[], fill:false },
        { label:'Propano (ppm)',data:[], fill:false }
      ]
    },
    {
      // pH + EC → dos tipos separados
      canvasId:   'chartPHec',
      sensorTypes:['ph','ec'],
      datasets: [
        { label:'pH',         data:[], fill:false },
        { label:'EC (μS/cm)', data:[], fill:false }
      ]
    },
    // Sensores individuales
    { canvasId:'chartSoilHum', sensorTypes:['soilHum'], datasets:[{ label:'Hum. Suelo (%)', data:[], fill:false }] },
    { canvasId:'chartH2O',     sensorTypes:['h2o'],     datasets:[{ label:'Nivel H₂O (%)',  data:[], fill:false }] },
    { canvasId:'chartNafta',   sensorTypes:['nafta'],   datasets:[{ label:'Nafta (%)',      data:[], fill:false }] },
    { canvasId:'chartAceite',  sensorTypes:['aceite'],  datasets:[{ label:'Aceite (%)',     data:[], fill:false }] }
  ];

  // crear instancias Chart.js
  chartsConfig.forEach(cfg => {
    const cvs = document.getElementById(cfg.canvasId);
    if (!cvs) return;
    const ctx = cvs.getContext('2d');
    cfg.chart = new Chart(ctx, {
      type: 'line',
      data:   { labels: [], datasets: cfg.datasets },
      options:{ responsive:true }
    });
  });

  // función genérica para cargar cada gráfico
  async function loadChart(cfg) {
    if (!cfg.chart || !currentDeviceId) return;
    const date = fechaInput ? fechaInput.value : today;
    let allData = [];

    // JSON únicos: tempHum o mq135
    if (cfg.sensorType) {
      try {
        const res = await fetch(
          `${baseApi}/get_history.php?deviceId=${currentDeviceId}` +
          `&sensorType=${cfg.sensorType}&date=${date}`
        );
        const data = await res.json();
        allData = data.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));
      } catch(e) {
        console.error(e);
      }
    }
    else {
      // uno o varios sensorTypes
      await Promise.all(cfg.sensorTypes.map(async type => {
        try {
          const res = await fetch(
            `${baseApi}/get_history.php?deviceId=${currentDeviceId}` +
            `&sensorType=${type}&date=${date}`
          );
          const data = await res.json();
          allData.push(...data);
        } catch(e) {
          console.error(e);
        }
      }));
      allData.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));
    }

    // preparar labels (HH:MM) y series
    const labels = [];
    const series = cfg.datasets.map(()=> []);

    allData.forEach(item => {
      const dt   = new Date(item.timestamp);
      const hhmm = `${String(dt.getHours()).padStart(2,'0')}:${String(dt.getMinutes()).padStart(2,'0')}`;
      if (!labels.includes(hhmm)) labels.push(hhmm);

      if (cfg.sensorType === 'tempHum') {
        // JSON tempHum
        item.unit === '°C'
          ? series[0].push(item.value)
          : series[1].push(item.value);
      }
      else if (cfg.sensorType === 'mq135') {
        // JSON mq135 → sensor_type: 'co2','methane','butane','propane'
        const idxMap = { co2:0, methane:1, butane:2, propane:3 };
        const i = idxMap[item.sensor_type];
        if (i >= 0) series[i].push(item.value);
      }
      else if (cfg.sensorTypes) {
        // pH/EC o individuales
        const i = cfg.sensorTypes.indexOf(item.sensor_type);
        if (i >= 0) series[i].push(item.value);
      }
    });

    // actualizar gráfico
    cfg.chart.data.labels = labels;
    cfg.chart.data.datasets.forEach((ds,i)=> ds.data = series[i]);
    cfg.chart.update();
  }

  // carga inicial y al cambiar fecha
  chartsConfig.forEach(loadChart);
  if (fechaInput) {
    fechaInput.addEventListener('change', ()=> {
      chartsConfig.forEach(loadChart);
    });
  }

  // popups de cada widget
  document.querySelectorAll('.chart-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      const sensor = icon.dataset.sensor; 
      Swal.fire({
        title: `Gráfico ${sensor}`,
        html: `
          <div style="text-align:left; margin-bottom:1rem;">
            <label for="modalDate">Fecha:</label>
            <input type="date" id="modalDate" class="swal2-input" style="width:auto;" value="${today}">
          </div>
          <canvas id="modalChart" width="600" height="400"></canvas>
        `,
        width:'650px',
        showCloseButton:true,
        didOpen: async () => {
          const popup     = Swal.getPopup();
          const dateInput = popup.querySelector('#modalDate');
          const ctx       = popup.querySelector('#modalChart').getContext('2d');
          let chartPopup;

          // elegir datasets
          if (sensor === 'tempHum') {
            chartPopup = new Chart(ctx, {
              type:'line',
              data:{ labels:[], datasets:[
                { label:'Temp (°C)', data:[], fill:false },
                { label:'Hum (%)',   data:[], fill:false }
              ]},
              options:{ responsive:true, animation:{ duration:0 } }
            });
          }
          else if (sensor === 'mq135') {
            chartPopup = new Chart(ctx, {
              type:'line',
              data:{ labels:[], datasets:[
                { label:'CO₂ (ppm)',    data:[], fill:false },
                { label:'Metano (ppm)', data:[], fill:false },
                { label:'Butano (ppm)', data:[], fill:false },
                { label:'Propano (ppm)',data:[], fill:false }
              ]},
              options:{ responsive:true, animation:{ duration:0 } }
            });
          }
          else {
            chartPopup = new Chart(ctx, {
              type:'line',
              data:{ labels:[], datasets:[
                { label:sensor, data:[], fill:false }
              ]},
              options:{ responsive:true, animation:{ duration:0 } }
            });
          }

          // recargar datos en el popup
          async function loadPopup() {
            try {
              const res  = await fetch(
                `${baseApi}/get_history.php?deviceId=${currentDeviceId}` +
                `&sensorType=${sensor}&date=${dateInput.value}`
              );
              const data = await res.json();
              data.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));

              // labels
              const labels = data.map(i => {
                const dt = new Date(i.timestamp);
                return `${String(dt.getHours()).padStart(2,'0')}:` +
                       `${String(dt.getMinutes()).padStart(2,'0')}`;
              });

              // series
              let series;
              if (sensor === 'tempHum') {
                const t=[], h=[];
                data.forEach(i => i.unit==='°C' ? t.push(i.value) : h.push(i.value));
                series = [t,h];
              }
              else if (sensor === 'mq135') {
                const co2=[], me=[], bu=[], pr=[];
                data.forEach(i => {
                  if (i.sensor_type==='co2')    co2.push(i.value);
                  if (i.sensor_type==='methane') me.push(i.value);
                  if (i.sensor_type==='butane')  bu.push(i.value);
                  if (i.sensor_type==='propane') pr.push(i.value);
                });
                series = [co2,me,bu,pr];
              }
              else {
                series = [ data.map(i=>i.value) ];
              }

              chartPopup.data.labels = labels;
              chartPopup.data.datasets.forEach((ds,i)=> ds.data = series[i]||[]);
              chartPopup.update();
            }
            catch(e){ console.error(e); }
          }

          await loadPopup();
          let iv = null;
          if (dateInput.value === today) {
            iv = setInterval(loadPopup,5000);
          }
          dateInput.addEventListener('change',()=> {
            if (iv) clearInterval(iv);
            loadPopup();
          });
          popup.querySelector('.swal2-close')
               .addEventListener('click',()=> iv && clearInterval(iv));
        }
      });
    });
  });

  setInterval(() => {
  chartsConfig.forEach(loadChart);
}, 5000); // recarga cada 5 segundos


});
