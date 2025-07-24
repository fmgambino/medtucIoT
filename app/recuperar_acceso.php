<?php
// /medtuciot/app/recuperar_acceso.php
require __DIR__ . '/config.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Obtener el email desde JSON
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '📭 Correo electrónico inválido.']);
    exit;
}

try {
    // Buscar el usuario
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '❌ No se encontró una cuenta con ese correo.']);
        exit;
    }

    // Generar una nueva contraseña temporal
    $tempPassword = substr(bin2hex(random_bytes(5)), 0, 10);
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Actualizar la contraseña
    $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $update->execute([$hashedPassword, $user['id']]);

    // Enviar el correo de recuperación
    sendRecoveryEmail($email, $user['first_name'], $user['last_name'], $user['username'], $tempPassword);

    echo json_encode(['success' => true, 'message' => '📬 Se envió un correo con tus datos de acceso. Revisa tu bandeja de entrada o spam.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '⚠️ Error interno del servidor.']);
}

// === FUNCIONES ===
function sendRecoveryEmail($email, $first_name, $last_name, $username, $tempPassword) {
    $subject = "🔐 Recuperación de acceso – MedTuCIoT";
    $loginUrl = "https://medtuc.electronicagambino.com/login";
    $logoUrl  = "https://medtuc.electronicagambino.com/assets/img/logo-dark.png";

    $message = "
    <html>
    <head>
      <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; color: #333; }
        .email-container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .email-logo { max-width: 200px; margin-bottom: 20px; }
        h2 { color: #ff4b2b; }
        .login-button { margin-top: 25px; padding: 12px 25px; background: #0073e6; color: #fff; text-decoration: none; font-weight: bold; border-radius: 6px; display: inline-block; }
      </style>
    </head>
    <body>
      <div class='email-container'>
        <img class='email-logo' src='{$logoUrl}' alt='MedTuCIoT'>
        <h2>Recuperación de acceso</h2>
        <p>Hola <strong>{$first_name} {$last_name}</strong>,</p>
        <p>Recibimos una solicitud para recuperar tus credenciales de acceso.</p>
        <p><strong>Usuario:</strong> {$username}</p>
        <p><strong>Contraseña temporal:</strong> {$tempPassword}</p>
        <p style='color: #777; font-size: 14px;'>Te recomendamos cambiar esta contraseña inmediatamente después de iniciar sesión.</p>
        <a class='login-button' href='{$loginUrl}'>Iniciar sesión</a>
        <p style='margin-top: 40px; font-size: 0.9em; color: #666;'>Este mensaje fue generado automáticamente. Si no realizaste esta solicitud, puedes ignorarlo.</p>
        <p style='font-size: 12px;'>© 2025 Electrónica Gambino – MedTuCIoT</p>
      </div>
    </body>
    </html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: MedTuCIoT <no-reply@medtuc.electronicagambino.com>\r\n";

    @mail($email, $subject, $message, $headers);
}
