<?php
/**
 * Registro (AJAX JSON)
 * Body: { name, email, pass, remember, csrf }
 * - Valido inputs (longitudes y formato)
 * - Creo user con Argon2id
 * - Creo fila en user_points (0/2500) si quieres el gamification desde ya
 * - Sesión + remember opcional
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$in       = json_decode(file_get_contents('php://input'), true) ?? [];
$name     = trim((string)($in['name'] ?? ''));
$emailRaw = (string)($in['email'] ?? '');
$pass     = (string)($in['pass'] ?? '');
$remember = !empty($in['remember']);
$csrf     = $in['csrf'] ?? null;

// CSRF
csrf_verify($csrf);

// Validaciones sencillas
$email = strtolower(trim($emailRaw));
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
  json_out(['ok'=>false,'error'=>'El nombre debe tener 2–100 caracteres'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_out(['ok'=>false,'error'=>'Email no válido'], 400);
}
if (mb_strlen($pass) < 8) {
  json_out(['ok'=>false,'error'=>'La contraseña debe tener al menos 8 caracteres'], 400);
}

// ¿Existe ya ese email?
$dup = db_exec("SELECT id FROM users WHERE email=?", 's', [$email]);
if ($dup) {
  json_out(['ok'=>false,'error'=>'Ese email ya está registrado'], 409);
}

// Inserto user
$now  = date('Y-m-d H:i:s');
$hash = hash_pass($pass);
$info = db_exec_nonquery(
  "INSERT INTO users (name,email,password_hash,preferred_category,created_at,updated_at)
   VALUES (?,?,?,?,?,?)",
  'ssssss',
  [$name, $email, $hash, 'todos', $now, $now]
);
if (!$info['ok']) {
  json_out(['ok'=>false,'error'=>'No se pudo crear el usuario'], 500);
}
$uid = (int)$info['id'];

// Puntos iniciales (si no tienes la tabla, comenta esto)
db_exec_nonquery(
  "INSERT INTO user_points (user_id,total_points,goal) VALUES (?,?,?)",
  'iii',
  [$uid, 0, 2500]
);

// Login + remember
auth_login($uid, $remember);

// Devuelvo OK
json_out(['ok'=>true,'uid'=>$uid,'name'=>$name,'email'=>$email]);
