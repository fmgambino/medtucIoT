<?php
// /app/register.php
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
    $country    = trim($_POST['country']    ?? '');
    $province   = trim($_POST['province']   ?? '');
    $city       = trim($_POST['city']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = trim($_POST['password']   ?? '');

    if ($first_name === '' || $last_name === '' || $country === '' || $email === '' || $password === '') {
        header("Location: {$baseUrl}/register?error=campos");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
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
            $email,
            $hashed
        ]);

        // Email de bienvenida
        $subject = "Bienvenido a MedTuCIoT";
        $message = "Hola {$first_name},\n\nGracias por registrarte en MedTuCIoT. Ya puedes iniciar sesión y comenzar a usar la plataforma.";
        $headers = "From: no-reply@medtuc.electronicagambino.com";
        @mail($email, $subject, $message, $headers);

        header("Location: {$baseUrl}/register?success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: {$baseUrl}/register?error=db");
        exit;
    }
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

        <select name="country" id="country" required>
          <option value="" data-i18n="country">País</option>
          <!-- Puedes poblar con JS si es dinámico -->
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
            exists: 'El correo ya está registrado.',
            db:     'Error de conexión con la base de datos.'
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
    document.getElementById('togglePassword').addEventListener('click', function() {
      const pwd  = document.getElementById('password');
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

    function toggleLanguage() {
      alert('Toggle language (implementar)');
    }
  </script>
</body>
</html>