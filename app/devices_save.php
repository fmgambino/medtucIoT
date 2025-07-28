<?php
require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

// Validar sesión y método
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '🔒 No autorizado.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Campos comunes
$id        = isset($data['id']) ? (int)$data['id'] : 0;
$ubicacion = trim($data['ubicacion'] ?? '');
$nombre    = trim($data['nombre'] ?? '');
$espid     = trim($data['espid'] ?? '');
$serial    = strtoupper(trim($data['serial'] ?? ''));
$icono     = trim($data['icono'] ?? '');
$domicilio = trim($data['domicilio'] ?? '');
$mapa      = trim($data['mapa'] ?? '');
$placeId   = 1; // futuro parámetro opcional

// Validar campos obligatorios
if (
    $ubicacion === '' || $nombre === '' || $espid === '' || $serial === '' ||
    $icono === '' || $domicilio === '' || $mapa === ''
) {
    echo json_encode(['success' => false, 'message' => '⚠️ Todos los campos son obligatorios.']);
    exit;
}

// Validar serial
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    echo json_encode(['success' => false, 'message' => '❗ Formato de número de serie inválido. Debe ser EGXXXXXX.']);
    exit;
}

// Validación duplicado
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id FROM devices WHERE serial_number = ? AND id != ? AND user_id = ?");
    $stmt->execute([$serial, $id, $userId]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM devices WHERE serial_number = ? AND user_id = ?");
    $stmt->execute([$serial, $userId]);
}
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => '❌ Este número de serie ya está en uso.']);
    exit;
}

try {
    if ($id > 0) {
        // Modo edición
        $update = $pdo->prepare("
            UPDATE devices
            SET serial_number = ?, place_id = ?, name = ?, icono = ?, ubicacion = ?, domicilio = ?, mapa = ?, esp32_id = ?
            WHERE id = ? AND user_id = ?
        ");
        $update->execute([
            $serial,
            $placeId,
            $nombre,
            $icono,
            $ubicacion,
            $domicilio,
            $mapa,
            $espid,
            $id,
            $userId
        ]);
        echo json_encode(['success' => true, 'message' => '✅ Dispositivo actualizado.']);
    } else {
        // Modo alta
        $insert = $pdo->prepare("
            INSERT INTO devices (serial_number, place_id, name, icono, ubicacion, domicilio, mapa, esp32_id, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $serial,
            $placeId,
            $nombre,
            $icono,
            $ubicacion,
            $domicilio,
            $mapa,
            $espid,
            $userId
        ]);
        echo json_encode(['success' => true, 'message' => '✅ Dispositivo registrado.']);
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error al guardar en la base de datos.']);
}
