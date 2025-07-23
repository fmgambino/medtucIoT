<?php
// app/imgPerfil.php
header("Content-Type: text/html; charset=UTF-8");
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

// Solo aceptar método POST y archivo presente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];

    // Validaciones
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(415);
        echo "Formato no permitido.";
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        http_response_code(413);
        echo "El archivo excede el tamaño permitido.";
        exit;
    }

    // Asegurar que el directorio existe
    $uploadDir = realpath(__DIR__ . '/../assets/files/');
    if (!$uploadDir || !is_writable($uploadDir)) {
        echo "Directorio de subida no disponible.";
        exit;
    }

    // Obtener imagen anterior
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldImage = $stmt->fetchColumn();

    // Nombre nuevo archivo
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_{$userId}_" . time() . '.' . $ext;
    $filepath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    // Guardar archivo
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo "Error al guardar el archivo.";
        exit;
    }

    $relativePath = "assets/files/" . $filename;

    // Borrar imagen anterior si no es la default
    if ($oldImage && $oldImage !== 'assets/files/default.png') {
        $oldPath = realpath(__DIR__ . '/../' . $oldImage);
        if ($oldPath && file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Guardar nueva ruta en la base y actualizar sesión
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->execute([$relativePath, $userId]);
    $_SESSION['profile_image'] = $relativePath;

    // Redirigir con versión anti-caché
    header("Location: {$baseUrl}/dashboard?v=" . time());
    exit;
}

// Si llega sin POST válido
http_response_code(400);
echo "Solicitud inválida.";
