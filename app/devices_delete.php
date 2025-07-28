<?php
require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

// Verifica sesión y método
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '🔒 No autorizado.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

// Validación
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '❌ ID inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '🗑 Dispositivo eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ No se encontró el dispositivo.']);
    }
} catch (PDOException $e) {
    error_log("Delete Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '❌ Error al eliminar el dispositivo.']);
}
