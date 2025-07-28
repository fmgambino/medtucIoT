<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

// Redirige si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/app/login.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>IoT Dashboard – MedTuCIoT</title>

  <!-- Remixicon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
  <link rel="icon" href="<?= BASE_PATH ?>/assets/img/favicon.png">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/addSensor.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/mobiles.css">

  <!-- Librerías JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@^2.0.0"></script>
</head>
<body>

<!-- Botón instalación PWA -->
<button id="btnInstall" style="display:none; position:fixed; bottom:1rem; right:1rem; background:#2196F3; color:#fff; border:none; padding:0.5rem 1rem; border-radius:4px; z-index:1000;">
  📲 Instalar App
</button>

<!-- Drawer móvil -->
<div class="mobile-drawer" id="mobileDrawer">
  <div class="mobile-drawer-header">
    <img src="<?= BASE_PATH ?>/assets/img/logo-small.png" alt="Logo Drawer">
  </div>
  <i id="themeToggleDrawer" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
  <i id="langToggleDrawer"  class="ri-earth-line icon-btn"  title="ES/EN"></i>
  <i id="notifToggleDrawer"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>
  <a href="<?= BASE_PATH ?>/logout" class="icon-btn" title="Salir"><i class="ri-logout-box-line"></i></a>
</div>

<div id="wrapper">
  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar collapsed">
    <ul class="menu">
      <li class="menu-header">IOT PANEL</li>

      <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <a href="<?= BASE_PATH ?>/dashboard">
          <i class="ri-dashboard-line"></i>
          <span class="menu-text">Dashboard</span>
        </a>
      </li>

      <li class="<?= $currentPage === 'devices' ? 'active' : '' ?>">
        <a href="<?= BASE_PATH ?>/devices.php">
          <i class="ri-cpu-line"></i>
          <span class="menu-text">Devices</span>
        </a>
      </li>

      <li class="<?= $currentPage === 'config' ? 'active' : '' ?>">
        <a href="<?= BASE_PATH ?>/config.php">
          <i class="ri-settings-3-line"></i>
          <span class="menu-text">Configuraciones</span>
        </a>
      </li>

      <li class="<?= $currentPage === 'broker' ? 'active' : '' ?>">
        <a href="<?= BASE_PATH ?>/broker.php">
          <i class="ri-cloud-line"></i>
          <span class="menu-text">Broker MQTT</span>
        </a>
      </li>

      <li class="<?= $currentPage === 'profile' ? 'active' : '' ?>">
        <a href="<?= BASE_PATH ?>/profile.php">
          <i class="ri-user-settings-line"></i>
          <span class="menu-text">Mi Perfil</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <img src="<?= BASE_PATH ?>/assets/img/logo.png" id="logo" class="logo" alt="Logo">

      <?php if ($currentPage === 'dashboard'): ?>
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
      <?php endif; ?>
    </div>

    <div class="topbar-right">
      <i id="themeToggle" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
      <i id="langToggle"  class="ri-earth-line icon-btn"  title="ES/EN"></i>
      <i id="notifToggle"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>

      <!-- Menú hamburguesa -->
      <button class="hamburger" id="hamburger" aria-label="Menú móvil">
        <span></span><span></span><span></span>
      </button>

      <!-- Imagen de perfil y logout -->
      <form id="profileForm" action="<?= BASE_PATH ?>/app/imgPerfil.php" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="file" name="profile_image" id="profileInput" accept="image/*" onchange="document.getElementById('profileForm').submit();">
      </form>

      <img src="<?= BASE_PATH . '/' . htmlspecialchars($_SESSION['profile_image'] ?? 'assets/files/default.png') ?>?v=<?= time() ?>"
           class="profile-img icon-btn"
           alt="Perfil"
           onclick="document.getElementById('profileInput').click();"
           style="cursor:pointer; border-radius:50%; width:40px; height:40px; object-fit:cover;">

      <a href="<?= BASE_PATH ?>/logout" class="icon-btn"><i class="ri-logout-box-line"></i></a>
    </div>
  </header>
