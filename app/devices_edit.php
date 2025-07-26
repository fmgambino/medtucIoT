<?php
require __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '🔒 Sesión no válida.']);
    exit;
}

$userId = $_SESSION['user_id'];
$id     = intval($_POST['id'] ?? 0);
$ubicacion = trim($_POST['ubicacion'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$espid     = trim($_POST['espid'] ?? '');
$serial    = strtoupper(trim($_POST['serial'] ?? ''));
$icono     = trim($_POST['icono'] ?? '');
$domicilio = trim($_POST['domicilio'] ?? '');
$mapa      = trim($_POST['mapa'] ?? '');

if (!$id || $ubicacion === '' || $nombre === '' || $espid === '' || $serial === '' || $icono === '' || $domicilio === '' || $mapa === '') {
    echo json_encode(['success' => false, 'message' => '⚠️ Todos los campos son obligatorios.']);
    exit;
}

if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    echo json_encode(['success' => false, 'message' => '❗ Formato de número de serie inválido.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial = ? AND id != ? AND user_id = ?");
$stmt->execute([$serial, $id, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => '❌ Este número de serie ya existe.']);
    exit;
}

try {
    $update = $pdo->prepare("
        UPDATE devices
        SET ubicacion = ?, nombre = ?, espid = ?, serial = ?, icono = ?, domicilio = ?, mapa = ?
        WHERE id = ? AND user_id = ?
    ");
    $update->execute([$ubicacion, $nombre, $espid, $serial, $icono, $domicilio, $mapa, $id, $userId]);

    echo json_encode(['success' => true, 'message' => '✅ Dispositivo actualizado.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '❌ Error al actualizar.']);
}
