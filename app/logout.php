<?php
// /app/logout.php
session_start();
require __DIR__ . '/config.php';

// Limpiar sesión completamente
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Redirigir al login dentro de /app/
$baseUrl = rtrim(BASE_PATH, '/');
header("Location: {$baseUrl}/app/login.php");
exit;
