<?php
// /app/logout.php
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

// Redirigir al login usando ruta amigable
$baseUrl = rtrim(BASE_PATH, '/');
header("Location: {$baseUrl}/login");
exit;
