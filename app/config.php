<?php
// /app/config.php
date_default_timezone_set('America/Argentina/Buenos_Aires');

// ----------------------
// 1) Detectar entorno y definir BASE_PATH automáticamente
// ----------------------
$hostName = $_SERVER['HTTP_HOST'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isLocal = strpos($hostName, 'localhost') !== false || strpos($hostName, '127.0.0.1') !== false;

// BASE_PATH vacío en producción (subdominio apunta a carpeta /medtuc)
if ($isLocal) {
    define('BASE_PATH', '/medtucIoT'); // Ruta en entorno local (ej: localhost/medtucIoT)
} else {
    define('BASE_PATH', ''); // Ruta en producción → no debe incluir /medtuc
}

// ----------------------
// 2) Configuración de la base de datos
// ----------------------
if ($isLocal) {
    $dbHost = '127.0.0.1';
    $dbName = 'medtuciot';
    $dbUser = 'root';
    $dbPass = '';
} else {
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
    // En producción podrías loguear el error a un archivo
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
