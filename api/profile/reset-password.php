<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$csrf  = $data['csrf'] ?? null;

if (function_exists('csrf_verify')) { csrf_verify($csrf); }
if (function_exists('rate_limiter')) { rate_limiter('password_reset', 5, 60); }

if (!$email) { http_response_code(400); json_out(['ok'=>false,'error'=>'Email inválido']); }

/* 1) (Opcional) Comprueba que existe el correo */
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
if (!$stmt) { http_response_code(500); json_out(['ok'=>false,'error'=>'DB prepare']); }
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($uid);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
  /* Para no filtrar usuarios, respondemos éxito igualmente */
  json_out(['ok'=>true, 'sent'=>true]);
}

/* 2) Generar OTP de 4 dígitos y guardar en sesión (10 min de vida) */
$code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
$_SESSION['otp_code']    = $code;
$_SESSION['otp_expires'] = time() + 600; // 10 minutos
$_SESSION['otp_user']    = (int)$uid;

/* 3) (Opcional) Envío de email real con el código */
// TODO: enviar $code a $email con tu librería de correo

json_out(['ok'=>true, 'sent'=>true]);
