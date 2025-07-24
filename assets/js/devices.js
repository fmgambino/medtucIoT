const modal = document.getElementById("deviceModal");
const form = document.getElementById("deviceForm");
const grid = document.getElementById("deviceGrid");
const toggle = document.getElementById("modeToggle");

let editMode = false;
let editIndex = -1;

window.onload = () => {
  loadTheme();
  const devices = JSON.parse(localStorage.getItem("devices") || "[]");
  devices.forEach((d, i) => addDeviceToGrid(d, i));
};

toggle.addEventListener("change", () => {
  document.body.classList.toggle("dark-mode", toggle.checked);
  document.body.classList.toggle("light-mode", !toggle.checked);
  localStorage.setItem("theme", toggle.checked ? "dark" : "light");
});

function loadTheme() {
  const theme = localStorage.getItem("theme") || "light";
  document.body.classList.add(theme + "-mode");
  toggle.checked = theme === "dark";
}

function openModal(edit = false, index = -1) {
  modal.classList.remove("hidden");
  editMode = edit;
  editIndex = index;

  if (edit && index !== -1) {
    const devices = JSON.parse(localStorage.getItem("devices") || "[]");
    const d = devices[index];
    form.ubicacion.value = d.ubicacion;
    form.nombre.value = d.nombre;
    form.espid.value = d.espid;
    form.serial.value = d.serial;
    form.icono.value = d.icono;
    form.domicilio.value = d.domicilio;
    form.mapa.value = d.mapa;
    document.getElementById("mapPreview").innerHTML = `<iframe src="${d.mapa}" loading="lazy" allowfullscreen></iframe>`;
  } else {
    const randomId = "ESP" + Math.floor(Math.random() * 100000);
    form.espid.value = randomId;
  }
}

function closeModal() {
  modal.classList.add("hidden");
  form.reset();
  document.getElementById("mapPreview").innerHTML = "";
  editMode = false;
  editIndex = -1;
}

form.domicilio.addEventListener("input", () => {
  const address = form.domicilio.value.trim();
  if (address.length > 5) {
    const url = `https://www.google.com/maps?q=${encodeURIComponent(address)}&output=embed`;
    form.mapa.value = url;
    document.getElementById("mapPreview").innerHTML = `<iframe src="${url}" loading="lazy" allowfullscreen></iframe>`;
  }
});

form.addEventListener("submit", (e) => {
  e.preventDefault();

  const now = new Date().toISOString();
  const device = {
    ubicacion: form.ubicacion.value,
    nombre: form.nombre.value,
    espid: form.espid.value,
    serial: form.serial.value,
    icono: form.icono.value,
    domicilio: form.domicilio.value,
    mapa: form.mapa.value,
    mac: form.serial.value.substring(2),
    ip: "192.168.0.101",
    wifi: "MedTuCloT_WiFi",
    historial: [{
      timestamp: now,
      rssi: "-49 dBm",
      mqtt: "Online",
      temp: "53.3 °C",
      uptime: "0:00:03:17"
    }]
  };

  let devices = JSON.parse(localStorage.getItem("devices") || "[]");

  if (editMode) {
    devices[editIndex] = device;
    localStorage.setItem("devices", JSON.stringify(devices));
    refreshGrid();
  } else {
    devices.push(device);
    localStorage.setItem("devices", JSON.stringify(devices));
    addDeviceToGrid(device, devices.length - 1);
  }

  closeModal();
});

function addDeviceToGrid(device, index) {
  const card = document.createElement("div");
  card.className = "card";
  card.innerHTML = `
    <div class="card-header">
      ${device.icono} ${device.nombre}
    </div>
    <div><strong>ID:</strong> ${device.espid}</div>
    <div><strong>Serie:</strong> ${device.serial}</div>
    <div class="map-container">
      <iframe src="${device.mapa}" loading="lazy" allowfullscreen></iframe>
    </div>
    <div class="card-footer">
      <button onclick="showInfo(${index})"><i data-feather="info"></i></button>
      <button onclick="editDevice(${index})"><i data-feather="edit"></i></button>
      <button onclick="deleteDevice(${index})"><i data-feather="trash-2"></i></button>
    </div>
  `;
  grid.insertBefore(card, grid.querySelector(".add-card"));
  feather.replace();
}

function refreshGrid() {
  const devices = JSON.parse(localStorage.getItem("devices") || "[]");
  grid.innerHTML = `<div class="add-card" onclick="openModal()">+ Añadir Dispositivo</div>`;
  devices.forEach((d, i) => addDeviceToGrid(d, i));
}

function deleteDevice(index) {
  let devices = JSON.parse(localStorage.getItem("devices") || "[]");
  devices.splice(index, 1);
  localStorage.setItem("devices", JSON.stringify(devices));
  refreshGrid();
}

function editDevice(index) {
  openModal(true, index);
}

function showInfo(index) {
  try {
    const isDark = document.body.classList.contains("dark-mode");
    const devices = JSON.parse(localStorage.getItem("devices") || "[]");
    const device = devices[index];
    const last = device.historial?.at(-1) || {
      rssi: "N/A", mqtt: "Offline", temp: "N/A", uptime: "N/A"
    };
    const mqttColor = last.mqtt.toLowerCase() === "online" ? "green" : "red";

    Swal.fire({
      title: `Estado de ${device.nombre}`,
      icon: "info",
      background: isDark ? "#1f1f1f" : "#fff",
      color: isDark ? "#fff" : "#111",
      confirmButtonColor: isDark ? "#00c853" : "#3085d6",
      showDenyButton: true,
      denyButtonText: '🔄 Simular Reinicio',
      html: `
        <div style="text-align: left; font-size: 0.95rem;">
          <p><i data-feather="map-pin"></i> <strong>Ubicación:</strong> ${device.ubicacion}</p>
          <p><i data-feather="cpu"></i> <strong>ID ESP32:</strong> ${device.espid}</p>
          <p><i data-feather="hash"></i> <strong>MAC:</strong> ${device.mac}</p>
          <p><i data-feather="wifi"></i> <strong>Red WiFi:</strong> ${device.wifi}</p>
          <p><i data-feather="globe"></i> <strong>IP:</strong> ${device.ip}</p>
          <hr/>
          <p><i data-feather="bar-chart-2"></i> <strong>RSSI:</strong> <span class="badge green">${last.rssi}</span></p>
          <p><i data-feather="check-circle"></i> <strong>MQTT:</strong> <span class="badge ${mqttColor}">${last.mqtt}</span></p>
          <p><i data-feather="thermometer"></i> <strong>Temp CPU:</strong> <span class="badge green">${last.temp}</span></p>
          <p><i data-feather="clock"></i> <strong>Uptime:</strong> <span class="badge gray">${last.uptime}</span></p>
          <p><i data-feather="refresh-ccw"></i> <strong>Reinicios:</strong> <span class="badge gray">${device.historial.length}</span></p>
          <div style="margin-top: 1rem; text-align: left;">
            <button onclick="showChart(${index})" class="btn-green">📊 Ver Historial de Temperatura</button>
          </div>
        </div>
      `,
      willOpen: () => feather.replace(),
      preDeny: () => simulateReboot(index)
    });
  } catch (err) {
    console.error("Error al mostrar info del dispositivo:", err);
    Swal.fire("Error", "No se pudo cargar la información del dispositivo.", "error");
  }
}

function simulateReboot(index) {
  const now = new Date().toISOString();
  const devices = JSON.parse(localStorage.getItem("devices") || "[]");

  const simulatedStatus = {
    timestamp: now,
    rssi: "-" + Math.floor(Math.random() * 20 + 40) + " dBm",
    mqtt: "Online",
    temp: (Math.random() * 20 + 40).toFixed(1) + " °C",
    uptime: "0:00:" + Math.floor(Math.random() * 59 + 10) + ":" + Math.floor(Math.random() * 59).toString().padStart(2, "0")
  };

  devices[index].historial.push(simulatedStatus);
  localStorage.setItem("devices", JSON.stringify(devices));

  Swal.fire({
    icon: 'success',
    title: 'Reinicio simulado',
    timer: 1000,
    showConfirmButton: false,
    background: isDark() ? "#1f1f1f" : "#fff",
    color: isDark() ? "#fff" : "#111"
  }).then(() => {
    showInfo(index);
  });
}

function showChart(index) {
  const devices = JSON.parse(localStorage.getItem("devices") || "[]");
  const device = devices[index];

  const fechas = device.historial.map(e => e.timestamp.split("T")[0]);
  const temperaturas = device.historial.map(e => parseFloat(e.temp));

  Swal.fire({
    title: `Historial de Temperatura – ${device.nombre}`,
    html: `
      <div style="text-align:left">
        <canvas id="tempChart" width="400" height="200"></canvas>
        <div style="margin-top:10px;">
          <label><strong>Filtrar por fecha:</strong></label>
          <input type="date" id="filterDate" />
        </div>
      </div>`,
    background: isDark() ? "#1f1f1f" : "#fff",
    color: isDark() ? "#fff" : "#111",
    confirmButtonText: "OK",
    didOpen: () => {
      const ctx = document.getElementById("tempChart").getContext("2d");
      const chart = new Chart(ctx, {
        type: "line",
        data: {
          labels: fechas,
          datasets: [{
            label: "Temp °C",
            data: temperaturas,
            borderColor: "#00c853",
            backgroundColor: "rgba(0, 200, 83, 0.2)",
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          scales: {
            x: { title: { display: true, text: "Fecha" } },
            y: { title: { display: true, text: "°C" }, suggestedMin: 30, suggestedMax: 80 }
          },
          plugins: {
            legend: { labels: { color: isDark() ? "#fff" : "#111" } }
          }
        }
      });

      document.getElementById("filterDate").addEventListener("change", (e) => {
        const date = e.target.value;
        const filtered = device.historial.filter(h => h.timestamp.startsWith(date));
        chart.data.labels = filtered.map(h => h.timestamp.split("T")[1].slice(0, 5));
        chart.data.datasets[0].data = filtered.map(h => parseFloat(h.temp));
        chart.update();
      });
    }
  });
}

function isDark() {
  return document.body.classList.contains("dark-mode");
}