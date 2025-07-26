const modal = document.getElementById("deviceModal");
const form = document.getElementById("deviceForm");
const grid = document.getElementById("deviceGrid");
const toggle = document.getElementById("modeToggle");

let editMode = false;
let editDeviceId = null;

window.onload = () => {
  loadTheme();
  fetchDevicesFromServer();
};

toggle?.addEventListener("change", () => {
  const dark = toggle.checked;
  document.body.classList.toggle("dark-mode", dark);
  document.body.classList.toggle("light-mode", !dark);
  localStorage.setItem("theme", dark ? "dark" : "light");
});

function loadTheme() {
  const theme = localStorage.getItem("theme") || "light";
  document.body.classList.add(`${theme}-mode`);
  if (toggle) toggle.checked = theme === "dark";
}

function fetchDevicesFromServer() {
  fetch("get_devices", {
    headers: {
      "X-Requested-With": "XMLHttpRequest"
    }
  })
    .then(res => res.json())
    .then(data => {
      grid.innerHTML = `<div class="add-card" onclick="openModal()">+ Añadir Dispositivo</div>`;
      if (Array.isArray(data) && data.length) {
        data.forEach((device, i) => addDeviceToGrid(device, i));
      } else {
        grid.innerHTML += `<p class="no-devices">No tienes dispositivos registrados aún.</p>`;
      }
    })
    .catch(err => {
      console.error("Error al cargar dispositivos:", err);
      grid.innerHTML += `<p class="no-devices">Error al cargar dispositivos.</p>`;
    });
}

function openModal() {
  form.reset();
  document.getElementById("mapPreview").innerHTML = "";
  form.espid.value = "ESP" + Math.floor(Math.random() * 100000);
  modal.classList.remove("hidden");
  editMode = false;
}

function closeModal() {
  modal.classList.add("hidden");
  editMode = false;
  editDeviceId = null;
}

form.domicilio.addEventListener("input", () => {
  const address = form.domicilio.value.trim();
  if (address.length > 5) {
    const url = `https://www.google.com/maps?q=${encodeURIComponent(address)}&output=embed`;
    form.mapa.value = url;
    document.getElementById("mapPreview").innerHTML = `<iframe src="${url}" loading="lazy" allowfullscreen></iframe>`;
  }
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(form);
  try {
    const res = await fetch("devices_add", {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      }
    });

    const result = await res.json();

    if (result.success) {
      Swal.fire("✅ Éxito", result.message, "success").then(() => {
        closeModal();
        fetchDevicesFromServer();
      });
    } else {
      Swal.fire("❌ Error", result.message, "error");
    }
  } catch (err) {
    console.error("Error al registrar dispositivo:", err);
    Swal.fire("❌ Error", "No se pudo registrar el dispositivo.", "error");
  }
});

function addDeviceToGrid(device, index) {
  const card = document.createElement("div");
  card.className = "card";
  card.innerHTML = `
    <div class="card-header">${device.icono} ${device.nombre}</div>
    <div><strong>ID:</strong> ${device.espid}</div>
    <div><strong>Serie:</strong> ${device.serial}</div>
    <div><strong>Ubicación:</strong> ${device.ubicacion}</div>
    <div class="map-container">
      <iframe src="${device.mapa}" loading="lazy" allowfullscreen></iframe>
    </div>
  `;
  grid.insertBefore(card, grid.querySelector(".add-card"));
}
