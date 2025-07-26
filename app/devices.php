<?php
// /medtuciot/app/devices.php
require __DIR__ . '/header.php';
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/app/login.php');
    exit;
}

// Obtener dispositivos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/devices.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
  <h1>Mis Dispositivos</h1>
  <div class="grid" id="deviceGrid">
    <?php if (empty($devices)): ?>
      <p class="no-devices">No tienes dispositivos registrados aún.</p>
    <?php endif; ?>

    <?php foreach ($devices as $d): ?>
      <div class="card">
        <div class="card-header"><?= htmlspecialchars($d['icono']) ?> <?= htmlspecialchars($d['nombre']) ?></div>
        <div><strong>ID:</strong> <?= htmlspecialchars($d['espid']) ?></div>
        <div><strong>Serie:</strong> <?= htmlspecialchars($d['serial']) ?></div>
        <div><strong>Ubicación:</strong> <?= htmlspecialchars($d['ubicacion']) ?></div>
        <div class="map-container">
          <iframe src="<?= htmlspecialchars($d['mapa']) ?>" loading="lazy" allowfullscreen></iframe>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="add-card" onclick="openModal()">+ Añadir Dispositivo</div>
  </div>
</div>

<!-- Modal para alta de dispositivo -->
<div class="modal hidden" id="deviceModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">×</span>
    <h2>Añadir Dispositivo</h2>
    <form id="deviceForm" action="<?= BASE_PATH ?>/app/device_add.php" method="POST">
      <label>Ubicación:</label>
      <input type="text" name="ubicacion" required>

      <label>Nombre:</label>
      <input type="text" name="nombre" required>

      <label>ID (ESPXXXX):</label>
      <input type="text" name="espid" value="ESP<?= rand(10000, 99999) ?>" readonly>

      <label>Número de Serie (único):</label>
      <input type="text" name="serial" required placeholder="EGXXXXXX">

      <label>Icono:</label>
      <select name="icono" required>
        <option value="🏠 Casa">🏠 Casa</option>
        <option value="🚗 Vehículo">🚗 Vehículo</option>
        <option value="🏢 Edificio">🏢 Edificio</option>
        <option value="🧊 Frigorífico">🧊 Frigorífico</option>
        <option value="📡 Satélite">📡 Satélite</option>
        <option value="📶 Antena">📶 Antena</option>
        <option value="🔧 Genérico">🔧 Genérico</option>
      </select>

      <label>Domicilio:</label>
      <input type="text" name="domicilio" id="domicilio" required>

      <input type="hidden" name="mapa" id="mapa">
      <div class="map-container" id="mapPreview" style="margin-top:1rem;"></div>

      <button type="submit" class="btn-green">Guardar</button>
    </form>
  </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/devices.js"></script>
<script>
  if (!window.form) {
    document.getElementById('domicilio').addEventListener('input', function () {
      const value = this.value.trim();
      const mapField = document.getElementById('mapa');
      const mapPreview = document.getElementById('mapPreview');

      if (value.length > 5) {
        const mapUrl = `https://www.google.com/maps?q=${encodeURIComponent(value)}&output=embed`;
        mapField.value = mapUrl;
        mapPreview.innerHTML = `<iframe src="${mapUrl}" loading="lazy" allowfullscreen></iframe>`;
      } else {
        mapField.value = '';
        mapPreview.innerHTML = '';
      }
    });
  }
</script>

<?php require __DIR__ . '/footer.php'; ?>
