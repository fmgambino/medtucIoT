<?php
require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión activa
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$deviceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$esp32Id = isset($_GET['esp32_id']) ? trim($_GET['esp32_id']) : '';

// Validar parámetros
if ($deviceId <= 0 && $esp32Id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '⚠️ Debes proporcionar un ID o un ESP32_ID']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Construir consulta en función del parámetro recibido
    if ($deviceId > 0) {
        $sql = "
            SELECT d.*, p.nombre AS place_name
            FROM dispositivos d
            LEFT JOIN lugares p ON d.place_id = p.id
            WHERE d.user_id = ? AND d.id = ?
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $deviceId]);
    } else {
        $sql = "
            SELECT d.*, p.nombre AS place_name
            FROM dispositivos d
            LEFT JOIN lugares p ON d.place_id = p.id
            WHERE d.user_id = ? AND d.esp32_id = ?
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $esp32Id]);
    }

    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        echo json_encode(['success' => true, 'data' => $device]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '⚠️ Dispositivo no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error en get_devices.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error en el servidor de base de datos']);
}
