<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok'=>false,'error'=>'Método no permitido'], 405);
$uid = auth_user_id(); if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$accent = trim((string)($payload['accent'] ?? ''));
$mode   = (string)($payload['mode'] ?? ''); // 'light'|'dark'

// Valido color (permito #rgb y #rrggbb)
if ($accent !== '' && !preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $accent)) {
  json_out(['ok'=>false,'error'=>'Color inválido (usa #RRGGBB)'], 400);
}
if ($mode !== '' && !in_array($mode, ['light','dark'], true)) {
  json_out(['ok'=>false,'error'=>'Modo inválido'], 400);
}

// Construyo SQL dinámico solo con lo que venga
$sets = []; $types = ''; $params = [];
if ($accent !== '') { $sets[] = 'theme_accent=?'; $types.='s'; $params[]=$accent; }
if ($mode !== '')   { $sets[] = 'theme_mode=?';   $types.='s'; $params[]=$mode; }
if (!$sets) json_out(['ok'=>false,'error'=>'Nada que actualizar'], 400);

$types .= 'i'; $params[] = $uid;
$sql = 'UPDATE users SET '.implode(',', $sets).' WHERE id=? LIMIT 1';
$info = db_exec_nonquery($sql, $types, $params);

json_out(['ok'=> (bool)$info['ok']]);
