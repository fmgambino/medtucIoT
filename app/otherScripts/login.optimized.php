<?php
// /medtuciot/app/login.php
session_start();
require __DIR__ . '/config.php';

// Si ya está autenticado, redirige al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/dashboard');
    exit;
}

$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');  // aquí username en realidad es el correo
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: ' . BASE_PATH . '/login?error=campos');
        exit;
    }

    try {
        // Cambiado para buscar por email en lugar de por username
        $stmt = $pdo->prepare(
            "SELECT 
                id, 
                email AS username, 
                password_hash, 
                role, 
                profile_image
             FROM users 
             WHERE email = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['username'];        // será el email
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= BASE_PATH ?>/">
  <title>Iniciar sesión – MedTuCIoT</title>
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="container">
    <div class="form-container sign-in-container">
      <form action="login" method="POST">
        <h1>Iniciar sesión</h1>
        <input type="text" name="username" placeholder="Usuario" required>
        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="Contraseña" required>
          <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar contraseña">
            <i id="toggleIcon" class="fa fa-eye"></i>
          </button>
        </div>
        <div class="options">
          <label>
            <input type="checkbox" name="remember"> Recuérdame
          </label>
          <a href="register" class="forgot-password">¿Nuevo aquí? Regístrate</a>
        </div>
        <button type="submit" class="btn">Ingresar</button>
      </form>
      <?php if ($error): ?>
        <script>
          const messages = {
            campos:   'Por favor, completa todos los campos.',
            invalid:  'Correo o contraseña incorrectos.',
            db:       'Error de conexión con la base de datos.'
          };
          Swal.fire({
            icon: 'error',
            title: '❌',
            text: messages['<?= addslashes($error) ?>'] || 'Ha ocurrido un error.'
          });
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
          <h1>¿Nuevo aquí?</h1>
          <p>Crea una cuenta para empezar a monitorizar tus dispositivos</p>
          <a href="register"><button class="ghost">Registrarse</button></a>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-footer">
    <span>Bienvenido a MedTuCIoT.</span><br>
    <span>Powered by Electrónica Gambino</span>
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
      // Implementa cambio de idioma (ej. guardando en localStorage y recargando)
      alert('Toggle language (implementar)');
    }
  </script>
</body>
</html>
