<?php
/**
 * Actualiza TELÉFONO
 * - Entrada JSON: { csrf, phone }
 * - No me flip* con validaciones de formato; lo dejamos suave
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$phone = trim((string)($in['phone'] ?? ''));
if (mb_strlen($phone) > 32) json_out(['ok'=>false,'error'=>'Teléfono demasiado largo'], 400);

$ok = db_exec_nonquery(
  "UPDATE users SET phone=?, updated_at=? WHERE id=?",
  'ssi', [$phone, date('Y-m-d H:i:s'), $uid]
)['ok'];

json_out(['ok'=>$ok], $ok?200:500);
