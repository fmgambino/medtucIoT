<?php
require __DIR__ . '/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/login');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
$lastDevice = end($devices);
$lastDate = $lastDevice ? date("Y-m-d H:i", strtotime($lastDevice['created_at'] ?? 'now')) : 'N/A';
$lastEspId = $lastDevice['esp32_id'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dispositivos - MedTuCloT</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/devices.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
  <div class="container">
    <h1>Dispositivos</h1>
    <p><strong>Último agregado:</strong> <?= $lastDate ?> — ID: <?= htmlspecialchars($lastEspId) ?></p>

    <div class="grid" id="deviceGrid">
      <div class="add-card" onclick="openModal()">+ Añadir Dispositivo</div>
      <?php foreach ($devices as $index => $d): ?>
        <div class="card">
          <div class="card-header">
            <?= htmlspecialchars($d['icono']) ?> <?= htmlspecialchars($d['nombre']) ?>
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
      <span class="close" onclick="closeModal()">×</span>
      <h2>Añadir Dispositivo</h2>
      <form id="deviceForm" method="POST" action="<?= BASE_PATH ?>/devices_add.php">
        <label>Ubicación:</label>
        <input type="text" name="ubicacion" required />
        <label>Nombre:</label>
        <input type="text" name="nombre" required />
        <label>ID (ESPXXXX):</label>
        <input type="text" name="espid" value="ESP<?= rand(10000,99999) ?>" readonly />
        <label>Número de Serie (EG+6 MAC):</label>
        <input type="text" name="serial" required />
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
        <input type="text" name="domicilio" id="domicilio" required />
        <input type="hidden" name="mapa" id="mapa" />
        <div class="map-container" id="mapPreview" style="margin-top:1rem;"></div>
        <button type="submit" class="btn-green">Guardar</button>
      </form>
    </div>
  </div>

  <div class="footer">© 2025 MedTuCloT – Electrónica Gambino</div>

  <script src="<?= BASE_PATH ?>/assets/js/main.js"></script>
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
          fetch(`<?= BASE_PATH ?>/devices_delete.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
          })
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
        title: `Información – ${device.nombre}`,
        icon: "info",
        background: isDark ? "#1f1f1f" : "#fff",
        color: isDark ? "#fff" : "#111",
        confirmButtonColor: isDark ? "#00c853" : "#3085d6",
        html: `
          <div style="text-align: left; font-size: 0.95rem;">
            <p><i data-feather="map-pin"></i> <strong>Ubicación:</strong> ${device.ubicacion}</p>
            <p><i data-feather="cpu"></i> <strong>ID ESP32:</strong> ${device.esp32_id}</p>
            <p><i data-feather="hash"></i> <strong>MAC:</strong> ${device.serial_number.substring(2)}</p>
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

    function editDevice(id) {
      window.location.href = `<?= BASE_PATH ?>/devices_edit.php?id=${id}`;
    }
  </script>
<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
