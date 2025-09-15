<?php
// /medtuciot/app/devices_delete.php

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

// 📥 Obtener datos (funciona con JSON y con form-urlencoded)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (is_array($data) && isset($data['id'])) {
    $id = (int) $data['id'];
} else {
    $id = (int) ($_POST['id'] ?? 0);
}

// ✅ Validar ID
if ($id <= 0) {
    jsonResponse(false, '❌ ID inválido.');
}

// 🗑 Intentar eliminar
try {
    $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() > 0) {
        jsonResponse(true, '🗑 Dispositivo eliminado correctamente.');
    } else {
        jsonResponse(false, '❌ No se encontró el dispositivo o no tienes permisos.');
    }
} catch (PDOException $e) {
    error_log("❌ DB Error [devices_delete.php]: " . $e->getMessage());
    jsonResponse(false, '❌ Error al eliminar el dispositivo.');
}
