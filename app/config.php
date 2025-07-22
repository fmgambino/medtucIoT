<?php
// /medtuciot/app/config.php

// ----------------------
// 1) Detectar entorno y definir BASE_PATH automáticamente
// ----------------------
$hostName = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = strpos($hostName, 'localhost') !== false || strpos($hostName, '127.0.0.1') !== false;

if ($isLocal) {
    // Entorno local (XAMPP)
    defined('BASE_PATH') || define('BASE_PATH', '/medtucIoT/');
} else {
    // Producción (Hostinger - subdominio apunta al root del proyecto)
    define('BASE_PATH', '/');
}

// ----------------------
// 2) Configuración de la base de datos
// ----------------------
if ($isLocal) {
    // Entorno local (XAMPP)
    $dbHost = '127.0.0.1';
    $dbName = 'medtuciot';    // debe coincidir con tu base de datos local
    $dbUser = 'root';
    $dbPass = '';
} else {
    // Producción (Hostinger)
    $dbHost = 'localhost';
    $dbName = 'u197809344_medtuciot';
    $dbUser = 'u197809344_fmgiot';
    $dbPass = 'Jamboree0381$$';
}

// ----------------------
// 3) Conexión PDO
// ----------------------
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En producción puedes registrar el error en un log
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
