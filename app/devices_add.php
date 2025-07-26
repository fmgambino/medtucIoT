<?php
// /medtuciot/app/devices_add.php
require __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

if (!isset($_SESSION['user_id'])) {
    if (isAjaxRequest()) {
        echo json_encode(['success' => false, 'message' => '🔒 Sesión no válida.']);
    } else {
        header('Location: login');
    }
    exit;
}

$userId    = $_SESSION['user_id'];
$ubicacion = trim($_POST['ubicacion'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$espid     = trim($_POST['espid'] ?? '');
$serial    = strtoupper(trim($_POST['serial'] ?? ''));
$icono     = trim($_POST['icono'] ?? '');
$domicilio = trim($_POST['domicilio'] ?? '');
$mapa      = trim($_POST['mapa'] ?? '');

// Validación de campos
if (
    $ubicacion === '' || $nombre === '' || $espid === '' || $serial === '' ||
    $icono === '' || $domicilio === '' || $mapa === ''
) {
    $msg = '⚠️ Todos los campos son obligatorios.';
    isAjaxRequest()
        ? print json_encode(['success' => false, 'message' => $msg])
        : exit("<script>alert('$msg'); window.location.href='devices';</script>");
    exit;
}

// Validar formato del número de serie (ejemplo: EGXXXXXX)
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    $msg = '❗ Formato de número de serie inválido. Debe ser como EGXXXXXX.';
    isAjaxRequest()
        ? print json_encode(['success' => false, 'message' => $msg])
        : exit("<script>alert('$msg'); window.location.href='devices';</script>");
    exit;
}

// Verificar si el número de serie ya fue registrado
$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial = ?");
$stmt->execute([$serial]);
if ($stmt->fetch()) {
    $msg = '❌ Este número de serie ya está registrado por otro usuario.';
    isAjaxRequest()
        ? print json_encode(['success' => false, 'message' => $msg])
        : exit("<script>alert('$msg'); window.location.href='devices';</script>");
    exit;
}

// Insertar el nuevo dispositivo
try {
    $insert = $pdo->prepare("
        INSERT INTO devices (user_id, ubicacion, nombre, espid, serial, icono, domicilio, mapa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([$userId, $ubicacion, $nombre, $espid, $serial, $icono, $domicilio, $mapa]);

    $msg = '✅ Dispositivo registrado con éxito.';
    isAjaxRequest()
        ? print json_encode(['success' => true, 'message' => $msg])
        : exit("<script>alert('$msg'); window.location.href='devices';</script>");

} catch (PDOException $e) {
    $msg = '❌ Error al registrar el dispositivo. Inténtalo nuevamente.';
    isAjaxRequest()
        ? print json_encode(['success' => false, 'message' => $msg])
        : exit("<script>alert('$msg'); window.location.href='devices';</script>");
}
