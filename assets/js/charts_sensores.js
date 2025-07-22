// assets/js/charts_sensores.js
document.addEventListener('DOMContentLoaded', () => {
  const baseApi         = `${BASE_PATH}/app`;
  const deviceIdInput   = document.getElementById('deviceId');
  const currentDeviceId = deviceIdInput ? deviceIdInput.value : null;
  const fechaInput      = document.getElementById('fecha');
  const today           = new Date().toISOString().slice(0,10);

  if (fechaInput) fechaInput.value = today;

  const chartsConfig = [
    {
      canvasId:   'chartDHT',
      sensorType: 'tempHum',
      datasets: [
        { label:'Temp (°C)', data:[], fill:false },
        { label:'Hum (%)',   data:[], fill:false }
      ]
    },
    {
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
      canvasId:   'chartPHec',
      sensorTypes:['ph','ec'],
      datasets: [
        { label:'pH',         data:[], fill:false },
        { label:'EC (μS/cm)', data:[], fill:false }
      ]
    },
    { canvasId:'chartSoilHum', sensorTypes:['soilHum'], datasets:[{ label:'Hum. Suelo (%)', data:[], fill:false }] },
    { canvasId:'chartH2O',     sensorTypes:['h2o'],     datasets:[{ label:'Nivel H₂O (%)',  data:[], fill:false }] },
    { canvasId:'chartNafta',   sensorTypes:['nafta'],   datasets:[{ label:'Nafta (%)',      data:[], fill:false }] },
    { canvasId:'chartAceite',  sensorTypes:['aceite'],  datasets:[{ label:'Aceite (%)',     data:[], fill:false }] }
  ];

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

  async function loadChart(cfg) {
    if (!cfg.chart || !currentDeviceId) return;
    const date = fechaInput ? fechaInput.value : today;
    let allData = [];

    if (cfg.sensorType) {
      try {
        const res = await fetch(
          `${baseApi}/get_history.php?deviceId=${currentDeviceId}&sensorType=${cfg.sensorType}&date=${date}`
        );
        const data = await res.json();
        allData = data.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));
      } catch(e) {
        console.error(e);
      }
    } else {
      await Promise.all(cfg.sensorTypes.map(async type => {
        try {
          const res = await fetch(
            `${baseApi}/get_history.php?deviceId=${currentDeviceId}&sensorType=${type}&date=${date}`
          );
          const data = await res.json();
          allData.push(...data);
        } catch(e) {
          console.error(e);
        }
      }));
      allData.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));
    }

    const labels = [];
    const series = cfg.datasets.map(()=> []);

    allData.forEach(item => {
        const dtLocal = new Date(new Date(item.timestamp).toLocaleString("en-US", { timeZone: "America/Argentina/Tucuman" }));
        const hhmm = `${String(dtLocal.getHours()).padStart(2, '0')}:${String(dtLocal.getMinutes()).padStart(2, '0')}`;


      if (cfg.sensorType === 'tempHum') {
        try {
          const parsed = typeof item.value === 'string' ? JSON.parse(item.value) : item.value;
          if (parsed?.temperature !== undefined && parsed?.humidity !== undefined) {
            labels.push(hhmm);
            series[0].push(parsed.temperature);
            series[1].push(parsed.humidity);
          }
        } catch (e) {
          console.warn('❌ Error al parsear tempHum:', item.value);
        }
      }

      else if (cfg.sensorType === 'mq135') {
        try {
          const parsed = typeof item.value === 'string' ? JSON.parse(item.value) : item.value;
          if (parsed?.co2 || parsed?.methane || parsed?.butane || parsed?.propane) {
            labels.push(hhmm);
            series[0].push(parsed?.co2 ?? null);
            series[1].push(parsed?.methane ?? null);
            series[2].push(parsed?.butane ?? null);
            series[3].push(parsed?.propane ?? null);
          }
        } catch (e) {
          console.warn('❌ Error al parsear mq135:', item.value);
        }
      }

      else if (cfg.sensorTypes) {
        const i = cfg.sensorTypes.indexOf(item.sensor_type);
        if (i >= 0) {
          if (!labels.includes(hhmm)) labels.push(hhmm);
          series[i].push(item.value);
        }
      }
    });

    cfg.chart.data.labels = labels;
    cfg.chart.data.datasets.forEach((ds,i)=> ds.data = series[i]);
    cfg.chart.update();
  }

  chartsConfig.forEach(loadChart);
  if (fechaInput) {
    fechaInput.addEventListener('change', ()=> {
      chartsConfig.forEach(loadChart);
    });
  }

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

          async function loadPopup() {
            try {
              const res  = await fetch(
                `${baseApi}/get_history.php?deviceId=${currentDeviceId}&sensorType=${sensor}&date=${dateInput.value}`
              );
              const data = await res.json();
              data.sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));

const labels = data.map(i => {
  const dt = new Date(i.timestamp);
  return `${String(dt.getHours()).padStart(2,'0')}:${String(dt.getMinutes()).padStart(2,'0')}`;
});


              let series;
              if (sensor === 'tempHum') {
                const t=[], h=[];
                data.forEach(i => {
                  try {
                    const parsed = typeof i.value === 'string' ? JSON.parse(i.value) : i.value;
                    t.push(parsed?.temperature ?? null);
                    h.push(parsed?.humidity ?? null);
                  } catch(e) {
                    console.warn('Error parseando tempHum en popup:', i.value);
                  }
                });
                series = [t,h];
              }
              else if (sensor === 'mq135') {
                const co2=[], me=[], bu=[], pr=[];
                data.forEach(i => {
                  try {
                    const parsed = typeof i.value === 'string' ? JSON.parse(i.value) : i.value;
                    co2.push(parsed?.co2 ?? null);
                    me.push(parsed?.methane ?? null);
                    bu.push(parsed?.butane ?? null);
                    pr.push(parsed?.propane ?? null);
                  } catch(e) {
                    console.warn('Error parseando mq135 en popup:', i.value);
                  }
                });
                series = [co2, me, bu, pr];
              }
              else {
                series = [ data.map(i=>i.value) ];
              }

              chartPopup.data.labels = labels;
              chartPopup.data.datasets.forEach((ds,i)=> ds.data = series[i] || []);
              chartPopup.update();
            }
            catch(e){ console.error(e); }
          }

          await loadPopup();
          let iv = null;
          if (dateInput.value === today) {
            iv = setInterval(loadPopup, 5000);
          }
          dateInput.addEventListener('change', ()=> {
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
  }, 5000);
});
