<?php
// app/get_device.php

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar parámetro ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Falta el parámetro ID']);
    exit;
}

$espid = trim($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE espid = :espid LIMIT 1");
    $stmt->execute(['espid' => $espid]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $device]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
