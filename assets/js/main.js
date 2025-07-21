// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
  // — Sidebar / Drawer references —
  const logo         = document.getElementById('logo');
  const sidebar      = document.getElementById('sidebar');
  const mainContent  = document.getElementById('main-content');
  const hamburger    = document.getElementById('hamburger');
  const mobileDrawer = document.getElementById('mobileDrawer');

  // — Sidebar vs Drawer toggle —
  logo.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
      // en móvil clic al logo abre/cierra el drawer
      mobileDrawer.classList.toggle('open');
    } else {
      // en escritorio colapsa el sidebar
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
    }
  });

  // — Mobile drawer toggle con hamburguesa —
  if (hamburger && mobileDrawer) {
    hamburger.addEventListener('click', () => {
      mobileDrawer.classList.toggle('open');
    });
  }



  // --- Menu navigation ---
  document.querySelectorAll('.sidebar .menu li').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.sidebar .menu li')
        .forEach(i => i.classList.remove('active'));
      item.classList.add('active');
      document.querySelectorAll('.page')
        .forEach(p => p.classList.remove('active-page'));
      const pageId = item.dataset.page;
      if (pageId) document.getElementById(pageId).classList.add('active-page');
    });
  });

  // --- Theme toggle ---
  const themeIcon = document.getElementById('themeToggle');
  function applyTheme(dark) {
    document.body.classList.toggle('dark', dark);
    themeIcon.classList.toggle('ri-sun-line', !dark);
    themeIcon.classList.toggle('ri-moon-line', dark);
  }
  let darkMode = localStorage.getItem('theme') === 'dark';
  applyTheme(darkMode);
  themeIcon.addEventListener('click', () => {
    darkMode = !darkMode;
    localStorage.setItem('theme', darkMode ? 'dark' : 'light');
    applyTheme(darkMode);
  });
  // También en drawer móvil
  const themeIconDrawer = document.getElementById('themeToggleDrawer');
  if (themeIconDrawer) {
    themeIconDrawer.addEventListener('click', () => {
      themeIcon.click();
    });
  }

  // --- Language & Notifications ---
  document.getElementById('langToggle').addEventListener('click', () =>
    Swal.fire('Cambio de idioma', 'Aquí implementa ES/EN', 'info')
  );
  document.getElementById('notifToggle').addEventListener('click', () =>
    Swal.fire('Notificaciones', 'No hay nuevas notificaciones.', 'info')
  );
  // drawer
  const langDrawer  = document.getElementById('langToggleDrawer');
  const notifDrawer = document.getElementById('notifToggleDrawer');
  if (langDrawer)  langDrawer .addEventListener('click', () => document.getElementById('langToggle').click());
  if (notifDrawer) notifDrawer.addEventListener('click', () => document.getElementById('notifToggle').click());

  // --- Generator status simulation ---
  const genStatusEl = document.getElementById('genStatus');
  if (genStatusEl) {
    let genOn = false;
    setInterval(() => {
      genOn = !genOn;
      genStatusEl.textContent = genOn
        ? '⚡ Generador: Encendido'
        : '⚡ Generador: Apagado';
    }, 10000);
  }

  // --- Utilitario aleatorio ---
  function rand(min, max, decimals = 2) {
    return (Math.random() * (max - min) + min).toFixed(decimals);
  }

  // --- Simulación sensores ---
async function updateSensors() {
  try {
    const res = await fetch(
      `${BASE_PATH}/app/get_latest.php?deviceId=${currentDeviceId}`
    );
    if (!res.ok) throw '';
    const list = await res.json();
    list.forEach(s => {
      let id = s.sensor_type==='tempHum' ? 'tempVal' : s.sensor_type+'Val';
      // para hum de tempHum:
      if (s.sensor_type==='tempHum' && s.unit==='%') id = 'humVal';
      const el = document.getElementById(id);
      if (el) el.textContent = s.value;
    });
  } catch {
    console.warn('No hay datos reales todavía');
  }
}

updateSensors();
setInterval(updateSensors, 5000);

  // --- Historial de reinicios ---
  let rebootHistory = JSON.parse(localStorage.getItem('rebootHistory') || 'null');
  if (!Array.isArray(rebootHistory)) {
    rebootHistory = [
      { ts: '2025-07-19 19:10', reason: 'Mantenimiento' },
      { ts: '2025-07-18 14:22', reason: 'Actualización firmware' },
      { ts: '2025-07-17 09:05', reason: 'Caída de red' }
    ];
    localStorage.setItem('rebootHistory', JSON.stringify(rebootHistory));
  }
  document.getElementById('lastReset').textContent =
    `Último reset: ${rebootHistory[0].ts}`;

  document.getElementById('showReboots').addEventListener('click', () => {
    const icons = {
      'Mantenimiento':         '<i class="ri-wrench-line"></i>',
      'Actualización firmware':'<i class="ri-refresh-line"></i>',
      'Caída de red':          '<i class="ri-plug-off-line"></i>',
      'Software':              '<i class="ri-cpu-line"></i>',
      'Alimentacion':          '<i class="ri-flashlight-line"></i>',
      'Reset remoto':          '<i class="ri-restart-line"></i>'
    };
    const listHtml = rebootHistory.map(r =>
      `<li>${icons[r.reason] || '<i class="ri-time-line"></i>'} ${r.ts} – ${r.reason}</li>`
    ).join('');
    Swal.fire({
      title: 'Historial de Reinicios',
      html: `<ul style="text-align:left; list-style:none; padding:0">${listHtml}</ul>`,
      showCloseButton: true
    });
  });

  document.getElementById('doReboot').addEventListener('click', async () => {
    const { isConfirmed } = await Swal.fire({
      title: 'Confirmar reset remoto?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    });
    if (isConfirmed) {
      const now = new Date().toLocaleString('en-US', { timeZone: 'America/Argentina/Tucuman' });
      rebootHistory.unshift({ ts: now, reason: 'Reset remoto' });
      localStorage.setItem('rebootHistory', JSON.stringify(rebootHistory));
      document.getElementById('lastReset').textContent = `Último reset: ${now}`;
      Swal.fire('✅ Dispositivo reiniciado');
    }
  });

  // --- Actuadores ---
  let actuatorLogs = JSON.parse(localStorage.getItem('actuatorLogs') || 'null');
  if (!actuatorLogs || typeof actuatorLogs !== 'object') {
    actuatorLogs = { A1: [], A2: [], A3: [], A4: [] };
    localStorage.setItem('actuatorLogs', JSON.stringify(actuatorLogs));
  }
  document.querySelectorAll('.actuators input[type=checkbox]').forEach(cb => {
    const id   = cb.dataset.device;
    const logs = actuatorLogs[id] || [];
    cb.checked = logs.length ? logs[0].state === 'ON' : Math.random() < 0.5;
    cb.addEventListener('change', () => {
      const newState = cb.checked;
      Swal.fire({
        title: `¿Seguro que quieres ${newState ? 'ENCENDER' : 'APAGAR'} el actuador ${id}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
      }).then(({ isConfirmed }) => {
        if (isConfirmed) {
          const now = new Date().toLocaleString('en-US', { timeZone: 'America/Argentina/Tucuman' });
          actuatorLogs[id].unshift({ ts: now, state: newState ? 'ON' : 'OFF' });
          actuatorLogs[id] = actuatorLogs[id].slice(0, 20);
          localStorage.setItem('actuatorLogs', JSON.stringify(actuatorLogs));
          Swal.fire({
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1200,
            title: `Actuador ${id}: ${newState ? 'ON' : 'OFF'}`
          });
        } else {
          cb.checked = !newState;
        }
      });
    });
  });

  document.querySelectorAll('.info-act').forEach(btn => {
    btn.addEventListener('click', () => {
      const id  = btn.dataset.id;
      const log = actuatorLogs[id] || [];
      const list = log.map(e =>
        `<li><i class="ri-time-line"></i> ${e.ts} — ${e.state}</li>`
      ).join('');
      Swal.fire({
        title: 'Historial de Actuador',
        html: `<ul style="text-align:left; list-style:none; padding:0">${list}</ul>`,
        showCloseButton: true
      });
    });
  });

  document.querySelectorAll('.edit-act').forEach(btn => {
    btn.addEventListener('click', () => {
      Swal.fire({
        title: 'Nuevo nombre',
        input: 'text',
        inputValue: btn.dataset.name,
        showCancelButton: true
      }).then(res => {
        if (res.value) {
          const card = btn.closest('.actuator-card');
          card.querySelector('.actuator-header span').textContent = `🔌 ${res.value}`;
          btn.dataset.name = res.value;
        }
      });
    });
  });

  // --- Gráficos sensores ---
  const fechaInput = document.getElementById('fecha');
  if (fechaInput) {
    fechaInput.value = new Date().toISOString().slice(0,10);
    function genSeries(min, max, dec) {
      const labels = [], data = [];
      for (let h = 0; h < 24; h++) {
        labels.push(`${h}:00`);
        data.push(parseFloat(rand(min, max, dec)));
      }
      return { labels, data };
    }
    const ctxDHT  = document.getElementById('chartDHT')?.getContext('2d');
    const ctxMQ   = document.getElementById('chartMQ135')?.getContext('2d');
    const ctxPHec= document.getElementById('chartPHec')?.getContext('2d');
    if (ctxDHT && ctxMQ && ctxPHec) {
      const dht = genSeries(15,35,2),
            mq  = genSeries(300,2000,0),
            ph  = genSeries(4.0,9.0,2),
            ec  = genSeries(500,2000,0).data;
      const chartDHT = new Chart(ctxDHT, {
        type: 'line',
        data: { labels: dht.labels, datasets: [
          { label:'Temp (°C)', data:dht.data, fill:false },
          { label:'Hum (%)',   data:genSeries(20,80,2).data, fill:false }
        ]},
        options:{ responsive:true }
      });
      const chartMQ = new Chart(ctxMQ, {
        type: 'line',
        data: { labels: mq.labels, datasets: [
          { label:'CO₂ (ppm)',    data:mq.data, fill:false },
          { label:'Metano (ppm)', data:genSeries(0,200,0).data, fill:false },
          { label:'Butano (ppm)', data:genSeries(0,200,0).data, fill:false },
          { label:'Propano (ppm)',data:genSeries(0,200,0).data, fill:false }
        ]},
        options:{ responsive:true }
      });
      const chartPH = new Chart(ctxPHec, {
        type: 'line',
        data: { labels: ph.labels, datasets: [
          { label:'pH',        data:ph.data, fill:false },
          { label:'EC (μS/cm)',data:ec,     fill:false }
        ]},
        options:{ responsive:true }
      });
      fechaInput.addEventListener('change', () => {
        const ndht = genSeries(15,35,2),
              nmq  = genSeries(300,2000,0),
              nph  = genSeries(4.0,9.0,2),
              nec  = genSeries(500,2000,0).data;
        chartDHT.data.labels = ndht.labels;
        chartDHT.data.datasets[0].data = ndht.data;
        chartDHT.data.datasets[1].data = genSeries(20,80,2).data;
        chartDHT.update();
        chartMQ.data.labels = nmq.labels;
        chartMQ.data.datasets[0].data = nmq.data;
        chartMQ.data.datasets[1].data = genSeries(0,200,0).data;
        chartMQ.data.datasets[2].data = genSeries(0,200,0).data;
        chartMQ.data.datasets[3].data = genSeries(0,200,0).data;
        chartMQ.update();
        chartPH.data.labels = nph.labels;
        chartPH.data.datasets[0].data = nph.data;
        chartPH.data.datasets[1].data = nec;
        chartPH.update();
      });
    }
  }

  // --- Popups de gráfico de widget ---
  document.querySelectorAll('.chart-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      Swal.fire({
        title: `Gráfico ${icon.dataset.sensor}`,
        html: `
          <div style="text-align:left; margin-bottom:1rem;">
            <label for="modalDate">Fecha:</label>
            <input type="date" id="modalDate" class="swal2-input" style="width:auto;"
                   value="${new Date().toISOString().slice(0,10)}">
          </div>
          <canvas id="modalChart" width="600" height="400"></canvas>
        `,
        width: '650px',
        showCloseButton: true,
        didOpen: () => {
          const popup     = Swal.getPopup();
          const modalDate = popup.querySelector('#modalDate');
          const ctx       = popup.querySelector('#modalChart').getContext('2d');
          const sensor    = icon.dataset.sensor;

          function generateData(date) {
            const labels = Array.from({length:24}, (_,h) => `${h}:00`);
            const rnd    = (min, max, dec=2) => +(Math.random()*(max-min)+min).toFixed(dec);
            let datasets;
            if (sensor === 'DHT22') {
              datasets = [
                { label:'Temp (°C)', data: labels.map(()=> rnd(15,35,2)), fill:false },
                { label:'Hum (%)',   data: labels.map(()=> rnd(20,80,2)), fill:false }
              ];
            } else if (sensor === 'MQ1325') {
              datasets = [
                { label:'CO₂',     data: labels.map(()=> rnd(300,2000,0)), fill:false },
                { label:'Metano',  data: labels.map(()=> rnd(0,200,0)),    fill:false },
                { label:'Butano',  data: labels.map(()=> rnd(0,200,0)),    fill:false },
                { label:'Propano', data: labels.map(()=> rnd(0,200,0)),    fill:false }
              ];
            } else if (sensor === 'pH') {
              datasets = [
                { label:'pH',        data: labels.map(()=> rnd(4.0,9.0,2)), fill:false }
              ];
            } else if (sensor === 'EC') {
              datasets = [
                { label:'EC (μS/cm)',data: labels.map(()=> rnd(200,2000,0)), fill:false }
              ];
            } else {
              datasets = [{ label:sensor, data: labels.map(()=> rnd(0,100,0)), fill:false }];
            }
            return { labels, datasets };
          }

          let { labels, datasets } = generateData(modalDate.value);
          const modalChart = new Chart(ctx, {
            type:'line',
            data:{ labels, datasets },
            options:{ responsive:true, animation:{ duration:0 } }
          });

          const today = new Date().toISOString().slice(0,10);
          let intervalId = null;
          if (modalDate.value === today) {
            intervalId = setInterval(() => {
              const now     = new Date().toLocaleString('en-US', { timeZone:'America/Argentina/Tucuman' });
              const hour    = new Date(now).getHours();
              const minute  = String(new Date(now).getMinutes()).padStart(2,'0');
              const label   = `${hour}:${minute}`;
              modalChart.data.labels.push(label);
              modalChart.data.labels.shift();
              modalChart.data.datasets.forEach(ds => {
                let v;
                if (ds.label.includes('Temp'))    v = +rand(15,35,2);
                else if (ds.label.includes('Hum')) v = +rand(20,80,2);
                else if (ds.label === 'CO₂')      v = Math.round(rand(300,2000,0));
                else if (ds.label === 'Metano')   v = Math.round(rand(0,200,0));
                else if (ds.label === 'Butano')   v = Math.round(rand(0,200,0));
                else if (ds.label === 'Propano')  v = Math.round(rand(0,200,0));
                else if (ds.label === 'pH')       v = +rand(4.0,9.0,2);
                else if (ds.label.includes('EC'))  v = Math.round(rand(200,2000,0));
                else                                v = +rand(0,100,0);
                ds.data.push(v);
                ds.data.shift();
              });
              modalChart.update();
            }, 1000);
          }

          modalDate.addEventListener('change', e => {
            const d = generateData(e.target.value);
            modalChart.data.labels   = d.labels;
            modalChart.data.datasets = d.datasets;
            modalChart.update();
            if (intervalId) clearInterval(intervalId);
          });

          Swal.getPopup().addEventListener('click', e => {
            if (e.target.classList.contains('swal2-close') && intervalId) {
              clearInterval(intervalId);
            }
          });
        }
      });
    });
  });
});
