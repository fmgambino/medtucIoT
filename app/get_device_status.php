<?php
// get_device_status.php

require_once "config.php";
session_start();

// -------------------------
// ↪ Encabezados de seguridad y respuesta
// -------------------------
header('Content-Type: application/json; charset=utf-8');

// Mostrar errores solo en desarrollo local
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// -------------------------
// ↪ Verificar autenticación
// -------------------------
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// -------------------------
// ↪ Validar parámetro de entrada
// -------------------------
$deviceId = filter_input(INPUT_GET, 'deviceId', FILTER_VALIDATE_INT);

if (!$deviceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de dispositivo inválido']);
    exit;
}

// -------------------------
// ↪ Consulta segura a la base de datos
// -------------------------
try {
    $sql = "
        SELECT 
            d.name,
            d.esp32_id,
            d.mac,
            d.wifi,
            d.ip,
            d.rssi,
            d.mqtt_status,
            d.cpu_temp,
            d.uptime,
            d.place
        FROM devices d
        WHERE d.id = :deviceId AND d.user_id = :userId
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sql);
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

    // -------------------------
    // ↪ Respuesta exitosa
    // -------------------------
    echo json_encode([
        'success' => true,
        'status'  => $device
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '❌ Error DB: ' . $e->getMessage()
    ]);
}

