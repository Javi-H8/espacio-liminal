<?php
/**
 * Paso 2 reset password: verificar CÓDIGO y fijar nueva contraseña
 * - Entrada JSON: { csrf, email, code, new, new2 }
 * - Si ok → seteo hash nuevo y consumo verificación
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$email = strtolower(trim((string)($in['email'] ?? '')));
$code  = trim((string)($in['code'] ?? ''));
$pass1 = (string)($in['new'] ?? '');
$pass2 = (string)($in['new2'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(['ok'=>false,'error'=>'Email no válido'], 400);
if ($pass1 !== $pass2) json_out(['ok'=>false,'error'=>'Las nuevas no coinciden'], 400);
if (mb_strlen($pass1) < 8) json_out(['ok'=>false,'error'=>'Mínimo 8 caracteres'], 400);

$user = db_exec("SELECT id FROM users WHERE email=?", 's', [$email])[0] ?? null;
if (!$user) json_out(['ok'=>false,'error'=>'Código no válido'], 400);
$uid = (int)$user['id'];

$row = db_exec(
  "SELECT id, expires_at FROM user_verifications
   WHERE user_id=? AND purpose='password_reset' AND code=? AND consumed_at IS NULL
   ORDER BY id DESC LIMIT 1",
  'is', [$uid, $code]
)[0] ?? null;

if (!$row) json_out(['ok'=>false,'error'=>'Código no válido'], 400);
if (strtotime($row['expires_at']) < time()) json_out(['ok'=>false,'error'=>'Código caducado'], 400);

$hash = hash_pass($pass1);
$ok = db_exec_nonquery(
  "UPDATE users SET password_hash=?, updated_at=? WHERE id=?",
  'ssi', [$hash, date('Y-m-d H:i:s'), $uid]
)['ok'];

if (!$ok) json_out(['ok'=>false,'error'=>'No se pudo actualizar'], 500);

// Consumo verificación y revoco remember tokens
db_exec_nonquery("UPDATE user_verifications SET consumed_at=NOW() WHERE id=?", 'i', [(int)$row['id']]);
db_exec_nonquery("DELETE FROM auth_tokens WHERE user_id=?", 'i', [$uid]);

json_out(['ok'=>true]);
