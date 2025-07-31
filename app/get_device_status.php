<?php
require_once "config.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

// Mostrar errores solo en entorno local
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
$deviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($deviceId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '❌ ID de dispositivo no válido']);
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
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :deviceId AND user_id = :userId");
    $stmt->execute([':deviceId' => $deviceId, ':userId' => $userId]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '❌ Dispositivo no encontrado']);
        exit;
    }

    // Simular datos de estado (aquí deberías integrar datos reales si están en otra tabla)
    $status = [
        'esp32_id'   => $device['esp32_id'] ?? 'N/A',
        'mac'        => $device['mac'] ?? 'A4:CF:12:34:56:78',
        'rssi'       => -47,
        'mqtt'       => 'Online',
        'cpu_temp'   => 51.7,
        'uptime'     => '0:00:04:17',
        'wifi'       => 'MedTuCIoT_WiFi',
        'ip'         => '192.168.0.104',
        'place'      => $device['place_name'] ?? '—',
        'last_seen'  => $device['last_seen'] ?? date('Y-m-d H:i:s'),
    ];

    echo json_encode([
        'success' => true,
        'status' => $status
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (get_device_status): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error del servidor.']);
}
