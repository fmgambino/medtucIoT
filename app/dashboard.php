<?php
session_start();
require __DIR__ . '/config.php';

// 1) Lugares y dispositivos
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

// 2) Dispositivo actual
$currentPlaceId  = isset($_GET['place'])  ? (int)$_GET['place']  : $places[0]['id'];
$currentDeviceId = isset($_GET['device']) ? (int)$_GET['device'] : $devices_by_place[$currentPlaceId][0]['id'];

// 3) Definición de líneas
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

// 4) Obtener sensores
$stmt = $pdo->prepare('SELECT * FROM sensors WHERE device_id = ? ORDER BY id');
$stmt->execute([$currentDeviceId]);
$sensorsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Reordenar tempHum y mq135 primero
$order = ['tempHum','mq135'];
usort($sensorsRaw, function($a,$b) use($order){
    $aIdx = array_search($a['sensor_type'],$order,true) !== false ? array_search($a['sensor_type'],$order,true) : PHP_INT_MAX;
    $bIdx = array_search($b['sensor_type'],$order,true) !== false ? array_search($b['sensor_type'],$order,true) : PHP_INT_MAX;
    return $aIdx - $bIdx;
});

// 6) Construir array final
$grouped = [];
foreach($sensorsRaw as $s){
    $type = $s['sensor_type'];
    $var  = strtolower($s['variable']);

    if($type==='tempHum' && !isset($grouped['tempHum'])){
        $grouped['tempHum'] = [
            'name'=>'DHT22','icon'=>'🌡️','id'=>$s['id'],
            'sensor_type'=>'tempHum','variable'=>'tempHum',
            'lines'=>$unitMap['tempHum']
        ];
    }
    elseif($type==='mq135' && !isset($grouped['mq135'])){
        $grouped['mq135'] = [
            'name'=>'MQ135','icon'=>'⛽','id'=>$s['id'],
            'sensor_type'=>'mq135','variable'=>'mq135',
            'lines'=>$unitMap['mq135']
        ];
    }
    elseif(!in_array($var,['co2','methane','butane','propane','temp','hum'],true)){
        $grouped[$var] = [
            'name'=>$s['name'],'icon'=>$s['icon'],'id'=>$s['id'],
            'sensor_type'=>$type,'variable'=>$var,
            'lines'=>$unitMap[$var] ?? [['label'=>'','spanId'=>"$var".'Val','unit'=>'']]
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

// → Inserta aquí (línea 25 antes de if !isset)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['actuator_id'],$_POST['new_name'])) {
    $stmt = $pdo->prepare('UPDATE devices SET name=? WHERE id=?');
    $stmt->execute([trim($_POST['new_name']), (int)$_POST['actuator_id']]);
    // Redirige para evitar resubmit
    header('Location: '.BASE_PATH."/dashboard?place={$_POST['place']}&device={$_POST['device']}");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/login');
    exit;
}
$user_img = $_SESSION['profile_image'] ?? 'default.png';

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
  <!-- Estilos -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/addSensor.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/mobiles.css">
  

  <!-- SweetAlert2 -->
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
      <img src="<?= BASE_PATH ?>/assets/img/logo-small.png" alt="Logo" class="logo" />
    </div>
    <i id="themeToggleDrawer" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
    <i id="langToggleDrawer"  class="ri-earth-line icon-btn"  title="ES/EN"></i>
    <i id="notifToggleDrawer"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>
    <!-- dentro de .topbar-right -->


    <a href="<?= BASE_PATH ?>/logout" class="icon-btn" title="Salir"><i class="ri-logout-box-line"></i></a>
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
        <li data-page="devices">
          <i class="ri-cpu-line"></i>
          <span class="menu-text">Devices</span>
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
          <img src="<?= BASE_PATH ?>/assets/img/logo.png" id="logo" class="logo" alt="Logo">
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

  <!-- MUEVE EL HAMBURGER AQUÍ -->
  <button class="hamburger" id="hamburger" aria-label="Menú móvil">
    <span></span><span></span><span></span>
  </button>

  <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($user['profile_image'], ENT_QUOTES) ?>"
       class="profile-img icon-btn" alt="Perfil">
  <a href="<?= BASE_PATH ?>/logout" class="icon-btn"><i class="ri-logout-box-line"></i></a>
</div>

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
          <i id="showReboots" class="ri-history-line icon-btn" title="Historial de reinicios"></i>
          <i id="doReboot" class="ri-restart-line icon-btn" title="Reset remoto"></i>
          <span id="lastReset" class="last-reset">Último reset: —</span>
        </div>
 
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

      <!-- Devices Page -->
      <section id="devices" class="page">
        <h1>Mis Dispositivos</h1>
        <!-- ABM Devices aquí -->
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
  window.BASE_PATH = '<?= BASE_PATH ?>';
</script>
<script>
  window.BASE_PATH = '/medtucIoT';  // ¡ojo con mayúsculas/minúsculas!
</script>
<script>
  const currentDeviceId = <?= (int)$currentDeviceId ?>;
  const BASE_PATH = '<?= BASE_PATH ?>';
</script>
<script defer src="<?= BASE_PATH ?>/assets/js/main.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/addSensor.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/charts_sensores.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/pwa.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/pwa.js"></script>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/medtucIoT/app/service-wojer.js', {
    scope: '/medtucIoT/app/'
  })
  .then(reg => {
    console.log('SW registrado', reg);
    // Si ya está activo—pero no controla—hagamos reload
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





  <script>
    function onPlaceChange() {
      const p = document.getElementById('placeSelect').value;
      const d = document.getElementById('deviceSelect').value;
      window.location = '<?= BASE_PATH ?>/dashboard?place=' + p + '&device=' + d;
    }
    function onDeviceChange() {
      onPlaceChange();
    }
  </script>
</body>
</html>
