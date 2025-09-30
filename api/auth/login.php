<?php
/**
 * Login (AJAX JSON)
 * Body: { email, pass, remember, csrf }
 * - Valido CSRF (el del bootstrap)
 * - Verifico email+pass (Argon2id)
 * - Creo sesión + remember si procede
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

// 1) Entrada JSON
$in       = json_decode(file_get_contents('php://input'), true) ?? [];
$emailRaw = (string)($in['email'] ?? '');
$pass     = (string)($in['pass'] ?? '');
$remember = !empty($in['remember']);
$csrf     = $in['csrf'] ?? null;

// 2) CSRF y saneado básico
csrf_verify($csrf);
$email = strtolower(trim($emailRaw));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
  json_out(['ok'=>false,'error'=>'Email/contraseña no válidos'], 400);
}

// (Opcional) rate-limit muy simple por IP/email
$bucket = 'login_' . md5($_SERVER['REMOTE_ADDR'] . '|' . $email);
if (too_many_attempts($bucket, 10, 60)) {
  json_out(['ok'=>false,'error'=>'Demasiados intentos, espera un minuto'], 429);
}

// 3) Busco user y valido hash
$row = db_exec("SELECT id, name, email, password_hash FROM users WHERE email=?", 's', [$email])[0] ?? null;
if (!$row || !password_verify($pass, $row['password_hash'])) {
  json_out(['ok'=>false,'error'=>'Credenciales incorrectas'], 401);
}

// 4) Sesión + remember
auth_login((int)$row['id'], $remember);

// 5) Respuesta
json_out(['ok'=>true,'uid'=>(int)$row['id'],'name'=>$row['name'],'email'=>$row['email']]);
