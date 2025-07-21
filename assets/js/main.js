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
      document.querySelectorAll('.sidebar .menu li').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active-page'));
      const pageId = item.dataset.page;
      if (pageId) document.getElementById(pageId).classList.add('active-page');
    });
  });

  // --- Theme toggle ---
  const themeIcon = document.getElementById('themeToggle');
  const themeIconDrawer = document.getElementById('themeToggleDrawer');

  function applyTheme(dark) {
    document.body.classList.toggle('dark', dark);
    themeIcon?.classList.toggle('ri-sun-line', !dark);
    themeIcon?.classList.toggle('ri-moon-line', dark);
    themeIconDrawer?.classList.toggle('ri-sun-line', !dark);
    themeIconDrawer?.classList.toggle('ri-moon-line', dark);
  }

  let darkMode = localStorage.getItem('theme') === 'dark';
  applyTheme(darkMode);

  function toggleTheme() {
    darkMode = !darkMode;
    localStorage.setItem('theme', darkMode ? 'dark' : 'light');
    applyTheme(darkMode);
  }

  themeIcon?.addEventListener('click', toggleTheme);
  themeIconDrawer?.addEventListener('click', toggleTheme);

  // --- Idioma y notificaciones ---
  const langBtn = document.getElementById('langToggle');
  const notifBtn = document.getElementById('notifToggle');
  langBtn?.addEventListener('click', () => Swal.fire('Cambio de idioma', 'Aquí implementa ES/EN', 'info'));
  notifBtn?.addEventListener('click', () => Swal.fire('Notificaciones', 'No hay nuevas notificaciones.', 'info'));

  document.getElementById('langToggleDrawer')?.addEventListener('click', () => langBtn?.click());
  document.getElementById('notifToggleDrawer')?.addEventListener('click', () => notifBtn?.click());

  // --- Simulación estado del generador ---
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

  // --- Simulación sensores (DHT22 JSON y MQ135 JSON) ---
  async function updateSensors() {
    try {
      const res = await fetch(`${BASE_PATH}/app/get_latest.php?deviceId=${currentDeviceId}`);
      const list = await res.json();

      list.forEach(s => {
        if (s.sensor_type === 'tempHum') {
          try {
            const parsed = typeof s.value === 'string' ? JSON.parse(s.value) : s.value;
            if (parsed?.temp) document.getElementById('tempVal').textContent = parsed.temp;
            if (parsed?.hum)  document.getElementById('humVal').textContent  = parsed.hum;
          } catch (e) {
            console.warn('Error parseando tempHum:', s.value);
          }
        }
        else if (s.sensor_type === 'mq135') {
          try {
            const parsed = typeof s.value === 'string' ? JSON.parse(s.value) : s.value;
            if (parsed?.co2)     document.getElementById('co2Val')    .textContent = parsed.co2;
            if (parsed?.methane) document.getElementById('methaneVal').textContent = parsed.methane;
            if (parsed?.butane)  document.getElementById('butaneVal') .textContent = parsed.butane;
            if (parsed?.propane) document.getElementById('propaneVal').textContent = parsed.propane;
          } catch (e) {
            console.warn('Error parseando mq135:', s.value);
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
          const elId = idMap[s.sensor_type];
          if (elId) {
            const el = document.getElementById(elId);
            if (el) el.textContent = s.value;
          }
        }
      });
    } catch (err) {
      console.warn('Error al actualizar sensores:', err);
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

  document.getElementById('showReboots')?.addEventListener('click', () => {
    const icons = {
      'Mantenimiento':         '<i class="ri-wrench-line"></i>',
      'Actualización firmware':'<i class="ri-refresh-line"></i>',
      'Caída de red':          '<i class="ri-plug-off-line"></i>',
      'Software':              '<i class="ri-cpu-line"></i>',
      'Alimentacion':          '<i class="ri-flashlight-line"></i>',
      'Reset remoto':          '<i class="ri-restart-line"></i>'
    };
    const html = rebootHistory.map(r =>
      `<li>${icons[r.reason] || '<i class="ri-time-line"></i>'} ${r.ts} – ${r.reason}</li>`
    ).join('');
    Swal.fire({
      title: 'Historial de Reinicios',
      html: `<ul style="text-align:left; list-style:none; padding:0">${html}</ul>`,
      showCloseButton: true
    });
  });

  document.getElementById('doReboot')?.addEventListener('click', async () => {
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
    const id = cb.dataset.device;
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
      const id = btn.dataset.id;
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
