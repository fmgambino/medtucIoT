<?php
require __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '🔒 No autorizado.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => '❌ ID no válido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);

    echo json_encode(['success' => true, 'message' => '🗑 Dispositivo eliminado.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '❌ Error al eliminar.']);
}
