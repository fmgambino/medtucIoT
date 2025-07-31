<?php
require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores en desarrollo
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

if (!isset($_GET['deviceId']) || !is_numeric($_GET['deviceId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de dispositivo inválido']);
    exit;
}

$deviceId = (int) $_GET['deviceId'];

try {
    // Buscar el dispositivo del usuario
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.esp32_id, d.mac, d.wifi, d.ip, d.rssi, d.mqtt_status, d.cpu_temp, d.uptime, d.place
        FROM devices d
        WHERE d.id = :deviceId AND d.user_id = :userId
        LIMIT 1
    ");
    $stmt->execute([
        ':deviceId' => $deviceId,
        ':userId' => $userId
    ]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '📛 Dispositivo no encontrado']);
        exit;
    }

    // Mapear nombres por claridad
    $status = [
        'name'      => $device['name'],
        'esp32_id'  => $device['esp32_id'],
        'mac'       => $device['mac'],
        'wifi'      => $device['wifi'],
        'ip'        => $device['ip'],
        'rssi'      => $device['rssi'],
        'mqtt'      => $device['mqtt_status'],
        'cpu_temp'  => $device['cpu_temp'],
        'uptime'    => $device['uptime'],
        'place'     => $device['place'],
    ];

    echo json_encode([
        'success' => true,
        'status'  => $status
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("DB error in get_device_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error en servidor']);
}
