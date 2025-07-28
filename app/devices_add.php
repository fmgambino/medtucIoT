<?php
// /medtuciot/app/devices_add.php

require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

// Verifica si es una petición AJAX
function isAjaxRequest(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

// Verifica que haya sesión válida
if (!isset($_SESSION['user_id'])) {
    $msg = '🔒 Sesión no válida.';
    if (isAjaxRequest()) {
        echo json_encode(['success' => false, 'message' => $msg]);
    } else {
        header('Location: login.php');
    }
    exit;
}

$userId     = (int)$_SESSION['user_id'];
$ubicacion  = trim($_POST['ubicacion'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$espid      = trim($_POST['espid'] ?? '');
$serial     = strtoupper(trim($_POST['serial'] ?? ''));
$icono      = trim($_POST['icono'] ?? '');
$domicilio  = trim($_POST['domicilio'] ?? '');
$mapa       = trim($_POST['mapa'] ?? '');
$placeId    = 1; // Por defecto, o ajustable en el futuro

// Verifica que todos los campos estén presentes
$camposFaltantes = array_filter([
    'ubicacion' => $ubicacion,
    'nombre'    => $nombre,
    'espid'     => $espid,
    'serial'    => $serial,
    'icono'     => $icono,
    'domicilio' => $domicilio,
    'mapa'      => $mapa
], fn($v) => $v === '');

if (!empty($camposFaltantes)) {
    $msg = '⚠️ Todos los campos son obligatorios.';
    isAjaxRequest()
        ? exit(json_encode(['success' => false, 'message' => $msg]))
        : exit("<script>alert('$msg'); window.location.href='devices.php';</script>");
}

// Validación del serial: EG + 6 alfanuméricos
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    $msg = '❗ El número de serie debe tener formato: EGXXXXXX (6 caracteres alfanuméricos).';
    isAjaxRequest()
        ? exit(json_encode(['success' => false, 'message' => $msg]))
        : exit("<script>alert('$msg'); window.location.href='devices.php';</script>");
}

// Verifica duplicados
$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial_number = ?");
$stmt->execute([$serial]);
if ($stmt->fetch()) {
    $msg = '❌ Este número de serie ya está registrado.';
    isAjaxRequest()
        ? exit(json_encode(['success' => false, 'message' => $msg]))
        : exit("<script>alert('$msg'); window.location.href='devices.php';</script>");
}

// Inserta el nuevo dispositivo
try {
    $insert = $pdo->prepare("
        INSERT INTO devices (
            serial_number,
            place_id,
            name,
            icono,
            ubicacion,
            domicilio,
            mapa,
            esp32_id,
            user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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

    $msg = '✅ Dispositivo registrado con éxito.';
    isAjaxRequest()
        ? exit(json_encode(['success' => true, 'message' => $msg]))
        : exit("<script>alert('$msg'); window.location.href='devices.php';</script>");

} catch (PDOException $e) {
    $msg = '❌ Error al registrar el dispositivo. Inténtalo nuevamente.';
    error_log('DB Error: ' . $e->getMessage());

    isAjaxRequest()
        ? exit(json_encode(['success' => false, 'message' => $msg]))
        : exit("<script>alert('$msg'); window.location.href='devices.php';</script>");
}
