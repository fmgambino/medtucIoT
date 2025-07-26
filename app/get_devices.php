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

// Validar parámetro requerido
if ($deviceId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '⚠️ ID de dispositivo no válido']);
    exit;
}

try {
    // Conexión segura con PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consulta segura
    $sql = "
        SELECT d.*, p.nombre AS place_name
        FROM dispositivos d
        LEFT JOIN lugares p ON d.place_id = p.id
        WHERE d.user_id = ? AND d.id = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $deviceId]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        echo json_encode(['success' => true, 'data' => $device]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '⚠️ Dispositivo no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error al conectar con la base de datos']);
}
