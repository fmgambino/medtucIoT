<?php
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID inválido"]);
    exit;
}

$id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM dispositivos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Dispositivo no encontrado"]);
} else {
    $device = $result->fetch_assoc();
    echo json_encode(["success" => true, "data" => $device]);
}

$stmt->close();
$conn->close();
