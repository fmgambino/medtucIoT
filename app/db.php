<?php
// /xampp/htdocs/medtucIoT/app/config.php

$host   = '127.0.0.1';   // o 'localhost'
$dbname = 'medtuc';    // nombre de la BD que importaste
$user   = 'root';        // usuario por defecto en XAMPP
$pass   = '';            // XAMPP viene con contraseña vacía para root

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En local puedes mostrar el mensaje para depurar
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
