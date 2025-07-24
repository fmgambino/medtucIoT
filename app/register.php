<?php
session_start();
require __DIR__ . '/config.php';

$baseUrl = rtrim(BASE_PATH, '/');

// Redirige si ya está autenticado
if (isset($_SESSION['user_id'])) {
    header("Location: {$baseUrl}/dashboard");
    exit;
}

$error   = $_GET['error'] ?? '';
$success = isset($_GET['success']);

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $country    = trim($_POST['country'] ?? '');
    $province   = trim($_POST['province'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $captcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Validaciones
    if (in_array('', [$first_name, $last_name, $username, $country, $email, $password])) {
        header("Location: {$baseUrl}/register?error=campos");
        exit;
    }

    if ($password !== $confirm_password) {
        header("Location: {$baseUrl}/register?error=passwords_no_match");
        exit;
    }

    if (empty($captcha_response) || !validateCaptcha($captcha_response)) {
        header("Location: {$baseUrl}/register?error=captcha");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            header("Location: {$baseUrl}/register?error=exists");
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("
            INSERT INTO users (first_name, last_name, country, province, city, email, username, password_hash, subscription_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Prospecto')
        ");
        $insert->execute([$first_name, $last_name, $country, $province, $city, $email, $username, $hashed]);

        // Enviar correo de bienvenida
        sendWelcomeEmail($email, $first_name, $last_name);
        header("Location: {$baseUrl}/register?success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: {$baseUrl}/register?error=db");
        exit;
    }
}

// Validar reCAPTCHA
function validateCaptcha($captcha) {
    $secretKey = '6LcVI44rAAAAAJ3hKeeGXGrnAGdJ2ETm_KahqkYY'; // Reemplaza con tu clave secreta válida
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}");
    $result = json_decode($response, true);
    return $result['success'] ?? false;
}

// Enviar email
function sendWelcomeEmail($email, $first_name, $last_name) {
    $subject = "Bienvenido a MedTuCIoT";
    $loginUrl = "https://medtuc.electronicagambino.com/login";
    $logoUrl  = "https://www.educaciontuc.gov.ar/wp-content/uploads/2024/10/mnisteriodeeducacion.webp";

$message = "
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; color: #333; }
    .email-container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
    .email-logo { max-width: 200px; margin-bottom: 20px; }
    h1 { color: #003366; }
    .login-button { margin-top: 25px; padding: 12px 25px; background: #0073e6; color: #fff; text-decoration: none; font-weight: bold; border-radius: 6px; display: inline-block; }
  </style>
</head>
<body>
  <div class='email-container'>
    <img class='email-logo' src='{$logoUrl}' alt='Ministerio de Educación'>
    <h1>¡Bienvenido a MedTuCIoT, {$first_name} {$last_name}!</h1>
    <p>Gracias por registrarte en nuestra plataforma de monitoreo y control IoT educativo.</p>
    <p>A partir de ahora podrás acceder a tu panel de usuario y comenzar a gestionar tus dispositivos conectados de forma eficiente y segura.</p>
    <p>Haz clic en el siguiente botón para iniciar sesión:</p>
    <a class='login-button' href='{$loginUrl}'>Iniciar Sesión</a>
    <p style='margin-top: 40px; font-size: 0.9em; color: #666;'>
      Ministerio de Educación de Tucumán – Plataforma MedTuCIoT<br>
      © 2025 Electrónica Gambino
    </p>
  </div>
</body>
</html>";


    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: MedTuCIoT <no-reply@medtuc.electronicagambino.com>\r\n";

    @mail($email, $subject, $message, $headers);
}
?>
<!-- ... encabezado del HTML ... -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= htmlspecialchars(BASE_PATH) ?>/">
  <title>Registrarse – MedTuCIoT</title>
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="container">
    <div class="form-container sign-up-container">
      <form action="register" method="POST" novalidate>
        <!-- Logo solo para móviles -->
        <img class="logo mobile-only" src="assets/img/logo-dark.png" alt="Logo MedTuCIoT">
        
        <h1>Registrarse</h1>
        <div class="form-row">
          <input type="text" name="first_name" placeholder="Nombre" required>
          <input type="text" name="last_name" placeholder="Apellido" required>
        </div>
        <select name="country" id="country" required>
          <option value="">País</option>
        </select>

        <input type="text" name="province" placeholder="Provincia / Estado">
        <input type="text" name="city" placeholder="Ciudad">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="text" name="username" placeholder="Usuario" required>
        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="Contraseña" required>
          <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar contraseña">
            <i id="toggleIcon" class="fa fa-eye"></i>
          </button>
        </div>
        <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
        <div class="g-recaptcha" data-sitekey="6LcVI44rAAAAAC3uIKeD_QMXZpvWIF8QBT5oLGrA"></div>

        <div class="options">
          <label><input type="checkbox" name="remember"> Recuérdame</label>
          <a href="login">¿Ya tienes una cuenta?</a>
        </div>

        <button type="submit" class="btn">Registrarse</button>
      </form>

      <?php if ($success): ?>
        <script>Swal.fire({ icon: 'success', title: '✔️', text: 'Registro exitoso. ¡Bienvenido!' });</script>
      <?php elseif ($error): ?>
        <script>
          const messages = {
            campos: 'Completa todos los campos.',
            exists: 'Correo o usuario ya registrados.',
            passwords_no_match: 'Las contraseñas no coinciden.',
            db: 'Error de base de datos.',
            captcha: 'Captcha inválido o no verificado.'
          };
          Swal.fire({ icon: 'error', title: '❌', text: messages['<?= addslashes($error) ?>'] ?? 'Error desconocido' });
        </script>
      <?php endif; ?>
    </div>

    <div class="overlay-container">
      <div class="overlay">
        <div class="top-controls">
          <label class="switch"><input type="checkbox" id="themeSwitcher"><span class="slider"></span></label>
          <button class="lang-toggle" onclick="toggleLanguage()">🇪🇸/🇺🇸</button>
        </div>
        <div class="overlay-panel overlay-right">
          <!-- Logo visible solo en escritorio -->
          <img class="logo desktop-only" src="assets/img/logo-dark.png" alt="Logo MedTuCIoT">
          <h1>¿Ya tienes cuenta?</h1>
          <p>Inicia sesión para acceder al dashboard</p>
          <a href="login"><button class="ghost">Iniciar Sesión</button></a>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-footer">
    Bienvenido a MedTuCIoT. <br> Powered by <a href="https://electronicagambino.com" target="_blank">Electrónica Gambino</a>
  </div>

  <script src="assets/js/auth.js"></script>
  <script>
    // Alternar visibilidad de contraseña
    document.getElementById('togglePassword').addEventListener('click', function () {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });

    // Toggle idioma
    function toggleLanguage() {
      alert('Funcionalidad de idioma próximamente');
    }

  async function loadCountries() {
    const select = document.getElementById('country');
    try {
      const res = await fetch('https://restcountries.com/v3.1/all');
      const countries = await res.json();

      countries
        .sort((a, b) => a.name.common.localeCompare(b.name.common))
        .forEach(country => {
          const option = document.createElement('option');
          option.value = country.name.common;
          option.textContent = country.name.common;
          select.appendChild(option);
        });
    } catch (err) {
      console.error('No se pudo cargar la lista de países:', err);
    }
  </script>
</body>
</html>

