<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

$deviceId = filter_input(INPUT_GET,'deviceId',FILTER_VALIDATE_INT);
if (!$deviceId) {
  http_response_code(400);
  echo json_encode(['error'=>'deviceId inválido']);
  exit;
}

// Por cada sensor_type distinto, toma la última fila
$sql = "
  SELECT sd.sensor_type, sd.value, sd.unit
  FROM sensor_data sd
  WHERE sd.device_id = ?
    AND sd.timestamp = (
      SELECT MAX(timestamp)
      FROM sensor_data
      WHERE device_id = sd.device_id
        AND sensor_type = sd.sensor_type
    )
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$deviceId]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
