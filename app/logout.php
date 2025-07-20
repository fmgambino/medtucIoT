<?php
// /medtuciot/app/logout.php
session_start();
require __DIR__ . '/config.php';

// Limpiar toda la sesión
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirigir directamente al login.php en /app/
header('Location: ' . BASE_PATH . '/app/login.php');
exit;
