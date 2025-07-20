<?php
// /medtuciot/app/config.php

// ----------------------
// 1) Definir BASE_PATH según entorno
// ----------------------
$hostName = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($hostName, 'localhost') !== false || strpos($hostName, '127.0.0.1') !== false) {
    // Entorno local (XAMPP)
    define('BASE_PATH', '/medtuciot');
} else {
    // Producción (Hostinger)
    define('BASE_PATH', '');
}

// ----------------------
// 2) Configuración de la base de datos
// ----------------------
if (strpos($hostName, 'localhost') !== false || strpos($hostName, '127.0.0.1') !== false) {
    // Entorno local (XAMPP)
    $dbHost = '127.0.0.1';
    $dbName = 'medtuciot';    // debe coincidir con la BD que importaste
    $dbUser = 'root';
    $dbPass = '';
} else {
    // Producción (Hostinger)
    $dbHost = 'localhost';
    $dbName = 'u197809344_pwa';
    $dbUser = 'u197809344_spv5';
    $dbPass = 'Jamboree0342$$';
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
    // En producción podrías loguear en lugar de mostrar el mensaje
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
