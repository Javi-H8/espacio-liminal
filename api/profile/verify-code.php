<?php
require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$code = (string)($data['code'] ?? '');

if (function_exists('rate_limiter')) { rate_limiter('otp_verify', 10, 60); }

$stored = $_SESSION['otp_code']    ?? null;
$exp    = $_SESSION['otp_expires'] ?? 0;

if (!$stored || time() > (int)$exp) {
  http_response_code(400);
  json_out(['ok'=>false,'error'=>'Código expirado o no solicitado']);
}

if (hash_equals($stored, $code)) {
  // OTP correcto: puedes marcar verificación o permitir el flujo que quieras
  unset($_SESSION['otp_code'], $_SESSION['otp_expires']);
  json_out(['ok'=>true]);
}

http_response_code(400);
json_out(['ok'=>false,'error'=>'Código incorrecto']);
