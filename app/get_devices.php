<?php
require_once "config.php";
session_start();

// Encabezado JSON
header('Content-Type: application/json; charset=utf-8');

// Mostrar errores solo en desarrollo
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Verificar sesión activa
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$deviceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$esp32Id = isset($_GET['esp32_id']) ? trim($_GET['esp32_id']) : '';

// Validación
if ($deviceId <= 0 && $esp32Id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '⚠️ Se requiere ID o ESP32_ID']);
    exit;
}

try {
    // Conexión segura
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consulta dinámica
    $sql = "
        SELECT d.*, p.nombre AS place_name
        FROM dispositivos d
        LEFT JOIN lugares p ON d.place_id = p.id
        WHERE d.user_id = :userId
    ";

    if ($deviceId > 0) {
        $sql .= " AND d.id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $deviceId, PDO::PARAM_INT);
    } else {
        $sql .= " AND d.esp32_id = :esp32_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':esp32_id', $esp32Id, PDO::PARAM_STR);
    }

    $stmt->execute();
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        echo json_encode(['success' => true, 'data' => $device]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '⚠️ Dispositivo no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (get_devices): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor. Intenta más tarde.']);
}
