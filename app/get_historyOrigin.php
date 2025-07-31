<?php
// /medtuciot/app/get_history.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

// 🛠️ Agregar conexión PDO
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer y validar parámetros
$deviceId   = filter_input(INPUT_GET,  'deviceId',   FILTER_SANITIZE_STRING);
$sensorType = filter_input(INPUT_GET,  'sensorType', FILTER_SANITIZE_STRING);
$date       = filter_input(INPUT_GET,  'date',       FILTER_SANITIZE_STRING) ?: date('Y-m-d');

if (!$deviceId || !$sensorType) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

// Lista de sensores que guardan JSON compuesto en el campo 'value'
$compositeSensors = ['tempHum', 'mq135'];

// Query dinámica según tipo de sensor
if (in_array($sensorType, $compositeSensors)) {
    $sql = "
      SELECT
        sensor_type,
        value,
        '' AS unit,
        timestamp
      FROM sensor_data
      WHERE device_id = :device_id
        AND sensor_type = :sensor_type
        AND DATE(timestamp) = :date
      ORDER BY timestamp ASC
    ";
} else {
    $sql = "
      SELECT
        sensor_type,
        value,
        unit,
        timestamp
      FROM sensor_data
      WHERE device_id = :device_id
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

// 🧪 Log útil para debug si falla en frontend
error_log("get_history.php → deviceId=$deviceId, sensorType=$sensorType, count=" . count($rows));

// Enviar datos al frontend
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
