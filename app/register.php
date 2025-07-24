<?php
session_start();
require __DIR__ . '/config.php';

$baseUrl = rtrim(BASE_PATH, '/');

if (isset($_SESSION['user_id'])) {
    header("Location: {$baseUrl}/dashboard");
    exit;
}

$error   = $_GET['error']   ?? '';
$success = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $username   = trim($_POST['username']   ?? '');
    $country    = trim($_POST['country']    ?? '');
    $province   = trim($_POST['province']   ?? '');
    $city       = trim($_POST['city']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = trim($_POST['password']   ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $captcha_response = $_POST['g-recaptcha-response'] ?? '';

    if ($first_name === '' || $last_name === '' || $username === '' || $country === '' || $email === '' || $password === '') {
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
            INSERT INTO users
                (first_name, last_name, country, province, city, email, username, password_hash, subscription_type)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, 'Prospecto')
        ");
        $insert->execute([
            $first_name,
            $last_name,
            $country,
            $province,
            $city,
            $email,
            $username,
            $hashed
        ]);

        // Email HTML de bienvenida
        $subject = "Bienvenido a MedTuCIoT";
        $loginUrl = "https://medtuc.electronicagambino.com/login";
        $logoUrl  = "https://www.educaciontuc.gov.ar/wp-content/uploads/2024/10/mnisteriodeeducacion.webp";

        $message = '
        <html>
          <head>
            <style>
              body { font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; padding: 20px; font-size: 16px; }
              .email-container {
                max-width: 600px; margin: auto; background-color: #fff; padding: 30px;
                border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;
              }
              .email-logo { max-width: 150px; margin-bottom: 20px; }
              h1 { color: #003366; font-size: 24px; }
              p { margin: 15px 0; line-height: 1.6; }
              .login-button {
                display: inline-block; margin-top: 25px; padding: 12px 25px;
                background-color: #0073e6; color: #fff; text-decoration: none;
                font-weight: bold; border-radius: 6px;
              }
              .footer { margin-top: 40px; font-size: 13px; color: #777; }
            </style>
          </head>
          <body>
            <div class="email-container">
              <img class="email-logo" src="' . $logoUrl . '" alt="Ministerio de Educación de Tucumán">
              <h1>¡Bienvenido a MedTuCIoT, ' . htmlspecialchars($first_name . ' ' . $last_name) . '!</h1>
              <p>Gracias por registrarte en nuestra plataforma de monitoreo y control IoT educativo.</p>
              <p>Ahora puedes acceder a tu panel de usuario y comenzar a gestionar tus dispositivos conectados de forma eficiente.</p>
              <a class="login-button" href="' . $loginUrl . '">Iniciar Sesión</a>
              <div class="footer">
                Ministerio de Educación de Tucumán - Plataforma MedTuCIoT<br>
                © ' . date('Y') . ' Electrónica Gambino
              </div>
            </div>
          </body>
        </html>
        ';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: MedTuCIoT <no-reply@medtuc.electronicagambino.com>\r\n";

        @mail($email, $subject, $message, $headers);

        header("Location: {$baseUrl}/register?success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: {$baseUrl}/register?error=db");
        exit;
    }
}

function validateCaptcha($captcha) {
    $secretKey = '6Les8I0rAAAAAAp5jL1MUQIZZgzRhiCjvNqk_M03'; // ← Sustituye con tu clave secreta de reCAPTCHA
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}");
    $result = json_decode($response, true);
    return $result['success'] ?? false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="<?= htmlspecialchars(BASE_PATH) ?>">
  <title data-i18n="register">Registro - MedTuCIoT</title>
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    /* Aplicar scroll en el panel izquierdo */
    .form-container.sign-up-container {
      overflow-y: auto;  /* Habilita el scroll si es necesario */
      max-height: 80vh;   /* Limita la altura del panel izquierdo */
      padding-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-container sign-up-container">
      <form action="register" method="POST">
        <h1 data-i18n="register">Crear cuenta</h1>

        <div class="form-row">
          <input type="text" name="first_name" placeholder="Nombre" required>
          <input type="text" name="last_name" placeholder="Apellido" required>
        </div>

        <!-- Campo para usuario (nickname) -->
        <input type="text" name="username" placeholder="Usuario (nombre de usuario)" required>

        <select name="country" id="country" required>
          <option value="" data-i18n="country">País</option>
        </select>

        <input type="text" name="province" placeholder="Provincia / Estado">
        <input type="text" name="city" placeholder="Ciudad">
        <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="email">

        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="new-password">
          <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar contraseña">
            <i id="toggleIcon" class="fa fa-eye"></i>
          </button>
        </div>

        <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>

        <!-- ReCAPTCHA -->
        <div class="g-recaptcha" data-sitekey="6Les8I0rAAAAABX5jxa83mh6b78OVEWJuvn5qU3C"></div>

        <div class="options">
          <label for="remember">
            <input type="checkbox" id="remember" name="remember">
            <span data-i18n="remember">Recuérdame</span>
          </label>
          <a href="login" class="forgot-password" data-i18n="haveAccount">¿Ya tienes cuenta?</a>
        </div>

        <button type="submit" class="btn" data-i18n="register">Registrarse</button>
      </form>

      <?php if ($success): ?>
        <script>
          Swal.fire({ icon: 'success', title: '✔️', text: 'Registro exitoso. ¡Bienvenido!' });
        </script>
      <?php elseif ($error): ?>
        <script>
          const msgs = {
            campos: 'Por favor, completa todos los campos.',
            exists: 'El correo o usuario ya está registrado.',
            passwords_no_match: 'Las contraseñas no coinciden.',
            db:     'Error de conexión con la base de datos.',
            captcha: 'Captcha inválido. Intenta nuevamente.'
          };
          Swal.fire({ icon: 'error', title: '❌', text: msgs['<?= addslashes($error) ?>'] ?? 'Error desconocido' });
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
          <img class="logo" src="assets/img/logo-dark.png" alt="Logo">
          <h1 data-i18n="haveAccount">¿Ya tienes cuenta?</h1>
          <p data-i18n="subtitle">Inicia sesión para acceder a tu dashboard</p>
          <a href="login"><button class="ghost" data-i18n="login">Iniciar Sesión</button></a>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-footer">
    <span data-i18n="footer">Bienvenido a MedTuCIoT.</span><br>
    <span data-i18n="powered">Powered by</span> <a href="https://electronicagambino.com" target="_blank">Electrónica Gambino</a>
  </div>

  <script src="assets/js/auth.js"></script>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });

    function toggleLanguage() {
      alert('Cambiar idioma: función por implementar');
    }
  </script>
</body>
</html>
