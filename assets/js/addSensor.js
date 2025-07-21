// assets/js/addSensor.js
document.addEventListener('DOMContentLoaded', () => {
  const addBtn    = document.getElementById('addSensorBtn');
  const modal     = document.getElementById('sensorModal');
  const closeBtn  = modal.querySelector('.close');
  const form      = document.getElementById('sensorForm');
  const titleEl   = document.getElementById('modalTitle');
  const idInput   = document.getElementById('sensorId');
  const nameInput = document.getElementById('sensorName');
  const portInput = document.getElementById('sensorPort');
  const varInput  = document.getElementById('sensorVar');
  const iconInput = document.getElementById('sensorIcon');
  const deviceId  = document.getElementById('deviceId').value;

  // Usamos sensor.php en lugar de sensores.php
  const baseUrl = `${BASE_PATH}/app/sensor.php`;
  const endpoints = {
    add:    `${baseUrl}?action=add`,
    edit:   `${baseUrl}?action=edit`,
    get:    id => `${baseUrl}?action=get&id=${encodeURIComponent(id)}`,
    delete: id => `${baseUrl}?action=delete&id=${encodeURIComponent(id)}`
  };

  if (!addBtn || !modal || !closeBtn || !form ||
      !titleEl || !idInput || !nameInput || !portInput || !varInput || !iconInput) {
    console.error('addSensor.js: faltan elementos en el DOM');
    return;
  }

  function openModal(isEdit = false, sensor = {}) {
    if (isEdit) {
      titleEl.textContent = 'Editar Sensor';
      idInput.value       = sensor.id;
      nameInput.value     = sensor.name     || '';
      portInput.value     = sensor.port     || '';
      varInput.value      = sensor.variable || '';
      iconInput.value     = sensor.icon     || '';
    } else {
      titleEl.textContent = 'Añadir Sensor';
      form.reset();
      idInput.value = '';
    }
    modal.classList.add('active');
  }

  function closeModal() {
    modal.classList.remove('active');
  }

  // Abrir modal
  addBtn.addEventListener('click', () => openModal());

  // Cerrar modal
  closeBtn.addEventListener('click', closeModal);
  window.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });

  // Guardar (add / edit)
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const isEdit = !!idInput.value;
    const url    = isEdit ? endpoints.edit : endpoints.add;
    const formData = new FormData(form);
    formData.set('deviceId', deviceId);  // asegurar que siempre venga

    try {
      const res  = await fetch(url, { method: 'POST', body: formData });
      const json = await res.json();
      if (res.ok && json.success) {
        location.reload();
      } else {
        Swal.fire('Error', json.error || 'No se pudo guardar el sensor.', 'error');
      }
    } catch (err) {
      console.error(err);
      Swal.fire('Error', 'Fallo de conexión o respuesta inválida.', 'error');
    }
  });

  // Editar sensor
  document.querySelectorAll('.edit-icon').forEach(icon => {
    icon.addEventListener('click', async () => {
      const id = icon.dataset.id;
      try {
        const res = await fetch(endpoints.get(id));
        const json = await res.json();
        if (res.ok && json.success && json.sensor) {
          openModal(true, json.sensor);
        } else {
          throw new Error(json.error || 'No encontrado');
        }
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar la información del sensor.', 'error');
      }
    });
  });

  // Eliminar sensor
  document.querySelectorAll('.delete-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      const widget = icon.closest('.widget');
      const name   = widget.querySelector('h2').textContent.trim();
      const id     = icon.dataset.id;

      Swal.fire({
        title: `Eliminar “${name}”?`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async ({ isConfirmed }) => {
        if (!isConfirmed) return;
        try {
          const res  = await fetch(endpoints.delete(id), { method: 'POST' });
          const json = await res.json();
          if (res.ok && json.success) {
            location.reload();
          } else {
            throw new Error(json.error || 'Error al eliminar');
          }
        } catch (err) {
          console.error(err);
          Swal.fire('Error', 'No se pudo eliminar el sensor.', 'error');
        }
      });
    });
  });

});
