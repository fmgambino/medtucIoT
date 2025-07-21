// assets/js/addSensor.js
document.addEventListener('DOMContentLoaded', () => {
  const grid        = document.querySelector('.sensor-grid');
  const addBtn      = document.getElementById('addSensorBtn');
  const modal       = document.getElementById('sensorModal');
  const closeBtn    = modal.querySelector('.close');
  const form        = document.getElementById('sensorForm');
  const titleEl     = document.getElementById('modalTitle');
  const idInput     = document.getElementById('sensorId');
  const typeInput   = document.getElementById('sensorType');
  const nameInput   = document.getElementById('sensorName');
  const portInput   = document.getElementById('sensorPort');
  const varInput    = document.getElementById('sensorVar');
  const iconInput   = document.getElementById('sensorIcon');
  const deviceId    = document.getElementById('deviceId').value;

  const baseUrl = `${BASE_PATH}/app/sensor.php`;
  const endpoints = {
    add:    `${baseUrl}?action=add`,
    edit:   `${baseUrl}?action=edit`,
    get:    id => `${baseUrl}?action=get&id=${encodeURIComponent(id)}`,
    delete: id => `${baseUrl}?action=delete&id=${encodeURIComponent(id)}`
  };

  // Iconos por defecto
  const defaultIcons = {
    tempHum: '🌡️',
    mq135:   '⛽',
    soilHum: '🌱',
    ph:      '🧪',
    ec:      '⚡',
    h2o:     '🌊',
    nafta:   '⛽',
    aceite:  '🛢️',
    ldr:     '💡',
    generic: '❓'
  };

  function openModal(isEdit = false, sensor = {}) {
    form.reset();
    if (isEdit) {
      titleEl.textContent = 'Editar Sensor';
      idInput.value       = sensor.id;
      typeInput.value     = sensor.sensor_type;
      nameInput.value     = sensor.name;
      portInput.value     = sensor.port;
      varInput.value      = sensor.variable;
      iconInput.value     = sensor.icon;
    } else {
      titleEl.textContent = 'Añadir Sensor';
      idInput.value       = '';
      iconInput.value     = defaultIcons[typeInput.value] || '❓';
    }
    // Ajustar varInput tras setear type
    typeInput.dispatchEvent(new Event('change'));
    modal.classList.add('active');
  }

  function closeModal() {
    modal.classList.remove('active');
  }

  // Autocompletar variable según tipo
  typeInput.addEventListener('change', () => {
    switch (typeInput.value) {
      case 'tempHum':
        varInput.value    = 'temp,hum'; varInput.readOnly = true;
        break;
      case 'mq135':
        varInput.value    = 'co2,methane,butane,propane'; varInput.readOnly = true;
        break;
      case 'generic':
        varInput.value    = ''; varInput.readOnly = false; varInput.placeholder = 'variable esp32';
        break;
      default:
        varInput.value    = typeInput.value; varInput.readOnly = true; varInput.placeholder = '';
        break;
    }
    iconInput.value = defaultIcons[typeInput.value] || '❓';
  });

  // Abrir "Añadir"
  addBtn.addEventListener('click', () => openModal(false));
  // Cerrar
  closeBtn.addEventListener('click', closeModal);
  window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

  // Envío de formulario
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const isEdit = Boolean(idInput.value);
    const url    = isEdit ? endpoints.edit : endpoints.add;
    const fd     = new FormData(form);
    fd.set('deviceId', deviceId);

    try {
      const res  = await fetch(url, { method: 'POST', body: fd });
      const json = await res.json();
      if (res.ok && json.success) location.reload();
      else Swal.fire('Error', json.error || 'No se pudo guardar.', 'error');
    } catch (err) {
      console.error(err);
      Swal.fire('Error', 'Fallo de conexión.', 'error');
    }
  });

  // Delegación para editar/eliminar
  grid.addEventListener('click', async e => {
    const editBtn = e.target.closest('.edit-icon');
    const delBtn  = e.target.closest('.delete-icon');

    if (editBtn) {
      const id = editBtn.dataset.id;
      try {
        const res  = await fetch(endpoints.get(id));
        const json = await res.json();
        if (res.ok && json.success) openModal(true, json.sensor);
        else throw new Error(json.error || 'No encontrado');
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar el sensor.', 'error');
      }
    }

    if (delBtn) {
      const widget = delBtn.closest('.widget');
      const name   = widget.querySelector('h2').innerText;
      const id     = delBtn.dataset.id;
      Swal.fire({
        title: `Eliminar ${name}?`, text: 'No se podrá deshacer.', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar'
      }).then(async ({ isConfirmed }) => {
        if (!isConfirmed) return;
        try {
          const res  = await fetch(endpoints.delete(id), { method: 'POST' });
          const json = await res.json();
          if (res.ok && json.success) location.reload();
          else throw new Error(json.error || 'Error');
        } catch (err) {
          console.error(err);
          Swal.fire('Error', 'No se pudo eliminar.', 'error');
        }
      });
    }
  });
});
