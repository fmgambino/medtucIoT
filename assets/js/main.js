// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {

  const deviceIdInput   = document.getElementById('deviceId');
  const currentDeviceId = deviceIdInput ? deviceIdInput.value : null;

  // — Sidebar / Drawer references —
  const logo         = document.getElementById('logo');
  const sidebar      = document.getElementById('sidebar');
  const mainContent  = document.getElementById('main-content');
  const hamburger    = document.getElementById('hamburger');
  const mobileDrawer = document.getElementById('mobileDrawer');

  // — Sidebar vs Drawer toggle —
  logo.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
      mobileDrawer.classList.toggle('open');
    } else {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
    }
  });

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

  function rand(min, max, decimals = 2) {
    return (Math.random() * (max - min) + min).toFixed(decimals);
  }

  // --- SENSORES SEGUROS ---
  async function updateSensors() {
    try {
      const res = await fetch(`${BASE_PATH}/app/get_latest.php?deviceId=${currentDeviceId}`);
      if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
      const list = await res.json();
      if (!Array.isArray(list)) throw new Error('Formato de datos inválido');

      list.forEach(s => {
        if (!s || typeof s !== 'object' || !s.sensor_type || s.value === undefined) return;

        if (s.sensor_type === 'tempHum') {
          const tempEl = document.getElementById('tempVal');
          const humEl  = document.getElementById('humVal');
          if (typeof s.value === 'string') {
            try {
              const obj = JSON.parse(s.value);
              if ('temp' in obj && tempEl) tempEl.textContent = obj.temp;
              if ('hum'  in obj && humEl)  humEl.textContent  = obj.hum;
            } catch (e) {
              console.warn('tempHum inválido:', s.value);
            }
          }
        }

        else if (s.sensor_type === 'mq135') {
          if (typeof s.value === 'string') {
            try {
              const obj = JSON.parse(s.value);
              const map = {
                co2:     'co2Val',
                methane: 'methaneVal',
                butane:  'butaneVal',
                propane: 'propaneVal'
              };
              Object.entries(map).forEach(([key, id]) => {
                if (obj[key] !== undefined) {
                  const el = document.getElementById(id);
                  if (el) el.textContent = obj[key];
                }
              });
            } catch (e) {
              console.warn('mq135 inválido:', s.value);
            }
          }
        }

        else {
          const idMap = {
            soilHum: 'soilHumVal',
            ph:      'phVal',
            ec:      'ecVal',
            h2o:     'h2oVal',
            nafta:   'naftaVal',
            aceite:  'aceiteVal'
          };
          const id = idMap[s.sensor_type];
          if (id) {
            const el = document.getElementById(id);
            if (el) el.textContent = s.value;
          }
        }
      });
    } catch (err) {
      console.warn('❌ Error al actualizar sensores:', err);
      Swal.fire({
        icon: 'error',
        title: 'Error al cargar sensores',
        text: 'No se pudo obtener la información. Revisa la conexión.',
        toast: true,
        timer: 4000,
        position: 'top-end',
        showConfirmButton: false
      });
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

});
