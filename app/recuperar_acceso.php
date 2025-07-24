<?php
require __DIR__ . '/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre, apellido, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No se encontró una cuenta con ese correo.']);
        exit;
    }

    // Generar una nueva contraseña temporal
    $tempPassword = substr(bin2hex(random_bytes(4)), 0, 8);
    $hashed = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Actualizar la contraseña
    $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $update->execute([$hashed, $user['id']]);

    // Componer el correo
    $to = $email;
    $subject = "🔐 Recuperación de acceso - MedTuCIoT";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: MedTuCIoT <no-reply@electronicagambino.com>\r\n";

    $message = "
    <html>
      <body style=\"font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;\">
        <div style=\"max-width: 600px; margin: auto; background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);\">
          <img src='https://medtuc.electronicagambino.com/assets/img/logo-dark.png' alt='MedTuCIoT' style='max-width: 180px; margin-bottom: 20px;' />
          <h2 style='color: #ff4b2b;'>Recuperación de Acceso</h2>
          <p>Hola <strong>{$user['nombre']} {$user['apellido']}</strong>,</p>
          <p>Recibimos una solicitud para restablecer tus credenciales de acceso en <strong>MedTuCIoT</strong>.</p>

          <p><strong>Usuario:</strong> {$user['username']}<br>
             <strong>Contraseña temporal:</strong> {$tempPassword}</p>

          <p style='color: #777; font-size: 14px;'>Por seguridad, te recomendamos cambiar esta contraseña inmediatamente después de ingresar.</p>

          <a href='https://medtuc.electronicagambino.com/login' style='display: inline-block; background: #ff4b2b; color: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: bold;'>Iniciar sesión</a>

          <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;' />
          <p style='font-size: 12px; color: #999;'>Este mensaje fue enviado automáticamente. Si no solicitaste esta recuperación, puedes ignorarlo.</p>
        </div>
      </body>
    </html>
    ";

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Se envió un correo con tus datos de acceso. Revisa tu bandeja de entrada o spam.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo. Intenta nuevamente.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
