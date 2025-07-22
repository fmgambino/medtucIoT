<?php
// /medtuciot/app/get_history.php
declare(strict_types=1);
session_start();

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// Validación de sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Validación de parámetros
$deviceId   = filter_input(INPUT_GET, 'deviceId', FILTER_VALIDATE_INT);
$sensorType = filter_input(INPUT_GET, 'sensorType', FILTER_SANITIZE_STRING);
$date       = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?: date('Y-m-d');

if (!$deviceId || !$sensorType || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

// Sensores con múltiples valores en campo JSON
$compositeSensors = ['tempHum', 'mq135'];

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta según si es sensor compuesto
    $sql = "
        SELECT
          sensor_type,
          value,
          " . (in_array($sensorType, $compositeSensors) ? "'' AS unit" : "unit") . ",
          timestamp
        FROM sensor_data
        WHERE device_id = :device_id
          AND sensor_type = :sensor_type
          AND DATE(timestamp) = :date
        ORDER BY timestamp ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'device_id'   => $deviceId,
        'sensor_type' => $sensorType,
        'date'        => $date
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar datos
    echo json_encode([
        'success' => true,
        'count'   => count($rows),
        'data'    => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
