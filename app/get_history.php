<?php
require_once "config.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verifica sesión
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$deviceId  = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$variable  = $_GET['variable'] ?? '';
$start     = $_GET['start'] ?? null;
$end       = $_GET['end'] ?? null;

if (!$deviceId || !$variable) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '❌ Parámetros insuficientes']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Validar que el dispositivo pertenece al usuario
    $stmt = $pdo->prepare("SELECT id FROM devices WHERE id = :deviceId AND user_id = :userId");
    $stmt->execute([':deviceId' => $deviceId, ':userId' => $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => '❌ Acceso no autorizado al dispositivo']);
        exit;
    }

    // Armar query
    $query = "SELECT timestamp, value FROM readings WHERE device_id = :deviceId AND variable = :variable";
    $params = [':deviceId' => $deviceId, ':variable' => $variable];

    if ($start && $end) {
        $query .= " AND timestamp BETWEEN :start AND :end";
        $params[':start'] = $start;
        $params[':end']   = $end;
    }

    $query .= " ORDER BY timestamp ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (get_history): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor.']);
}
