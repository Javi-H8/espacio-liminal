<?php
/**
 * Paso 1: Solicitar cambio de EMAIL → generamos código y guardamos petición
 * - Entrada JSON: { csrf, new_email }
 * - Respuesta: ok y (si APP_ENV=dev) te devuelvo el código para probar.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$newEmail = strtolower(trim((string)($in['new_email'] ?? '')));
if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
  json_out(['ok'=>false,'error'=>'Email no válido'], 400);
}

// ¿Ese email ya existe?
$dup = db_exec("SELECT id FROM users WHERE email=?", 's', [$newEmail]);
if ($dup) json_out(['ok'=>false,'error'=>'Ese email ya está en uso'], 409);

// Limpio peticiones previas del mismo user & propósito
db_exec_nonquery("DELETE FROM user_verifications WHERE user_id=? AND purpose='email_change'", 'i', [$uid]);

$code   = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6 dígitos
$token  = bin2hex(random_bytes(16)); // por si quieres también validar por link
$expire = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

$ok = db_exec_nonquery(
  "INSERT INTO user_verifications (user_id, purpose, target_value, code, token, expires_at, created_at)
   VALUES (?,?,?,?,?, ?, NOW())",
  'isssss', [$uid, 'email_change', $newEmail, $code, $token, $expire]
)['ok'];

if (!$ok) json_out(['ok'=>false,'error'=>'No pude generar el código'], 500);

// TODO: aquí envías email/SMS al usuario con $code o un link con $token
// Como estamos en dev, si quieres te lo devuelvo para depurar sin emails:
$out = ['ok'=>true];
if (is_dev()) $out['dev_code'] = $code;
json_out($out);
