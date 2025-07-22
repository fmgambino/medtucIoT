<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_GET['deviceId'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Falta el parámetro deviceId']);
  exit;
}

$deviceId = (int) $_GET['deviceId'];

try {
  $result = [];

  // Consulta todo el sensor_data reciente
  $stmt = $pdo->prepare("
    SELECT sensor_type, value, unit, timestamp
    FROM sensor_data
    WHERE device_id = ?
    AND timestamp >= NOW() - INTERVAL 1 DAY
    ORDER BY timestamp DESC
  ");
  $stmt->execute([$deviceId]);
  $allData = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $latest = [];
  foreach ($allData as $row) {
    $type = $row['sensor_type'];
    if (!isset($latest[$type])) {
      $latest[$type] = $row;
    }
  }

  // Agrupar temp + hum => tempHum
  $temp = $latest['temp']['value'] ?? null;
  $hum  = $latest['hum']['value']  ?? null;
  if ($temp !== null || $hum !== null) {
    $result[] = [
      'sensor_type' => 'tempHum',
      'value' => ['temp' => $temp, 'hum' => $hum]
    ];
    unset($latest['temp'], $latest['hum']);
  }

  // Agrupar gases MQ135
  $gases = ['co2', 'methane', 'butane', 'propane'];
  $gasValues = [];
  foreach ($gases as $g) {
    if (isset($latest[$g])) {
      $gasValues[$g] = $latest[$g]['value'];
      unset($latest[$g]);
    }
  }
  if (!empty($gasValues)) {
    $result[] = [
      'sensor_type' => 'mq135',
      'value' => $gasValues
    ];
  }

  // El resto de sensores
  foreach ($latest as $row) {
    $result[] = [
      'sensor_type' => $row['sensor_type'],
      'value'       => $row['value'],
      'unit'        => $row['unit'],
      'timestamp'   => $row['timestamp']
    ];
  }

  echo json_encode($result);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en servidor: ' . $e->getMessage()]);
}
