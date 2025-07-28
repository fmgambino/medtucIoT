<?php
require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores solo en desarrollo
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Verifica sesión activa
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    // Conexión PDO segura
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener todos los dispositivos del usuario
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = :userId ORDER BY created_at DESC");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'devices' => $devices
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (get_devices): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor. Intenta más tarde.']);
}
