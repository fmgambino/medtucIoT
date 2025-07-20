<?php
require __DIR__ . '/config.php';

try {
    // consulta de prueba
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $row = $stmt->fetch();
    echo "Conexión OK, users en tabla: " . $row['total'];
} catch (Exception $e) {
    die("Fallo en la consulta: " . $e->getMessage());
}
