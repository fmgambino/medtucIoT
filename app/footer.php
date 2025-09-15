
      <!-- Footer -->
      <footer class="footer">
        © 2025 MedTuCIoT – Electrónica Gambino
      </footer>
    </div>
  </div>

<!-- SCRIPTS -->
<script>
  // Definir BASE_PATH global JS
  const BASE_PATH = '<?= rtrim(BASE_PATH, '/') ?>';
  window.BASE_PATH = BASE_PATH;
</script>

<script>
  const currentDeviceId = <?= (int)($currentDeviceId ?? 0) ?>;
</script>

<!-- Scripts propios -->
<script defer src="<?= rtrim(BASE_PATH, '/') ?>/assets/js/main.js"></script>
<script defer src="<?= rtrim(BASE_PATH, '/') ?>/assets/js/addSensor.js"></script>
<script defer src="<?= rtrim(BASE_PATH, '/') ?>/assets/js/charts_sensores.js"></script>
<script defer src="<?= rtrim(BASE_PATH, '/') ?>/assets/js/pwa.js"></script>

<!-- Librerías externas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="app/js/devicesCam.js"></script>
<!-- Feather Icons (requerido por feather.replace()) -->
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>


<!-- Service Worker adaptativo -->
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register(BASE_PATH + '/service-wojer.js', {
  scope: BASE_PATH + '/'
  })
  .then(reg => {
    console.log('SW registrado', reg);
    if (navigator.serviceWorker.controller) return;
    reg.addEventListener('updatefound', () => {
      const newSW = reg.installing;
      newSW.addEventListener('statechange', () => {
        if (newSW.state === 'activated') {
          console.log('SW activado, recargando para tomar control');
          window.location.reload();
        }
      });
    });
  })
  .catch(err => console.error('Error SW:', err));
}
</script>

<!-- Cambio de lugar o dispositivo -->
<script>
function onPlaceChange() {
  const p = document.getElementById('placeSelect').value;
  const d = document.getElementById('deviceSelect').value;
  window.location.href = `${BASE_PATH}/dashboard?place=${encodeURIComponent(p)}&device=${encodeURIComponent(d)}`;
}
function onDeviceChange() {
  onPlaceChange();
}
</script>

<!-- Valor del dispositivo actual desde PHP a JS -->
<script>
  const currentDeviceId = <?= (int)($currentDeviceId ?? 0) ?>;
</script>

<!-- Popup de información del dispositivo -->
<script>
document.getElementById("showDeviceInfo").addEventListener("click", async () => {
  if (!currentDeviceId) {
    Swal.fire("Error", "No se ha seleccionado ningún dispositivo", "error");
    return;
  }

  try {
    const res = await fetch(`${BASE_PATH}/app/get_device_status.php?deviceId=${currentDeviceId}`, {
  method: 'GET',
  headers: {
    "X-Requested-With": "XMLHttpRequest"
  },
  credentials: 'same-origin' // ⬅️ Esto permite enviar cookies PHP
});


    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const result = await res.json();

    if (!result.success || !result.status) {
      Swal.fire("Error", result.message || "No se pudo obtener info del dispositivo", "error");
      return;
    }

    const d = result.status;
    const isDark = document.body.classList.contains("dark-mode");

    Swal.fire({
      title: `Estado de ${d.name || d.esp32_id}`,
      icon: "info",
      background: isDark ? "#1f1f1f" : "#fff",
      color: isDark ? "#fff" : "#111",
      confirmButtonColor: isDark ? "#00c853" : "#3085d6",
      html: `
        <div style="text-align: left; font-size: 0.95rem;">
          <p><i data-feather="map-pin"></i> <strong>Ubicación:</strong> ${d.place}</p>
          <p><i data-feather="cpu"></i> <strong>ID ESP32:</strong> ${d.esp32_id}</p>
          <p><i data-feather="hash"></i> <strong>MAC:</strong> ${d.mac}</p>
          <p><i data-feather="wifi"></i> <strong>Red WiFi:</strong> ${d.wifi}</p>
          <p><i data-feather="globe"></i> <strong>IP:</strong> ${d.ip}</p>
          <hr/>
          <p><i data-feather="bar-chart-2"></i> <strong>RSSI:</strong> <span class="badge green">${d.rssi} dBm</span></p>
          <p><i data-feather="check-circle"></i> <strong>MQTT:</strong> <span class="badge green">${d.mqtt}</span></p>
          <p><i data-feather="thermometer"></i> <strong>Temp CPU:</strong>
            <span class="badge green">${d.cpu_temp} °C</span>
            <i class="ri-line-chart-fill chart-icon" style="cursor:pointer;" title="Ver histórico" onclick="showCpuChart()"></i>
          </p>
          <p><i data-feather="clock"></i> <strong>Uptime:</strong> <span class="badge gray">${d.uptime}</span></p>
        </div>
      `,
      willOpen: () => feather.replace()
    });

  } catch (err) {
    console.error(err);
    Swal.fire("Error", "No se pudo obtener información del dispositivo", "error");
  }
});
</script>

<!-- Popup de información TEMP CPU -->
<script>
function showCpuChart() {
  Swal.fire({
    title: 'Historial Temperatura CPU',
    html: `
      <label for="startDate">Desde:</label>
      <input type="date" id="startDate" value="<?= date('Y-m-d', strtotime('-1 day')) ?>" style="margin-bottom:0.5rem; display:block;">
      <label for="endDate">Hasta:</label>
      <input type="date" id="endDate" value="<?= date('Y-m-d') ?>" style="display:block;">
      <canvas id="cpuTempChart" width="300" height="150" style="margin-top:1rem;"></canvas>
    `,
    width: 600,
    showCancelButton: true,
    confirmButtonText: 'Actualizar',
    background: document.body.classList.contains("dark-theme") ? '#1f1f1f' : '#fff',
    color: document.body.classList.contains("dark-theme") ? '#fff' : '#111',
    didOpen: () => {
      const ctx = document.getElementById('cpuTempChart').getContext('2d');
      window.cpuChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            label: 'Temp CPU (°C)',
            data: [],
            borderColor: '#00c853',
            backgroundColor: 'rgba(0,200,83,0.1)',
            tension: 0.3,
            fill: true,
            pointRadius: 2,
            pointHoverRadius: 5,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            zoom: {
              zoom: {
                wheel: { enabled: true },
                pinch: { enabled: true },
                mode: 'x',
              },
              pan: {
                enabled: true,
                mode: 'x',
              }
            }
          },
          scales: {
            x: {
              title: { display: true, text: 'Fecha y hora' },
              ticks: { maxRotation: 45, minRotation: 45 }
            },
            y: {
              title: { display: true, text: '°C' },
              beginAtZero: true
            }
          }
        }
      });
      fetchCpuData();
    },
    preConfirm: () => {
      return fetchCpuData();
    }
  });
}

function fetchCpuData() {
  const start = document.getElementById('startDate').value;
  const end   = document.getElementById('endDate').value;

  return fetch(`${BASE_PATH}/get_history.php?device_id=${currentDeviceId}&variable=cpu_temp&start=${start}&end=${end}`)
    .then(res => res.json())
    .then(json => {
      if (!json.success || !Array.isArray(json.data)) {
        Swal.showValidationMessage('❌ No se pudo obtener datos de temperatura CPU.');
        return;
      }
      const labels = json.data.map(e => e.timestamp);
      const values = json.data.map(e => parseFloat(e.value));
      window.cpuChart.data.labels = labels;
      window.cpuChart.data.datasets[0].data = values;
      window.cpuChart.update();
    })
    .catch(err => {
      console.error("Error al cargar histórico de CPU:", err);
      Swal.showValidationMessage("❌ Error al consultar el servidor.");
    });
}
</script>

</body>
</html>

