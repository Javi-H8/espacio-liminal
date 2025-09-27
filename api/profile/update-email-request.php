<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($data['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400); json_out(['ok'=>false,'error'=>'Email no válido']);
}

$userId = 1;

/* genera código 4 dígitos y guarda en sesión
   (si prefieres DB, usa tu tabla password_otps como te indiqué antes) */
$code = str_pad((string)random_int(0,9999), 4, '0', STR_PAD_LEFT);
$_SESSION['email_change'] = [
  'user_id' => $userId,
  'email'   => $email,
  'code'    => $code,
  'exp'     => time()+600
];

/* aquí enviarías el email real; por ahora devolvemos el code en debug=false */
json_out(['ok'=>true /*,'debug_code'=>$code*/ ]);
