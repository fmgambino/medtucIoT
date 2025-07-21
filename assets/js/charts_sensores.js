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
      // DHT22 → Temp/Hum en un único fetch tempHum
      canvasId:   'chartDHT',
      sensorType: 'tempHum',
      datasets: [
        { label:'Temp (°C)', data:[], fill:false },
        { label:'Hum (%)',   data:[], fill:false }
      ]
    },
    {
      // MQ135 → 4 gases en un único fetch mq135
      canvasId:   'chartMQ135',
      sensorType: 'mq135',
      datasets: [
        { label:'CO₂ (ppm)',    data:[], fill:false },
        { label:'Metano (ppm)', data:[], fill:false },
        { label:'Butano (ppm)', data:[], fill:false },
        { label:'Propano (ppm)',data:[], fill:false }
      ]
    },
    // pH en su propio gadget
    {
      canvasId:   'chartPH',
      sensorType: 'ph',
      datasets: [
        { label:'pH', data:[], fill:false }
      ]
    },
    // EC en su propio gadget
    {
      canvasId:   'chartEC',
      sensorType: 'ec',
      datasets: [
        { label:'EC (μS/cm)', data:[], fill:false }
      ]
    },
    // Humedad de Suelo
    {
  canvasId:   'chartSoilHum',
  sensorType: 'soilHum',
  datasets:   [{ label:'Hum. Suelo (%)', data:[], fill:false }]
},

    {
      canvasId:   'chartH2O',
      sensorType: 'h2o',
      datasets: [
        { label:'Nivel H₂O (%)', data:[], fill:false }
      ]
    },
    {
      canvasId:   'chartNafta',
      sensorType: 'nafta',
      datasets: [
        { label:'Nafta (%)', data:[], fill:false }
      ]
    },
    {
      canvasId:   'chartAceite',
      sensorType: 'aceite',
      datasets: [
        { label:'Aceite (%)', data:[], fill:false }
      ]
    }
  ];

  // crear instancias Chart.js
  chartsConfig.forEach(cfg => {
    const cvs = document.getElementById(cfg.canvasId);
    if (!cvs) {
      console.warn(`⚠️ No se encontró <canvas id="${cfg.canvasId}">`);
      return;
    }
    cfg.chart = new Chart(cvs.getContext('2d'), {
      type: 'line',
      data: { labels: [], datasets: cfg.datasets },
      options: { responsive:true }
    });
  });

  // función genérica para cargar cada gráfico
  async function loadChart(cfg) {
    if (!cfg.chart || !currentDeviceId) return;
    const date = fechaInput ? fechaInput.value : today;
    const url  = `${baseApi}/get_history.php?deviceId=${encodeURIComponent(currentDeviceId)}` +
                 `&sensorType=${encodeURIComponent(cfg.sensorType)}` +
                 `&date=${encodeURIComponent(date)}`;

    // depuración SoilHum
    if (cfg.sensorType === 'soilHum') console.log('🔍 Fetch SoilHum URL:', url);

    let rows = [];
    try {
      const res = await fetch(url);
      rows = await res.json();
    } catch (e) {
      console.error(`❌ Error fetch ${cfg.sensorType}:`, e);
      return;
    }

    if (cfg.sensorType === 'soilHum') console.log('📊 SoilHum data:', rows);

    // ordenar por timestamp ascendente
    rows.sort((a,b) => new Date(a.timestamp) - new Date(b.timestamp));

    // preparar labels y series
    const labels = [];
    const series = cfg.datasets.map(()=>[]);

    rows.forEach(item => {
      const dt   = new Date(item.timestamp);
      const hhmm = `${String(dt.getHours()).padStart(2,'0')}:` +
                   `${String(dt.getMinutes()).padStart(2,'0')}`;
      if (!labels.includes(hhmm)) labels.push(hhmm);

      if (cfg.sensorType === 'tempHum') {
        // Temp vs Hum según unidad
        if (item.unit === '°C') series[0].push(item.value);
        else                    series[1].push(item.value);
      }
      else if (cfg.sensorType === 'mq135') {
        // gases según sensor_type
        const idxMap = { co2:0, methane:1, butane:2, propane:3 };
        const idx    = idxMap[item.sensor_type];
        if (idx != null) series[idx].push(item.value);
      }
      else {
        // sensores simples (incluye soilHum)
        series[0].push(item.value);
      }
    });

    // actualizar gráfico
    cfg.chart.data.labels = labels;
    cfg.chart.data.datasets.forEach((ds,i) => ds.data = series[i]);
    cfg.chart.update();
  }

  // carga inicial y al cambiar fecha
  chartsConfig.forEach(loadChart);
  if (fechaInput) {
    fechaInput.addEventListener('change', () => {
      chartsConfig.forEach(loadChart);
    });
  }

  // recarga periódica en vivo
  setInterval(() => chartsConfig.forEach(loadChart), 50);

  // histórico en popup con SweetAlert2
  document.querySelectorAll('.chart-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      const key = icon.dataset.sensor;
      const cfg = chartsConfig.find(c => c.sensorType === key);
      if (!cfg) return;

      Swal.fire({
        title: `Gráfico ${cfg.sensorType}`,
        html: `
          <div style="text-align:left; margin-bottom:1rem;">
            <label for="modalDate">Fecha:</label>
            <input type="date" id="modalDate" class="swal2-input" style="width:auto;" value="${today}">
          </div>
          <canvas id="modalChart" width="600" height="400"></canvas>
        `,
        width: '650px',
        showCloseButton: true,
        didOpen: () => {
          // usar getElementById para asegurar existencia
          const dateInput = document.getElementById('modalDate');
          if (!dateInput) {
            console.error('❗ No se encontró #modalDate');
            return;
          }
          const canvas = document.getElementById('modalChart');
          if (!canvas) {
            console.error('❗ No se encontró #modalChart');
            return;
          }
          const ctx = canvas.getContext('2d');
          const popupDatasets = cfg.datasets.map(d => ({ ...d, data: [] }));
          const popupChart = new Chart(ctx, {
            type: 'line',
            data: { labels: [], datasets: popupDatasets },
            options: { responsive: true, animation: { duration: 0 } }
          });

          async function reloadPopup() {
            const url2 = `${baseApi}/get_history.php?deviceId=${encodeURIComponent(currentDeviceId)}` +
                         `&sensorType=${encodeURIComponent(cfg.sensorType)}` +
                         `&date=${encodeURIComponent(dateInput.value)}`;
            let pd = [];
            try {
              const res = await fetch(url2);
              pd = await res.json();
            } catch (e) {
              console.error(`❌ Error popup fetch ${cfg.sensorType}:`, e);
              return;
            }
            pd.sort((a,b) => new Date(a.timestamp) - new Date(b.timestamp));

            const labels2 = [];
            const series2 = popupDatasets.map(()=>[]);
            pd.forEach(item => {
              const dt   = new Date(item.timestamp);
              const hhmm = `${String(dt.getHours()).padStart(2,'0')}:` +
                           `${String(dt.getMinutes()).padStart(2,'0')}`;
              if (!labels2.includes(hhmm)) labels2.push(hhmm);

              if (cfg.sensorType === 'tempHum') {
                (item.unit === '°C' ? series2[0] : series2[1]).push(item.value);
              }
              else if (cfg.sensorType === 'mq135') {
                const idxMap = { co2:0, methane:1, butane:2, propane:3 };
                const idx    = idxMap[item.sensor_type];
                if (idx != null) series2[idx].push(item.value);
              }
              else {
                series2[0].push(item.value);
              }
            });

            popupChart.data.labels   = labels2;
            popupChart.data.datasets.forEach((d,i) => d.data = series2[i] || []);
            popupChart.update();
          }

          reloadPopup();
          let iv = null;
          if (dateInput.value === today) iv = setInterval(reloadPopup, 5000);
          dateInput.addEventListener('change', () => {
            if (iv) clearInterval(iv);
            reloadPopup();
          });
        }
      });
    });
  });

});
