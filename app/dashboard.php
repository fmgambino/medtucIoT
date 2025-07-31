<?php
// dashboard.php

// Mostrar errores (para desarrollo, quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/app/login.php');
    exit;
}

// Verifica que $pdo exista
if (!isset($pdo)) {
    die("❌ Error: conexión a base de datos no inicializada correctamente.");
}

$userId = $_SESSION['user_id'];

// Obtener dispositivos del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ?");
    $stmt->execute([$userId]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error al obtener dispositivos: " . $e->getMessage());
}

// Obtener imagen de perfil
try {
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $relativePath = $stmt->fetchColumn() ?: 'assets/files/default.png';
} catch (PDOException $e) {
    $relativePath = 'assets/files/default.png';
}

$fullPath = __DIR__ . '/../' . $relativePath;
$version  = file_exists($fullPath) ? filemtime($fullPath) : time();
$user_img = $relativePath . '?v=' . $version;

// Lugares y dispositivos simulados
$places = [
    ['id'=>1,'name'=>'Casa'],
    ['id'=>2,'name'=>'Oficina'],
    ['id'=>3,'name'=>'Campo1'],
];
$devices_by_place = [
    1 => [['id'=>110,'name'=>'ESP32-Casa-1'],['id'=>102,'name'=>'ESP32-Casa-2'],['id'=>103,'name'=>'ESP32-Casa-3']],
    2 => [['id'=>201,'name'=>'ESP32-Oficina-1'],['id'=>202,'name'=>'ESP32-Oficina-2'],['id'=>203,'name'=>'ESP32-Oficina-3']],
    3 => [['id'=>301,'name'=>'ESP32-Campo1-1'],['id'=>302,'name'=>'ESP32-Campo1-2'],['id'=>303,'name'=>'ESP32-Campo1-3']],
];

// Obtener lugar y dispositivo actual
$currentPlaceId = isset($_GET['place']) ? (int)$_GET['place'] : ($places[0]['id'] ?? 0);
$deviceList = $devices_by_place[$currentPlaceId] ?? [];
$currentDeviceId = isset($_GET['device']) ? (int)$_GET['device'] : ($deviceList[0]['id'] ?? 0);

// Mostrar advertencia si no hay dispositivo válido
if (!$currentDeviceId) {
    echo "<p style='padding:2rem; font-size:1.2rem; color:#d00;'>⚠️ No hay dispositivos asociados aún.<br>Ve a <a href='devices.php'>Mis Dispositivos</a> para agregar uno.</p>";
    require __DIR__ . '/footer.php';
    exit;
}

// Unidades para cada tipo de sensor
$unitMap = [
    'tempHum'  => [
        ['label'=>'Temp',  'unit'=>'°C',    'spanId'=>'tempVal'],
        ['label'=>'Hum',   'unit'=>'%',     'spanId'=>'humVal'],
    ],
    'mq135'    => [
        ['label'=>'CO₂',     'unit'=>'ppm', 'spanId'=>'co2Val'],
        ['label'=>'Metano',  'unit'=>'ppm', 'spanId'=>'methaneVal'],
        ['label'=>'Butano',  'unit'=>'ppm', 'spanId'=>'butaneVal'],
        ['label'=>'Propano', 'unit'=>'ppm', 'spanId'=>'propaneVal'],
    ],
    'soilHum' => [['label'=>'', 'unit'=>'%',   'spanId'=>'soilHumVal']],
    'ph'      => [['label'=>'', 'unit'=>'',    'spanId'=>'phVal']],
    'ec'      => [['label'=>'', 'unit'=>'μS/cm','spanId'=>'ecVal']],
    'h2o'     => [['label'=>'', 'unit'=>'%',   'spanId'=>'h2oVal']],
    'nafta'   => [['label'=>'', 'unit'=>'%',   'spanId'=>'naftaVal']],
    'aceite'  => [['label'=>'', 'unit'=>'%',   'spanId'=>'aceiteVal']],
    'ldr'     => [['label'=>'', 'unit'=>'lux', 'spanId'=>'ldrVal']],
];

// Obtener sensores del dispositivo
try {
    $stmt = $pdo->prepare('SELECT * FROM sensors WHERE device_id = ? ORDER BY id');
    $stmt->execute([$currentDeviceId]);
    $sensorsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error al obtener sensores: " . $e->getMessage());
}

// Priorizar sensores compuestos
$order = ['tempHum','mq135'];
usort($sensorsRaw, function($a, $b) use ($order) {
    $aIdx = array_search($a['sensor_type'], $order, true);
    $bIdx = array_search($b['sensor_type'], $order, true);
    return ($aIdx === false ? PHP_INT_MAX : $aIdx) - ($bIdx === false ? PHP_INT_MAX : $bIdx);
});

// Agrupar sensores
$grouped = [];
foreach ($sensorsRaw as $s) {
    $type = $s['sensor_type'];
    $var  = strtolower($s['variable']);

    if ($type === 'tempHum' && !isset($grouped['tempHum'])) {
        $grouped['tempHum'] = [
            'name' => 'DHT22', 'icon' => '🌡️', 'id' => $s['id'],
            'sensor_type' => 'tempHum', 'variable' => 'tempHum',
            'lines' => $unitMap['tempHum']
        ];
    } elseif ($type === 'mq135' && !isset($grouped['mq135'])) {
        $grouped['mq135'] = [
            'name' => 'MQ135', 'icon' => '⛽', 'id' => $s['id'],
            'sensor_type' => 'mq135', 'variable' => 'mq135',
            'lines' => $unitMap['mq135']
        ];
    } elseif (!in_array($var, ['co2', 'methane', 'butane', 'propane', 'temp', 'hum'], true)) {
        $grouped[$var] = [
            'name' => $s['name'], 'icon' => $s['icon'], 'id' => $s['id'],
            'sensor_type' => $type, 'variable' => $var,
            'lines' => $unitMap[$var] ?? [['label'=>'','spanId'=>"$var".'Val','unit'=>'']]
        ];
    }
}


// Ahora $sensors lleva el orden y los gadgets corregidos para tempHum y mq135


// Actuadores simulados
$actuators = [
    [
      'id'    => 'A1',
      'name'  => 'Grupo Electrógeno',
      'state' => rand(0,1),
      'log'   => [
        ['ts'=>'2025-07-19 10:00','state'=>'ON'],
        ['ts'=>'2025-07-18 14:30','state'=>'OFF'],
      ]
    ],
    [
      'id'    => 'A2',
      'name'  => 'Lámpara',
      'state' => rand(0,1),
      'log'   => [
        ['ts'=>'2025-07-19 09:00','state'=>'OFF'],
        ['ts'=>'2025-07-18 16:45','state'=>'ON'],
      ]
    ],
    [
      'id'    => 'A3',
      'name'  => 'Ventilador',
      'state' => rand(0,1),
      'log'   => [
        ['ts'=>'2025-07-19 08:15','state'=>'ON'],
        ['ts'=>'2025-07-18 12:22','state'=>'OFF'],
      ]
    ],
    [
      'id'    => 'A4',
      'name'  => 'Válvula',
      'state' => rand(0,1),
      'log'   => [
        ['ts'=>'2025-07-19 11:30','state'=>'OFF'],
        ['ts'=>'2025-07-18 18:05','state'=>'ON'],
      ]
    ],
];

// ——————————————————————————————————————————————————————

// → Inserta aquí (línea 25 aprox)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actuator_id'], $_POST['new_name'])) {
    $stmt = $pdo->prepare('UPDATE devices SET name = ? WHERE id = ?');
    $stmt->execute([
        trim($_POST['new_name']),
        (int)$_POST['actuator_id']
    ]);

    // Redirige para evitar resubmit con parámetros limpios
    $baseUrl = rtrim(BASE_PATH, '/');
    $place   = urlencode($_POST['place'] ?? '');
    $device  = urlencode($_POST['device'] ?? '');
    header("Location: {$baseUrl}/dashboard?place={$place}&device={$device}");
    exit;
}

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    $baseUrl = rtrim(BASE_PATH, '/');
    header("Location: {$baseUrl}/login");
    exit;
}

// Variables de sesión y navegación
$user_img = $_SESSION['profile_image'] ?? 'default.png';

// Manejo de selección de lugar y dispositivo
$selected_place  = (int)($_GET['place']  ?? $places[0]['id']);
$devices         = $devices_by_place[$selected_place] ?? [];
$selected_device = (int)($_GET['device'] ?? ($devices[0]['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>IoT Dashboard – MedTuCIoT</title>

  <!-- Remixicon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
  <link rel="icon" href="<?= rtrim(BASE_PATH, '/') ?>/assets/img/favicon.png">

  <!-- Estilos -->
  <link rel="stylesheet" href="<?= rtrim(BASE_PATH, '/') ?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?= rtrim(BASE_PATH, '/') ?>/assets/css/addSensor.css">
  <link rel="stylesheet" href="<?= rtrim(BASE_PATH, '/') ?>/assets/css/mobiles.css">

  <!-- Librerías JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>
</head>
<body>

<button id="btnInstall" style="display:none;
  position:fixed; bottom:1rem; right:1rem;
  background:#2196F3; color:#fff; border:none;
  padding:0.5rem 1rem; border-radius:4px; z-index:1000;">
  📲 Instalar App
</button>

  <!-- drawer móvil oculto -->
  <div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
      <img src="<?= rtrim(BASE_PATH, '/') ?>/assets/img/logo-small.png">
    </div>
    <i id="themeToggleDrawer" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
    <i id="langToggleDrawer"  class="ri-earth-line icon-btn"  title="ES/EN"></i>
    <i id="notifToggleDrawer"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>
    <!-- dentro de .topbar-right -->


    <a href="<?= BASE_PATH ?>logout" class="icon-btn" title="Salir"><i class="ri-logout-box-line"></i></a>
  </div>
  

  <div id="wrapper">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar collapsed">
      <ul class="menu">
        <li class="menu-header">IOT PANEL</li>
        <li data-page="dashboard" class="active">
          <i class="ri-dashboard-line"></i>
          <span class="menu-text">Dashboard</span>
        </li>
        <li>
          <a href="<?= BASE_PATH ?>/devices">
          <i class="ri-cpu-line"></i>
          <span class="menu-text">Devices</span>
          </a>
        </li>

        <li data-page="config">
          <i class="ri-settings-3-line"></i>
          <span class="menu-text">Configuraciones</span>
        </li>
        <li data-page="broker">
          <i class="ri-cloud-line"></i>
          <span class="menu-text">Broker MQTT</span>
        </li>
        <li data-page="profile">
          <i class="ri-user-settings-line"></i>
          <span class="menu-text">Mi Perfil</span>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <div id="main-content" class="main-content expanded">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <img src="<?= BASE_PATH ?>assets/img/logo.png" id="logo" class="logo" alt="Logo">
          <select id="placeSelect" onchange="onPlaceChange()">
            <?php foreach ($places as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $p['id'] == $selected_place ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name'], ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <select id="deviceSelect" onchange="onDeviceChange()">
            <?php foreach ($devices as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $d['id'] == $selected_device ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['name'], ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="topbar-right">
  <i id="themeToggle" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
  <i id="langToggle"  class="ri-earth-line icon-btn"  title="ES/EN"></i>
  <i id="notifToggle"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>
<!-- Botón del menú hamburguesa -->
<button class="hamburger" id="hamburger" aria-label="Menú móvil">
  <span></span><span></span><span></span>
</button>

<!-- Formulario oculto -->
<form id="profileForm" action="<?= BASE_PATH ?>/app/imgPerfil.php" method="POST" enctype="multipart/form-data" style="display:none;">
  <input type="file" name="profile_image" id="profileInput" accept="image/*" onchange="document.getElementById('profileForm').submit();">
</form>

<!-- Imagen de perfil con versión anti-caché -->
<img src="<?= BASE_PATH . '/' . htmlspecialchars($_SESSION['profile_image'] ?? 'assets/files/default.png') ?>?v=<?= time() ?>"
     class="profile-img icon-btn"
     alt="Perfil"
     onclick="document.getElementById('profileInput').click();"
     style="cursor:pointer; border-radius:50%; width:40px; height:40px; object-fit:cover;">



<!-- Botón de logout -->
<a href="<?= BASE_PATH ?>/logout" class="icon-btn"><i class="ri-logout-box-line"></i></a>




      </header>

      <!-- Dashboard Page -->
      <section id="dashboard" class="page active-page">
        <h1>
          Dashboard —
          <?= htmlspecialchars(
               $places[array_search($selected_place, array_column($places, 'id'))]['name'] ?? '',
               ENT_QUOTES
             ) ?>
        </h1>
        <div class="panel-actions">
          <!-- Ícono de información en dashboard.php -->
          <i id="showDeviceInfo" class="ri-information-line icon-btn" title="Información del dispositivo"></i>
          <i id="openCameraPopup" class="ri-camera-line icon-btn" title="Ver cámara del dispositivo" data-url="https://www.skylinewebcams.com/es/webcam/argentina/tierra-del-fuego/ushuaia/ushuaia.html"></i>
          <i id="showReboots" class="ri-history-line icon-btn" title="Historial de reinicios"></i>
          <i id="doReboot" class="ri-restart-line icon-btn" title="Reset remoto"></i>
          <span id="lastReset" class="last-reset">Último reset: —</span>
          <i class="ri-line-chart-line icon-btn" title="Ver historial de temperatura CPU" id="cpuTempChartBtn"></i>        </div>
 
<!-- SENSOR GRID + “Añadir Sensor” -->
<div class="sensor-grid">
  <!-- Añadir Sensor -->
  <div class="widget add-sensor" id="addSensorBtn">
    <h2><i class="ri-add-line"></i> Añadir Sensor</h2>
  </div>

  <?php if (!empty($grouped)): ?>
    <?php foreach ($grouped as $key => $sensor): ?>
      <div class="widget" data-sensor="<?= htmlspecialchars($key) ?>">
        <h2>
          <?= htmlspecialchars($sensor['icon']) ?>
          <?= htmlspecialchars($sensor['name']) ?>
          <i class="ri-line-chart-fill chart-icon"
             data-sensor="<?= htmlspecialchars($key) ?>"
             title="Ver gráfico <?= htmlspecialchars($sensor['name']) ?>"></i>
        </h2>

        <?php foreach ($sensor['lines'] as $line): ?>
          <p>
            <?= $line['label'] !== '' ? htmlspecialchars($line['label']) . ': ' : '' ?>
            <span id="<?= htmlspecialchars($line['spanId']) ?>">—</span>
            <?= htmlspecialchars($line['unit']) ?>
          </p>
        <?php endforeach; ?>

        <!-- Siempre mostramos las acciones de editar/eliminar -->
        <div class="widget-actions">
          <i class="ri-pencil-line edit-icon"
             data-id="<?= (int)$sensor['id'] ?>"
             title="Editar sensor"></i>
          <i class="ri-delete-bin-line delete-icon"
             data-id="<?= (int)$sensor['id'] ?>"
             title="Eliminar sensor"></i>
        </div>

        <!-- Sólo al widget de Hum. Suelo inyectamos el canvas -->
        <?php if ($sensor['variable'] === 'soilHum'): ?>
          <canvas id="chartSoilHum" width="300" height="150"></canvas>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No hay sensores configurados para este dispositivo.</p>
  <?php endif; ?>
</div>

<!-- MODAL Añadir/Editar Sensor -->
<div id="sensorModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="modalTitle">Añadir Sensor</h2>
    <form id="sensorForm">
      <input type="hidden" id="deviceId" name="deviceId" value="<?= (int)$currentDeviceId ?>">
      <input type="hidden" id="sensorId" name="sensorId" value="">

      <label for="sensorType">Tipo de sensor:</label>
      <select id="sensorType" name="sensorType" required>
        <option value="">— Selecciona —</option>
        <option value="tempHum">DHT22 (Temp + Hum)</option>
        <option value="mq135">MQ135 (4 gases)</option>
        <option value="soilHum">Hum. Suelo</option>
        <option value="ph">pH</option>
        <option value="ec">EC</option>
        <option value="h2o">Nivel H₂O</option>
        <option value="nafta">Nafta</option>
        <option value="aceite">Aceite</option>
        <option value="ldr">LDR</option>
        <option value="generic">Genérico</option>
      </select>

      <label for="sensorName">Nombre:</label>
      <input type="text" id="sensorName" name="sensorName" required>

      <label for="sensorPort">Puerto:</label>
      <input type="text" id="sensorPort" name="sensorPort" required>

      <label for="sensorVar">Variable ESP32:</label>
      <input type="text" id="sensorVar" name="sensorVar" required>

      <label for="sensorIcon">Icono:</label>
      <select id="sensorIcon" name="sensorIcon">
        <option value="🌡️">🌡️ Temperatura</option>
        <option value="💧">💧 Humedad</option>
        <option value="🧪">🧪 pH</option>
        <option value="⛽">⛽ Gases</option>
        <option value="🛢️">🛢️ Combustibles</option>
        <option value="🌬️">🌬️ Viento</option>
        <option value="📏">📏 Distancia</option>
        <option value="💡">💡 Luz</option>
        <option value="⚡">⚡ Eléctrico</option>
        <option value="🔋">🔋 Batería</option>
        <option value="🌱">🌱 Suelo</option>
        <option value="🌊">🌊 Nivel</option>
        <option value="❓">❓ Genérico</option>
      </select>

      <button type="submit" id="saveSensorBtn">Guardar</button>
    </form>
  </div>
</div>





<div class="actuators">
  <h2>Actuadores</h2>
  <div class="sensor-grid">
    <?php foreach ($actuators as $a): ?>
      <div class="widget actuator-card">
        <div class="actuator-header">
          <span>🔌 <?= htmlspecialchars($a['name'],ENT_QUOTES) ?></span>
          <i class="ri-pencil-line edit-act"
   data-id="<?= $a['id'] ?>"
   data-name="<?= htmlspecialchars($a['name'],ENT_QUOTES) ?>"></i>
<i class="ri-information-line info-act"
   data-id="<?= $a['id'] ?>"
   title="Ver historial"></i>

        </div>
        <label class="switch">
          <input type="checkbox" data-device="<?= $a['id'] ?>" <?= $a['state']?'checked':''?>>
          <span class="slider"></span>
        </label>
      </div>
    <?php endforeach; ?>
  </div>
</div>

      </section>


      <!-- Configuraciones Page -->
      <section id="config" class="page">
        <h1>Configuraciones</h1>
        <!-- Ajustes generales aquí -->
      </section>

      <!-- Broker MQTT Page -->
      <section id="broker" class="page">
        <h1>Broker MQTT</h1>
        <!-- Formulario de configuración MQTT -->
      </section>

      <!-- Profile Page -->
      <section id="profile" class="page">
        <h1>Mi Perfil</h1>
        <!-- ABM Perfil usuario aquí -->
      </section>

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
