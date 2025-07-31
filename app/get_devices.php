<?php
require_once "config.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores en desarrollo (opcional)
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Verifica sesión activa
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '⚠️ Usuario no autenticado']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    // Conexión PDO segura
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ¿Se pasó un ID de dispositivo específico?
    if (isset($_GET['id'])) {
        $deviceId = (int) $_GET['id'];

        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :deviceId AND user_id = :userId LIMIT 1");
        $stmt->bindParam(':deviceId', $deviceId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($device) {
            echo json_encode(['success' => true, 'device' => $device]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '📛 Dispositivo no encontrado']);
        }

    } else {
        // Obtener todos los dispositivos del usuario
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = :userId ORDER BY created_at DESC");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'devices' => $devices
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (get_devices): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor. Intenta más tarde.']);
}
