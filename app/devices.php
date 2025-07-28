<?php
require __DIR__ . '/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dispositivos - MedTuCloT</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="light-mode">
  <div class="navbar">
    <div style="display: flex; align-items: center;">
      <img src="logo.png" class="logo" alt="Logo" />
      <select id="locationFilter">
        <option>Todas</option>
        <option>Casa</option>
        <option>Oficina</option>
      </select>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <label class="switch">
        <input type="checkbox" id="modeToggle" />
        <span class="slider"></span>
      </label>
      <img src="profile.jpg" class="profile-pic" alt="Perfil" />
    </div>
  </div>

  <div class="container">
    <h1>Dispositivos</h1>
    <div class="grid" id="deviceGrid">
      <div class="add-card" onclick="openModal()">+ Añadir Dispositivo</div>
      <?php foreach ($devices as $index => $d): ?>
        <div class="card">
          <div class="card-header">
            <?= htmlspecialchars($d['icono']) ?> <?= htmlspecialchars($d['name']) ?>
          </div>
          <div><strong>ID:</strong> <?= htmlspecialchars($d['esp32_id']) ?></div>
          <div><strong>Serie:</strong> <?= htmlspecialchars($d['serial_number']) ?></div>
          <div class="map-container">
            <iframe src="<?= htmlspecialchars($d['mapa']) ?>" loading="lazy" allowfullscreen></iframe>
          </div>
          <div class="card-footer">
            <button onclick='showInfo(<?= json_encode($d, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'><i data-feather="info"></i></button>
            <button onclick='editDevice(<?= $d['id'] ?>)'><i data-feather="edit"></i></button>
            <button onclick='deleteDevice(<?= $d['id'] ?>)'><i data-feather="trash-2"></i></button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="modal hidden" id="deviceModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Añadir Dispositivo</h2>
      <form id="deviceForm" method="POST" action="devices_add.php">
        <label>Ubicación:</label>
        <input type="text" name="ubicacion" required>
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <label>ID (ESPXXXX):</label>
        <input type="text" name="espid" value="ESP<?= rand(10000,99999) ?>" readonly>
        <label>Número de Serie (EG+6 MAC):</label>
        <input type="text" name="serial" required>
        <label>Icono:</label>
        <select name="icono" required>
          <option>🏠 Casa</option>
          <option>🚗 Vehículo</option>
          <option>🏢 Edificio</option>
          <option>🧊 Frigorífico</option>
          <option>📡 Satélite</option>
          <option>📶 Antena</option>
          <option>🔧 Genérico</option>
        </select>
        <label>Domicilio:</label>
        <input type="text" name="domicilio" id="domicilio" required>
        <input type="hidden" name="mapa" id="mapa">
        <div class="map-container" id="mapPreview"></div>
        <button type="submit" class="btn-green">Guardar</button>
      </form>
    </div>
  </div>

  <div class="footer">
    &copy; 2025 MedTuCloT &ndash; Electrónica Gambino
  </div>

  <script>
    feather.replace();

    const input = document.getElementById('domicilio');
    const mapField = document.getElementById('mapa');
    const mapPreview = document.getElementById('mapPreview');
    input?.addEventListener('input', () => {
      const value = input.value.trim();
      if (value.length > 5) {
        const mapUrl = `https://www.google.com/maps?q=${encodeURIComponent(value)}&output=embed`;
        mapField.value = mapUrl;
        mapPreview.innerHTML = `<iframe src="${mapUrl}" loading="lazy" allowfullscreen></iframe>`;
      } else {
        mapField.value = '';
        mapPreview.innerHTML = '';
      }
    });

    function openModal() {
      document.getElementById("deviceModal").classList.remove("hidden");
    }

    function closeModal() {
      document.getElementById("deviceModal").classList.add("hidden");
    }

    function deleteDevice(id) {
      Swal.fire({
        title: 'Eliminar dispositivo',
        text: 'Esta acción no se puede deshacer. ¿Deseas continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`devices_delete.php?id=${id}`)
            .then(res => res.json())
            .then(resp => {
              if (resp.success) {
                location.reload();
              } else {
                Swal.fire("Error", resp.message, "error");
              }
            });
        }
      });
    }

    function showInfo(device) {
      const isDark = document.body.classList.contains("dark-mode");
      Swal.fire({
        title: `Información – ${device.name}`,
        icon: "info",
        background: isDark ? "#1f1f1f" : "#fff",
        color: isDark ? "#fff" : "#111",
        confirmButtonColor: isDark ? "#00c853" : "#3085d6",
        html: `
          <div style="text-align: left; font-size: 0.95rem;">
            <p><strong>📍 Ubicación:</strong> ${device.ubicacion}</p>
            <p><strong>🔢 Serial:</strong> ${device.serial_number}</p>
            <p><strong>🔌 ESP32 ID:</strong> ${device.esp32_id}</p>
            <p><strong>📄 Nombre:</strong> ${device.name}</p>
          </div>
        `
      });
    }

    function editDevice(id) {
      window.location.href = `devices_edit.php?id=${id}`;
    }
  </script>
</body>
</html>
