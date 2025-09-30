<?php
/**
 * Actualiza IDIOMA
 * - Entrada JSON: { csrf, locale }
 * - Permitimos: es, en, fr, it
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);
$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$allowed = ['es','en','fr','it'];
$locale  = (string)($in['locale'] ?? 'es');
if (!in_array($locale, $allowed, true)) $locale = 'es';

$ok = db_exec_nonquery(
  "UPDATE users SET locale=?, updated_at=? WHERE id=?",
  'ssi', [$locale, date('Y-m-d H:i:s'), $uid]
)['ok'];

json_out(['ok'=>$ok], $ok?200:500);
