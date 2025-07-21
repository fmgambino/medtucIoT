<?php
// /medtuciot/app/dashboard.php
session_start();
require __DIR__ . '/config.php';

// — Simulación de datos (reemplaza con tus consultas reales) —
$places = [
    ['id'=>1,'name'=>'Casa'],
    ['id'=>2,'name'=>'Oficina'],
    ['id'=>3,'name'=>'Campo1'],
];
$devices_by_place = [
    1=>[['id'=>101,'name'=>'ESP32-Casa-1'],['id'=>102,'name'=>'ESP32-Casa-2'],['id'=>103,'name'=>'ESP32-Casa-3']],
    2=>[['id'=>201,'name'=>'ESP32-Oficina-1'],['id'=>202,'name'=>'ESP32-Oficina-2'],['id'=>203,'name'=>'ESP32-Oficina-3']],
    3=>[['id'=>301,'name'=>'ESP32-Campo1-1'],['id'=>302,'name'=>'ESP32-Campo1-2'],['id'=>303,'name'=>'ESP32-Campo1-3']],
];

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
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/mobiles.css">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>

</head>
<body>
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
        <div class="sensor-grid">
  <div class="widget">
   <h2>🌡️ DHT22 <i class="ri-line-chart-fill chart-icon" data-sensor="DHT22" title="Ver gráfico DHT22"></i></h2>
    <p>Temp: <span id="tempVal">—</span> °C</p>
    <p>Hum:  <span id="humVal">—</span> %</p>
  </div>
  <div class="widget">
   <h2>🏭 MQ1325 <i class="ri-line-chart-fill chart-icon" data-sensor="MQ1325" title="Ver gráfico MQ1325"></i></h2>
    <p>CO₂:    <span id="co2Val">—</span> ppm</p>
    <!-- ... -->
  </div>
  <div class="widget">
    <h2>💧Hum. Suelo <i class="ri-line-chart-fill chart-icon" data-sensor="Hum Suelo" title="Ver gráfico Hum. Suelo"></i></h2>
    <p><span id="soilHumVal">—</span> %</p>
  </div>
  <div class="widget">
   <h2>🧪 pH <i class="ri-line-chart-fill chart-icon" data-sensor="pH" title="Ver gráfico pH"></i></h2>
    <p><span id="phVal">—</span></p>
  </div>
  <div class="widget">
   <h2>⚡ EC <i class="ri-line-chart-fill chart-icon" data-sensor="EC" title="Ver gráfico EC"></i></h2>
    <p><span id="ecVal">—</span> μS/cm</p>
  </div>
  <!-- NUESTROS NUEVOS WIDGETS -->

<div class="widget">
  <h2>💧Nivel H₂O <i class="ri-line-chart-fill chart-icon" data-sensor="Nivel H2O" title="Ver gráfico Nivel H2O"></i></h2>
  <p><span id="h2oVal">—</span> %</p>
</div>

<div class="widget">
  <h2>⛽ Nafta <i class="ri-line-chart-fill chart-icon" data-sensor="Nafta" title="Ver gráfico Nafta"></i></h2>
  <p><span id="naftaVal">—</span> %</p>
</div>

<div class="widget">
  <h2>🛢️ Aceite <i class="ri-line-chart-fill chart-icon" data-sensor="Aceite" title="Ver gráfico Aceite"></i></h2>
  <p><span id="aceiteVal">—</span> %</p>
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

  <!-- Scripts -->
  <script defer src="<?= BASE_PATH ?>/assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>

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
