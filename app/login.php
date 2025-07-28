<?php
// /medtuciot/app/login.php
session_start();
require __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/dashboard');
    exit;
}

$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($username) || empty($password) || empty($captchaResponse)) {
        header('Location: ' . BASE_PATH . '/login?error=campos');
        exit;
    }

    // Validar reCAPTCHA
    $secretKey = '6LcVI44rAAAAAJ3hKeeGXGrnAGdJ2ETm_KahqkYY';
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}");
    $captchaSuccess = json_decode($verify);

    if (!$captchaSuccess->success) {
        header('Location: ' . BASE_PATH . '/login?error=captcha');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email AS username, password_hash, role, profile_image FROM users WHERE email = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['username'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'];
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        } else {
            header('Location: ' . BASE_PATH . '/login?error=invalid');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: ' . BASE_PATH . '/login?error=db');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="<?= BASE_PATH ?>/">
  <title data-i18n="login">Iniciar sesión - MedTuCIoT</title>
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="icon" href="<?= rtrim(BASE_PATH, '/') ?>/assets/img/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="container">
    <div class="form-container sign-in-container">
      <form action="login" method="POST" autocomplete="on">
        <h1 data-i18n="login">Iniciar sesión</h1>
        <input type="email" name="username" placeholder="Correo electrónico" autocomplete="username" required>
        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="Contraseña" autocomplete="current-password" required>
          <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar contraseña">
            <i id="toggleIcon" class="fa fa-eye"></i>
          </button>
        </div>
        <div class="g-recaptcha" data-sitekey="6LcVI44rAAAAAC3uIKeD_QMXZpvWIF8QBT5oLGrA"></div>
        <div class="options">
          <label for="remember">
            <input type="checkbox" id="remember" name="remember">
            <span data-i18n="remember">Recuérdame</span>
          </label>
          <a href="#" onclick="event.preventDefault(); showRecovery();">¿Olvidaste tu contraseña?</a>
        </div>
        <button type="submit" class="btn" data-i18n="login">Ingresar</button>
      </form>
      <?php if ($error): ?>
        <script>
          const messages = {
            campos: 'Por favor, completa todos los campos.',
            invalid: 'Correo o contraseña incorrectos.',
            captcha: 'Verificación de reCAPTCHA fallida.',
            db: 'Error de conexión con la base de datos.'
          };
          Swal.fire({ icon: 'error', title: '❌', text: messages['<?= addslashes($error) ?>'] || 'Ha ocurrido un error.' });
        </script>
      <?php endif; ?>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="top-controls">
          <label class="switch" title="Cambiar tema">
            <input type="checkbox" id="themeSwitcher"><span class="slider"></span>
          </label>
          <button class="lang-toggle" onclick="toggleLanguage()" title="Cambiar idioma">🇪🇸/🇺🇸</button>
        </div>
        <div class="overlay-panel overlay-right">
          <img class="logo" src="assets/img/logo-dark.png" alt="Logo MedTuCIoT">
          <h1 data-i18n="createAccount">¿Nuevo aquí?</h1>
          <p data-i18n="subtitle">Crea una cuenta para empezar a monitorizar tus dispositivos</p>
          <a href="register"><button class="ghost" data-i18n="register">Registrarse</button></a>
        </div>
      </div>
    </div>
  </div>
  <div class="auth-footer">
    <span data-i18n="footer">Bienvenido al sistema de monitoreo IoT.</span><br>
    <span data-i18n="powered">Powered by</span> <a href="https://electronicagambino.com" target="_blank">Electrónica Gambino</a>
  </div>
  <script src="assets/js/auth.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
        this.setAttribute('aria-label','Ocultar contraseña');
      } else {
        pwd.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
        this.setAttribute('aria-label','Mostrar contraseña');
      }
    });
  </script>
</body>
</html>
