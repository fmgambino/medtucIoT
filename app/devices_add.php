<?php
// /medtuciot/app/devices_add.php

require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

/**
 * Enviar respuesta JSON y finalizar script
 */
function jsonResponse(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// 🔐 Validación de sesión
if (empty($_SESSION['user_id'])) {
    jsonResponse(false, '🔒 Sesión no válida. Vuelve a iniciar sesión.');
}

$userId     = (int) $_SESSION['user_id'];
$ubicacion  = trim($_POST['ubicacion'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$espid      = trim($_POST['espid'] ?? '');
$serial     = strtoupper(trim($_POST['serial'] ?? ''));
$icono      = trim($_POST['icono'] ?? '');
$domicilio  = trim($_POST['domicilio'] ?? '');
$mapa       = trim($_POST['mapa'] ?? '');
$placeId    = 1; // Por ahora fijo, escalable en el futuro

// ✅ Validar campos obligatorios
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
    jsonResponse(false, '⚠️ Todos los campos son obligatorios.');
}

// ✅ Validar formato de serial (EGXXXXXX)
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    jsonResponse(false, '❗ El número de serie debe tener formato: EGXXXXXX (6 caracteres alfanuméricos).');
}

// 🚫 Verificar duplicados
$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial_number = ? AND user_id = ?");
$stmt->execute([$serial, $userId]);
if ($stmt->fetch()) {
    jsonResponse(false, '❌ Este número de serie ya está registrado.');
}

// 📝 Insertar dispositivo
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

    jsonResponse(true, '✅ Dispositivo registrado con éxito.');

} catch (PDOException $e) {
    error_log("❌ DB Error [devices_add.php]: " . $e->getMessage());
    jsonResponse(false, '❌ Error al registrar el dispositivo. Inténtalo nuevamente.');
}
