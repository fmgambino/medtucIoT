<?php
// /medtuciot/app/get_history.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

// Leer y validar parámetros
$deviceId   = filter_input(INPUT_GET,  'deviceId',   FILTER_VALIDATE_INT);
$sensorType = filter_input(INPUT_GET,  'sensorType', FILTER_SANITIZE_STRING);
$date       = filter_input(INPUT_GET,  'date',       FILTER_SANITIZE_STRING) ?: date('Y-m-d');

if (!$deviceId || !$sensorType) {
    http_response_code(400);
    echo json_encode(['error'=>'Parámetros inválidos']);
    exit;
}

// JSON compuestos (almacenados como valor único)
$compositeSensors = ['tempHum', 'mq135'];

if (in_array($sensorType, $compositeSensors)) {
    // Obtener registros donde sensor_type = 'tempHum' o 'mq135'
    $sql = "
      SELECT sensor_type, value, '' AS unit, timestamp
      FROM sensor_data
      WHERE device_id   = :device_id
        AND sensor_type = :sensor_type
        AND DATE(timestamp) = :date
      ORDER BY timestamp ASC
    ";
} else {
    // Sensores simples (1 valor por fila)
    $sql = "
      SELECT sensor_type, value, unit, timestamp
      FROM sensor_data
      WHERE device_id   = :device_id
        AND sensor_type = :sensor_type
        AND DATE(timestamp) = :date
      ORDER BY timestamp ASC
    ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'device_id'   => $deviceId,
    'sensor_type' => $sensorType,
    'date'        => $date
]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver en formato JSON
echo json_encode($rows);
