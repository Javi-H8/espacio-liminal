<?php
/**
 * Cambiar CONTRASEÑA con la actual (flujo dentro de perfil)
 * - Entrada JSON: { csrf, current, new, new2 }
 * - Verifico la actual, seteo hash Argon2id, borro remember-tokens, regen id
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$cur = (string)($in['current'] ?? '');
$new = (string)($in['new'] ?? '');
$rep = (string)($in['new2'] ?? '');

if ($new !== $rep) json_out(['ok'=>false,'error'=>'Las nuevas no coinciden'], 400);
if (mb_strlen($new) < 8) json_out(['ok'=>false,'error'=>'Mínimo 8 caracteres'], 400);

$row = db_exec("SELECT password_hash FROM users WHERE id=?", 'i', [$uid])[0] ?? null;
if (!$row || !password_verify($cur, $row['password_hash'])) {
  json_out(['ok'=>false,'error'=>'La contraseña actual no es correcta'], 401);
}

$hash = hash_pass($new);
$ok = db_exec_nonquery(
  "UPDATE users SET password_hash=?, updated_at=? WHERE id=?",
  'ssi', [$hash, date('Y-m-d H:i:s'), $uid]
)['ok'];

if (!$ok) json_out(['ok'=>false,'error'=>'No se pudo actualizar'], 500);

// Seguridad extra: revoco remembers y roto sesión
db_exec_nonquery("DELETE FROM auth_tokens WHERE user_id=?", 'i', [$uid]);
if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);

json_out(['ok'=>true]);
