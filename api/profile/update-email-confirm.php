<?php
/**
 * Paso 2: Confirmar cambio de EMAIL con CÓDIGO (o token si prefieres)
 * - Entrada JSON: { csrf, code }   // (o token)
 * - Si todo ok → actualizo users.email y consumo la verificación
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$code  = isset($in['code'])  ? trim((string)$in['code']) : null;
$token = isset($in['token']) ? trim((string)$in['token']) : null;

if (!$code && !$token) json_out(['ok'=>false,'error'=>'Falta código o token'], 400);

// Cargo verificación válida
if ($code) {
  $row = db_exec(
    "SELECT id, target_value, expires_at FROM user_verifications
     WHERE user_id=? AND purpose='email_change' AND code=? AND consumed_at IS NULL
     ORDER BY id DESC LIMIT 1",
    'is', [$uid, $code]
  )[0] ?? null;
} else {
  $row = db_exec(
    "SELECT id, target_value, expires_at FROM user_verifications
     WHERE user_id=? AND purpose='email_change' AND token=? AND consumed_at IS NULL
     ORDER BY id DESC LIMIT 1",
    'is', [$uid, $token]
  )[0] ?? null;
}

if (!$row) json_out(['ok'=>false,'error'=>'Código/token no válido'], 400);
if (strtotime($row['expires_at']) < time()) {
  json_out(['ok'=>false,'error'=>'El código ha caducado'], 400);
}

$newEmail = strtolower(trim($row['target_value']));

// Doble check: email no usado por otro (por si pasó tiempo)
$dup = db_exec("SELECT id FROM users WHERE email=?", 's', [$newEmail]);
if ($dup) json_out(['ok'=>false,'error'=>'Ese email ya está en uso'], 409);

// Actualizo email del usuario
$ok = db_exec_nonquery(
  "UPDATE users SET email=?, updated_at=? WHERE id=?",
  'ssi', [$newEmail, date('Y-m-d H:i:s'), $uid]
)['ok'];

if (!$ok) json_out(['ok'=>false,'error'=>'No se pudo actualizar el email'], 500);

// Marco verificación consumida
db_exec_nonquery("UPDATE user_verifications SET consumed_at=NOW() WHERE id=?", 'i', [(int)$row['id']]);

json_out(['ok'=>true, 'email'=>$newEmail]);
