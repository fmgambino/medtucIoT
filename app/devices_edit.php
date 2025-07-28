<?php
require __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

// Validar sesión y método
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '🔒 No autorizado.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Leer JSON del cuerpo del request
$data = json_decode(file_get_contents('php://input'), true);

$id        = intval($data['id'] ?? 0);
$ubicacion = trim($data['ubicacion'] ?? '');
$nombre    = trim($data['nombre'] ?? '');
$espid     = trim($data['espid'] ?? '');
$serial    = strtoupper(trim($data['serial'] ?? ''));
$icono     = trim($data['icono'] ?? '');
$domicilio = trim($data['domicilio'] ?? '');
$mapa      = trim($data['mapa'] ?? '');

// Validar campos obligatorios
if (!$id || $ubicacion === '' || $nombre === '' || $espid === '' || $serial === '' || $icono === '' || $domicilio === '' || $mapa === '') {
    echo json_encode(['success' => false, 'message' => '⚠️ Todos los campos son obligatorios.']);
    exit;
}

// Validar formato del número de serie
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    echo json_encode(['success' => false, 'message' => '❗ Formato de número de serie inválido.']);
    exit;
}

// Verificar unicidad del número de serie
$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial = ? AND id != ? AND user_id = ?");
$stmt->execute([$serial, $id, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => '❌ Este número de serie ya está registrado.']);
    exit;
}

// Ejecutar actualización
try {
    $update = $pdo->prepare("
        UPDATE devices
        SET ubicacion = ?, nombre = ?, espid = ?, serial = ?, icono = ?, domicilio = ?, mapa = ?
        WHERE id = ? AND user_id = ?
    ");
    $update->execute([$ubicacion, $nombre, $espid, $serial, $icono, $domicilio, $mapa, $id, $userId]);

    echo json_encode(['success' => true, 'message' => '✅ Dispositivo actualizado correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '❌ Error al actualizar el dispositivo.']);
}