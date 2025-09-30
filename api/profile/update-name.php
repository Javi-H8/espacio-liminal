<?php
/**
 * Actualiza el NOMBRE del usuario logueado
 * - Entrada JSON: { csrf, name }
 * - Validación básica y update
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id();
if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);

$in   = json_decode(file_get_contents('php://input'), true) ?? [];
$csrf = $in['csrf'] ?? null;
csrf_verify($csrf);

$name = trim((string)($in['name'] ?? ''));
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
  json_out(['ok'=>false,'error'=>'El nombre debe tener 2–100 caracteres'], 400);
}

$ok = db_exec_nonquery(
  "UPDATE users SET name=?, updated_at=? WHERE id=?",
  'ssi', [$name, date('Y-m-d H:i:s'), $uid]
)['ok'];

json_out(['ok'=>$ok], $ok?200:500);
