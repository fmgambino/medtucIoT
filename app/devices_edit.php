<?php
// /medtuciot/app/devices_edit.php

require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

/**
 * Enviar respuesta JSON y finalizar
 */
function jsonResponse(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// 🔐 Validar sesión y método
if (empty($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, '🔒 No autorizado.');
}

$userId = (int) $_SESSION['user_id'];

// 📥 Obtener datos (funciona con JSON y con form-urlencoded / FormData)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (is_array($data) && isset($data['id'])) {
    // Si viene en JSON
    $id        = (int) ($data['id'] ?? 0);
    $ubicacion = trim($data['ubicacion'] ?? '');
    $nombre    = trim($data['nombre'] ?? '');
    $espid     = trim($data['espid'] ?? '');
    $serial    = strtoupper(trim($data['serial'] ?? ''));
    $icono     = trim($data['icono'] ?? '');
    $domicilio = trim($data['domicilio'] ?? '');
    $mapa      = trim($data['mapa'] ?? '');
} else {
    // Si viene en FormData (más común con fetch + FormData)
    $id        = (int) ($_POST['id'] ?? 0);
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $espid     = trim($_POST['espid'] ?? '');
    $serial    = strtoupper(trim($_POST['serial'] ?? ''));
    $icono     = trim($_POST['icono'] ?? '');
    $domicilio = trim($_POST['domicilio'] ?? '');
    $mapa      = trim($_POST['mapa'] ?? '');
}

// ✅ Validar campos obligatorios
if (
    $id <= 0 ||
    $ubicacion === '' ||
    $nombre === '' ||
    $espid === '' ||
    $serial === '' ||
    $icono === '' ||
    $domicilio === '' ||
    $mapa === ''
) {
    jsonResponse(false, '⚠️ Todos los campos son obligatorios.');
}

// ✅ Validar formato de serial
if (!preg_match('/^EG[A-Z0-9]{6}$/', $serial)) {
    jsonResponse(false, '❗ El número de serie debe tener formato: EGXXXXXX.');
}

// 🚫 Verificar duplicados (excepto el propio dispositivo)
$stmt = $pdo->prepare("SELECT id FROM devices WHERE serial_number = ? AND id != ? AND user_id = ?");
$stmt->execute([$serial, $id, $userId]);
if ($stmt->fetch()) {
    jsonResponse(false, '❌ Otro dispositivo ya tiene este número de serie.');
}

// 📝 Ejecutar actualización
try {
    $update = $pdo->prepare("
        UPDATE devices
        SET serial_number = ?, name = ?, icono = ?, ubicacion = ?, domicilio = ?, mapa = ?, esp32_id = ?
        WHERE id = ? AND user_id = ?
    ");
    $update->execute([
        $serial,
        $nombre,
        $icono,
        $ubicacion,
        $domicilio,
        $mapa,
        $espid,
        $id,
        $userId
    ]);

    if ($update->rowCount() > 0) {
        jsonResponse(true, '✅ Dispositivo actualizado correctamente.');
    } else {
        jsonResponse(false, '⚠️ No se realizaron cambios (verifica permisos o datos).');
    }
} catch (PDOException $e) {
    error_log("❌ DB Error [devices_edit.php]: " . $e->getMessage());
    jsonResponse(false, '❌ Error al actualizar el dispositivo.');
}
