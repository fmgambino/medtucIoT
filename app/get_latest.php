<?php
require_once 'config.php'; // contiene conexión $pdo
header('Content-Type: application/json');

if (!isset($_GET['deviceId'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Falta el parámetro deviceId']);
  exit;
}

$deviceId = (int) $_GET['deviceId'];

try {
  // Obtener todos los tipos de sensores distintos para ese device
  $stmt = $pdo->prepare("
    SELECT sensor_type, MAX(timestamp) as last_time
    FROM sensor_data
    WHERE device_id = ?
    GROUP BY sensor_type
  ");
  $stmt->execute([$deviceId]);
  $sensorTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $result = [];

  foreach ($sensorTypes as $row) {
    $sensorType = $row['sensor_type'];

    // Obtener últimas 10 entradas de ese tipo
    $stmt = $pdo->prepare("
      SELECT sensor_type, value, unit, timestamp
      FROM sensor_data
      WHERE device_id = ? AND sensor_type = ?
      ORDER BY timestamp DESC
      LIMIT 10
    ");
    $stmt->execute([$deviceId, $sensorType]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupación especial: tempHum
    if (in_array($sensorType, ['temp', 'hum'])) {
      $temp = null;
      $hum  = null;
      foreach ($data as $d) {
        if ($d['sensor_type'] === 'temp') $temp = $d['value'];
        if ($d['sensor_type'] === 'hum')  $hum  = $d['value'];
      }
      // Solo agregar si hay al menos un valor
      if ($temp !== null || $hum !== null) {
        $result[] = [
          'sensor_type' => 'tempHum',
          'value' => ['temp' => $temp, 'hum' => $hum]
        ];
      }
    }

    // Agrupación especial: mq135
    elseif (in_array($sensorType, ['co2', 'methane', 'butane', 'propane'])) {
      $map = [];
      foreach ($data as $d) {
        $map[$d['sensor_type']] = $d['value'];
      }
      if (!empty($map)) {
        $result[] = [
          'sensor_type' => 'mq135',
          'value' => $map
        ];
      }
    }

    // Sensores simples
    else {
      $latest = $data[0] ?? null;
      if ($latest) {
        $result[] = [
          'sensor_type' => $latest['sensor_type'],
          'value'       => $latest['value'],
          'unit'        => $latest['unit'],
          'timestamp'   => $latest['timestamp']
        ];
      }
    }
  }

  echo json_encode($result);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en servidor: ' . $e->getMessage()]);
}
