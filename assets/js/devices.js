const modal = document.getElementById("deviceModal");
const form = document.getElementById("deviceForm");
const grid = document.getElementById("deviceGrid");
const toggle = document.getElementById("modeToggle");
const mapPreview = document.getElementById("mapPreview");

let editMode = false;
let editId = null;

// Al cargar la página
document.addEventListener("DOMContentLoaded", async () => {
  loadTheme();
  await fetchDevices();
});

// Cambiar tema
toggle.addEventListener("change", () => {
  const isDark = toggle.checked;
  document.body.classList.toggle("dark-mode", isDark);
  document.body.classList.toggle("light-mode", !isDark);
  localStorage.setItem("theme", isDark ? "dark" : "light");
});

// Cargar tema desde localStorage
function loadTheme() {
  const theme = localStorage.getItem("theme") || "light";
  document.body.classList.add(`${theme}-mode`);
  toggle.checked = theme === "dark";
}

// Abrir modal
function openModal(edit = false, data = null) {
  modal.classList.remove("hidden");
  form.reset();
  mapPreview.innerHTML = "";
  editMode = edit;
  editId = null;

  if (edit && data) {
    editId = data.id;
    for (let key in data) {
      if (form[key]) form[key].value = data[key];
    }
    mapPreview.innerHTML = `<iframe src="${data.mapa}" loading="lazy" allowfullscreen></iframe>`;
  } else {
    form.espid.value = "ESP" + Math.floor(Math.random() * 100000);
  }
}

// Cerrar modal
function closeModal() {
  modal.classList.add("hidden");
  form.reset();
  mapPreview.innerHTML = "";
  editMode = false;
  editId = null;
}

// Vista previa mapa
form.domicilio.addEventListener("input", () => {
  const address = form.domicilio.value.trim();
  if (address.length > 5) {
    const url = `https://www.google.com/maps?q=${encodeURIComponent(address)}&output=embed`;
    form.mapa.value = url;
    mapPreview.innerHTML = `<iframe src="${url}" loading="lazy" allowfullscreen></iframe>`;
  }
});

// Guardar (crear/editar)
form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(form);
  if (editMode) formData.append("id", editId);

  // Confirmación con SweetAlert2
  const confirm = await Swal.fire({
    title: editMode ? "¿Guardar cambios?" : "¿Agregar este dispositivo?",
    text: editMode
      ? "Se actualizará la información del dispositivo."
      : "Se registrará en tu cuenta.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: editMode ? "Sí, guardar" : "Sí, agregar",
    cancelButtonText: "Cancelar"
  });

  if (!confirm.isConfirmed) return;

  try {
    const res = await fetch(editMode ? "devices_edit" : "devices_add", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: formData
    });

    const result = await res.json();
    if (result.success) {
      Swal.fire("✅ Éxito", result.message, "success").then(() => {
        location.reload(); // Recargar la página para reflejar cambios
      });
    } else {
      Swal.fire("❌ Error", result.message, "error");
    }
  } catch (err) {
    console.error(err);
    Swal.fire("❌ Error", "Ocurrió un problema al procesar.", "error");
  }
});

// Obtener dispositivos
async function fetchDevices() {
  try {
    const res = await fetch("get_devices.php");
    const result = await res.json();

    grid.innerHTML = `
      <div class="add-card" onclick="openModal()">
        <i data-feather="plus"></i> Añadir Dispositivo
      </div>
    `;

    if (result.devices && Array.isArray(result.devices)) {
      result.devices.forEach(device => addDeviceToGrid(device));
    }

    feather.replace();
  } catch (err) {
    console.error("Error al cargar dispositivos:", err);
  }
}

// Agregar tarjeta al grid
function addDeviceToGrid(device) {
  const card = document.createElement("div");
  card.className = "card";

  card.innerHTML = `
    <div class="card-header">${device.icono} ${device.name}</div>
    <div><strong>ID:</strong> ${device.esp32_id}</div>
    <div><strong>Serie:</strong> ${device.serial_number}</div>
    <div class="map-container">
      <iframe src="${device.mapa}" loading="lazy" allowfullscreen></iframe>
    </div>
    <div class="card-footer">
      <button onclick='showInfo(${JSON.stringify(device)})'><i data-feather="info"></i></button>
      <button onclick='openModal(true, ${JSON.stringify(device)})'><i data-feather="edit"></i></button>
      <button onclick='deleteDevice(${device.id})'><i data-feather="trash-2"></i></button>
    </div>
  `;

  grid.insertBefore(card, grid.firstElementChild);
  feather.replace();
}

// Mostrar popup de información
function showInfo(device) {
  const isDark = document.body.classList.contains("dark-mode");

  Swal.fire({
    title: `Estado de ${device.name}`,
    icon: "info",
    background: isDark ? "#1f1f1f" : "#fff",
    color: isDark ? "#fff" : "#111",
    confirmButtonColor: isDark ? "#00c853" : "#3085d6",
    html: `
      <div style="text-align: left; font-size: 0.95rem;">
        <p><i data-feather="map-pin"></i> <strong>Ubicación:</strong> ${device.ubicacion}</p>
        <p><i data-feather="cpu"></i> <strong>ID ESP32:</strong> ${device.esp32_id}</p>
        <p><i data-feather="hash"></i> <strong>MAC:</strong> ${device.serial_number?.substring(2)}</p>
        <p><i data-feather="wifi"></i> <strong>Red WiFi:</strong> MedTuCloT_WiFi</p>
        <p><i data-feather="globe"></i> <strong>IP:</strong> 192.168.0.101</p>
        <hr/>
        <p><i data-feather="bar-chart-2"></i> <strong>RSSI:</strong> <span class="badge green">-49 dBm</span></p>
        <p><i data-feather="check-circle"></i> <strong>MQTT:</strong> <span class="badge green">Online</span></p>
        <p><i data-feather="thermometer"></i> <strong>Temp CPU:</strong> <span class="badge green">53.3 °C</span></p>
        <p><i data-feather="clock"></i> <strong>Uptime:</strong> <span class="badge gray">0:00:03:17</span></p>
      </div>
    `,
    willOpen: () => feather.replace()
  });
}

// Eliminar dispositivo
async function deleteDevice(id) {
  const confirm = await Swal.fire({
    title: "¿Eliminar dispositivo?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
  });

  if (confirm.isConfirmed) {
    try {
      const res = await fetch("devices_delete.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: `id=${id}`
      });

      const result = await res.json();
      if (result.success) {
        Swal.fire("Eliminado", result.message, "success").then(fetchDevices);
      } else {
        Swal.fire("Error", result.message, "error");
      }
    } catch (err) {
      console.error(err);
      Swal.fire("Error", "No se pudo eliminar el dispositivo.", "error");
    }
  }
}
