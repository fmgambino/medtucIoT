<?php
declare(strict_types=1);
// /medtuciot/app/sensor.php

session_start();
require __DIR__ . '/config.php';

// Mostrar errores en entorno local (quitar en producción)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Tipo de respuesta
header('Content-Type: application/json; charset=utf-8');

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Asegurar excepciones de PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_REQUEST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($action) {
        case 'get':
            if ($method !== 'GET') {
                respond(['success' => false, 'error' => 'Método no permitido'], 405);
            }
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                respond(['success' => false, 'error' => 'ID inválido'], 400);
            }
            $stmt = $pdo->prepare(
                'SELECT id, device_id, name, port, variable, icon 
                 FROM sensors 
                 WHERE id = ?'
            );
            $stmt->execute([$id]);
            $sensor = $stmt->fetch(PDO::FETCH_ASSOC);
            respond(['success' => true, 'sensor' => $sensor ?: null]);
            break;

        case 'add':
            if ($method !== 'POST') {
                respond(['success' => false, 'error' => 'Método no permitido'], 405);
            }
            $deviceId = filter_input(INPUT_POST, 'deviceId', FILTER_VALIDATE_INT);
            $name     = trim($_POST['sensorName'] ?? '');
            $port     = trim($_POST['sensorPort'] ?? '');
            $variable = trim($_POST['sensorVar'] ?? '');
            $icon     = trim($_POST['sensorIcon'] ?? '');

            if (!$deviceId || !$name || !$port || !$variable || !$icon) {
                respond(['success' => false, 'error' => 'Faltan datos'], 400);
            }

            // Verificar existencia de device_id
            $chk = $pdo->prepare('SELECT COUNT(*) FROM devices WHERE id = ?');
            $chk->execute([$deviceId]);
            if ((int)$chk->fetchColumn() === 0) {
                respond(['success' => false, 'error' => 'device_id no existe'], 400);
            }

            $stmt = $pdo->prepare('
                INSERT INTO sensors 
                  (device_id, name, port, variable, icon) 
                VALUES 
                  (:device_id, :name, :port, :variable, :icon)
            ');
            $stmt->execute([
                'device_id' => $deviceId,
                'name'      => $name,
                'port'      => $port,
                'variable'  => $variable,
                'icon'      => $icon,
            ]);

            respond([
                'success' => true,
                'id'      => (int)$pdo->lastInsertId()
            ], 201);
            break;

        case 'edit':
            if ($method !== 'POST') {
                respond(['success' => false, 'error' => 'Método no permitido'], 405);
            }
            $id       = filter_input(INPUT_POST, 'sensorId', FILTER_VALIDATE_INT);
            $deviceId = filter_input(INPUT_POST, 'deviceId', FILTER_VALIDATE_INT);
            $name     = trim($_POST['sensorName'] ?? '');
            $port     = trim($_POST['sensorPort'] ?? '');
            $variable = trim($_POST['sensorVar'] ?? '');
            $icon     = trim($_POST['sensorIcon'] ?? '');

            if (!$id || !$deviceId || !$name || !$port || !$variable || !$icon) {
                respond(['success' => false, 'error' => 'Faltan datos'], 400);
            }

            // Verificar existencia de device_id
            $chk = $pdo->prepare('SELECT COUNT(*) FROM devices WHERE id = ?');
            $chk->execute([$deviceId]);
            if ((int)$chk->fetchColumn() === 0) {
                respond(['success' => false, 'error' => 'device_id no existe'], 400);
            }

            $stmt = $pdo->prepare('
                UPDATE sensors SET
                  device_id = :device_id,
                  name      = :name,
                  port      = :port,
                  variable  = :variable,
                  icon      = :icon
                WHERE id = :id
            ');
            $stmt->execute([
                'device_id' => $deviceId,
                'name'      => $name,
                'port'      => $port,
                'variable'  => $variable,
                'icon'      => $icon,
                'id'        => $id,
            ]);
            respond(['success' => true]);
            break;

        case 'delete':
            if ($method !== 'POST') {
                respond(['success' => false, 'error' => 'Método no permitido'], 405);
            }
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
                ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                respond(['success' => false, 'error' => 'ID inválido'], 400);
            }
            $stmt = $pdo->prepare('DELETE FROM sensors WHERE id = ?');
            $stmt->execute([$id]);
            respond(['success' => true]);
            break;

        default:
            respond(['success' => false, 'error' => 'Acción no válida'], 400);
    }
} catch (PDOException $e) {
    respond(['success' => false, 'error' => 'Error de BD: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['success' => false, 'error' => $e->getMessage()], 500);
}
