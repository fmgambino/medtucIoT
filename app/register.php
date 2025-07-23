<?php
// /app/register.php
session_start();
require __DIR__ . '/config.php';

$baseUrl = rtrim(BASE_PATH, '/');

if (isset($_SESSION['user_id'])) {
    header("Location: {$baseUrl}/dashboard");
    exit;
}

$error   = $_GET['error']   ?? '';
$success = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $country    = trim($_POST['country']    ?? '');
    $province   = trim($_POST['province']   ?? '');
    $city       = trim($_POST['city']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = trim($_POST['password']   ?? '');

    if ($first_name === '' || $last_name === '' || $country === '' || $email === '' || $password === '') {
        header("Location: {$baseUrl}/register?error=campos");
        exit;
    }

    try {
        // Verificar si el correo ya está en uso
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: {$baseUrl}/register?error=exists");
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insertar nuevo usuario (usamos el email como username también)
        $insert = $pdo->prepare("
            INSERT INTO users
                (first_name, last_name, country, province, city, email, username, password_hash, subscription_type)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, 'Prospecto')
        ");
        $insert->execute([
            $first_name,
            $last_name,
            $country,
            $province,
            $city,
            $email,
            $email, // username = email
            $hashed
        ]);

        // Enviar correo de bienvenida (si el servidor permite)
        $subject = "Bienvenido a MedTuCIoT";
        $message = "Hola {$first_name},\n\nGracias por registrarte en MedTuCIoT. Ya puedes iniciar sesión y comenzar a usar la plataforma.";
        $headers = "From: no-reply@medtuc.electronicagambino.com";

        @mail($email, $subject, $message, $headers); // usa @ para evitar errores si mail no está disponible

        header("Location: {$baseUrl}/login?success=1");
        exit;

    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
}
?>
