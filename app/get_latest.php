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
  // Buscar las últimas muestras de cada tipo de sensor en las últimas 24h
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

  $result = [];

  // Si existe 'tempHum' JSON
  if (isset($latest['tempHum'])) {
    $parsed = json_decode($latest['tempHum']['value'], true);
    $result[] = [
      'sensor_type' => 'tempHum',
      'value' => [
        'temperature' => $parsed['temperature'] ?? null,
        'humidity'    => $parsed['humidity'] ?? null
      ],
      'timestamp' => $latest['tempHum']['timestamp']
    ];
    unset($latest['tempHum']);
  }

  // MQ135 gases
  if (isset($latest['mq135'])) {
    $parsed = json_decode($latest['mq135']['value'], true);
    $result[] = [
      'sensor_type' => 'mq135',
      'value' => [
        'co2'     => $parsed['co2'] ?? null,
        'methane' => $parsed['methane'] ?? null,
        'butane'  => $parsed['butane'] ?? null,
        'propane' => $parsed['propane'] ?? null
      ],
      'timestamp' => $latest['mq135']['timestamp']
    ];
    unset($latest['mq135']);
  }

  // Otros sensores individuales
  foreach ($latest as $type => $row) {
    $result[] = [
      'sensor_type' => $type,
      'value'       => is_numeric($row['value']) ? (float)$row['value'] : $row['value'],
      'unit'        => $row['unit'],
      'timestamp'   => $row['timestamp']
    ];
  }

  echo json_encode($result);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en servidor: ' . $e->getMessage()]);
}
