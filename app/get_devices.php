<?php
// get_device_status.php

require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores solo en desarrollo local
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Verificar sesión activa
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Validar parámetro deviceId
$deviceId = filter_input(INPUT_GET, 'deviceId', FILTER_VALIDATE_INT);
if (!$deviceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '❌ ID de dispositivo inválido']);
    exit;
}

try {
    // Buscar el dispositivo asociado al usuario
    $stmt = $pdo->prepare("
        SELECT id, name, esp32_id, serial_number, icono, ubicacion, mapa
        FROM devices
        WHERE id = :deviceId AND user_id = :userId
        LIMIT 1
    ");
    $stmt->execute([
        ':deviceId' => $deviceId,
        ':userId'   => $userId
    ]);

    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '📛 Dispositivo no encontrado']);
        exit;
    }

    // Formatear respuesta
    $status = [
        'name'        => $device['name'],
        'esp32_id'    => $device['esp32_id'],
        'serial'      => $device['serial_number'],
        'icono'       => $device['icono'],
        'ubicacion'   => $device['ubicacion'],
        'mapa'        => $device['mapa']
    ];

    echo json_encode([
        'success' => true,
        'status'  => $status
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("🛑 DB error in get_device_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error interno del servidor']);
}
