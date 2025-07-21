// assets/js/addSensor.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE    = window.BASE_PATH || '';
  const SCRIPT  = `${BASE}/app/sensor.php`;

  const endpoints = {
    add:    `${SCRIPT}?action=add`,
    edit:   `${SCRIPT}?action=edit`,
    get:    id => `${SCRIPT}?action=get&id=${encodeURIComponent(id)}`,
    delete: id => `${SCRIPT}?action=delete&id=${encodeURIComponent(id)}`
  };

  // Modal & form elements
  const addBtn     = document.getElementById('addSensorBtn');
  const modal      = document.getElementById('sensorModal');
  const closeBtn   = modal.querySelector('.close');
  const form       = document.getElementById('sensorForm');
  const titleEl    = document.getElementById('modalTitle');
  const idInput    = document.getElementById('sensorId');
  const deviceInput= document.getElementById('deviceId');
  const nameInput  = document.getElementById('sensorName');
  const portInput  = document.getElementById('sensorPort');
  const varInput   = document.getElementById('sensorVar');
  const iconInput  = document.getElementById('sensorIcon');

  if (![addBtn, modal, closeBtn, form, titleEl, idInput, deviceInput, nameInput, portInput, varInput, iconInput].every(Boolean)) {
    console.error('addSensor.js: faltan elementos en el DOM');
    return;
  }

  // open modal (add or edit)
  function openModal(isEdit = false, data = {}) {
    titleEl.textContent = isEdit ? 'Editar Sensor' : 'Añadir Sensor';
    if (isEdit) {
      idInput.value      = data.id || '';
      deviceInput.value  = data.device_id || deviceInput.value;
      nameInput.value    = data.name     || '';
      portInput.value    = data.port     || '';
      varInput.value     = data.variable || '';
      iconInput.value    = data.icon     || '';
    } else {
      form.reset();
      idInput.value      = '';
    }
    modal.classList.add('active');
  }

  // close modal
  function closeModal() {
    modal.classList.remove('active');
  }

  // show blank modal on "Añadir Sensor"
  addBtn.addEventListener('click', () => openModal(false));
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });

  // submit form (add or edit)
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const url = idInput.value ? endpoints.edit : endpoints.add;
    try {
      const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      if (json.success) return location.reload();
      throw new Error(json.error || 'Error guardando sensor');
    } catch (err) {
      console.error(err);
      Swal.fire('Error', err.message, 'error');
    }
  });

  // attach edit handlers
  document.querySelectorAll('.edit-icon').forEach(icon => {
    icon.addEventListener('click', async () => {
      const id = icon.dataset.id;
      try {
        const res    = await fetch(endpoints.get(id));
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const { sensor, ...rest } = await res.json();
        openModal(true, sensor || rest);
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar el sensor.', 'error');
      }
    });
  });

  // attach delete handlers
  document.querySelectorAll('.delete-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      const id   = icon.dataset.id;
      const name = icon.closest('.widget').querySelector('h2').textContent.trim();
      Swal.fire({
        title: `Eliminar "${name}"?`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar'
      }).then(async ({ isConfirmed }) => {
        if (!isConfirmed) return;
        try {
          const res  = await fetch(endpoints.delete(id), { method: 'POST' });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          if (json.success) return location.reload();
          throw new Error(json.error || 'Error eliminando sensor');
        } catch (err) {
          console.error(err);
          Swal.fire('Error', err.message, 'error');
        }
      });
    });
  });
});
