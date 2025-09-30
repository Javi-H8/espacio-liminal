<?php
/**
 * Actualiza CATEGORÍA favorita
 * - Entrada JSON: { csrf, preferred_category }
 * - Lista cerrada de ejemplo (ajusta a tu BBDD si tienes tabla categorías)
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$allowed = ['todos','nature','city','mountains','beach','culture'];
$pref    = (string)($in['preferred_category'] ?? 'todos');
if (!in_array($pref, $allowed, true)) $pref = 'todos';

$ok = db_exec_nonquery(
  "UPDATE users SET preferred_category=?, updated_at=? WHERE id=?",
  'ssi', [$pref, date('Y-m-d H:i:s'), $uid]
)['ok'];

json_out(['ok'=>$ok], $ok?200:500);
