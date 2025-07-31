<?php
require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores en desarrollo
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Verificar sesión
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    // Usar conexión existente de config.php ($pdo)

    if (isset($_GET['id'])) {
        $deviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($deviceId === false || $deviceId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '❌ ID inválido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :deviceId AND user_id = :userId LIMIT 1");
        $stmt->bindParam(':deviceId', $deviceId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $device = $stmt->fetch();

        if ($device) {
            echo json_encode(['success' => true, 'device' => $device]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '📛 Dispositivo no encontrado']);
        }

    } else {
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = :userId ORDER BY created_at DESC");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $devices = $stmt->fetchAll();
        echo json_encode(['success' => true, 'devices' => $devices]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error en get_devices.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor. Intenta más tarde.']);
}
