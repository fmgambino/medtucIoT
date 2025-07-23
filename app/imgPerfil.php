<?php
// app/imgPerfil.php
session_start();
require_once __DIR__ . '/config.php';

// Normalizar BASE_PATH sin barra final
$baseUrl = rtrim(BASE_PATH, '/');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Acceso denegado";
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];

    // Validaciones básicas
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo "Formato no permitido.";
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        echo "El archivo excede el tamaño permitido.";
        exit;
    }

    $uploadDir = __DIR__ . '/../assets/files/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Obtener la imagen anterior desde la DB
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldImage = $stmt->fetchColumn();

    // Generar nombre único
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_{$userId}_" . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    // Subir imagen nueva
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo "Error al subir la imagen.";
        exit;
    }

    $relativePath = "assets/files/" . $filename;

    // Borrar imagen anterior si no es la default
    if ($oldImage && $oldImage !== 'assets/files/default.png') {
        $oldPath = __DIR__ . '/../' . $oldImage;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Actualizar en base de datos y en sesión
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->execute([$relativePath, $userId]);
    $_SESSION['profile_image'] = $relativePath;

    // Redirigir con parámetro anti-cache
    header("Location: {$baseUrl}/app/dashboard.php?v=" . time());
    exit;
}

http_response_code(400);
echo "Solicitud inválida.";
