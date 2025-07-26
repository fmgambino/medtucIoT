<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

// Verificación de sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/app/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>IoT Dashboard – MedTuCIoT</title>

  <!-- Remixicon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
  <link rel="icon" href="<?= BASE_PATH ?>/app/assets/img/favicon.png" type="image/png" />

  <!-- Estilos -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/app/assets/css/styles.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/app/assets/css/addSensor.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/app/assets/css/mobiles.css">

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

<!-- Drawer móvil -->
<div class="mobile-drawer" id="mobileDrawer">
  <div class="mobile-drawer-header">
    <img src="<?= BASE_PATH ?>/app/assets/img/logo-small.png" alt="Logo" class="logo" />
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
        <img src="<?= BASE_PATH ?>/app/assets/img/logo.png" id="logo" class="logo" alt="Logo">
      </div>
      <div class="topbar-right">
        <i id="themeToggle" class="ri-sun-line icon-btn" title="Modo claro/oscuro"></i>
        <i id="langToggle"  class="ri-earth-line icon-btn" title="ES/EN"></i>
        <i id="notifToggle"class="ri-notification-3-line icon-btn" title="Notificaciones"></i>

        <button class="hamburger" id="hamburger" aria-label="Menú móvil">
          <span></span><span></span><span></span>
        </button>

        <form id="profileForm" action="<?= BASE_PATH ?>/app/imgPerfil.php" method="POST" enctype="multipart/form-data" style="display:none;">
          <input type="file" name="profile_image" id="profileInput" accept="image/*" onchange="document.getElementById('profileForm').submit();">
        </form>

        <img src="<?= BASE_PATH . '/app/' . htmlspecialchars($_SESSION['profile_image'] ?? 'assets/files/default.png') ?>?v=<?= time() ?>"
             class="profile-img icon-btn"
             alt="Perfil"
             onclick="document.getElementById('profileInput').click();"
             style="cursor:pointer; border-radius:50%; width:40px; height:40px; object-fit:cover;">

        <a href="<?= BASE_PATH ?>/logout" class="icon-btn"><i class="ri-logout-box-line"></i></a>
      </div>
    </header>
