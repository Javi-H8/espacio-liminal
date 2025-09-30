<?php
/**
 * Lee en crudo lo que hay en la BBDD para mi usuario (solo lectura, formato JSON)
 * - Yo esto lo uso para verificar que el update ha ido a tabla y no se ha quedado en el limbo
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id();
if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);

// Me traigo exactamente los campos que estamos editando
$row = db_exec(
  "SELECT id, name, email,
          COALESCE(phone,'')             AS phone,
          COALESCE(gender,'')            AS gender,
          COALESCE(locale,'es')          AS locale,
          COALESCE(preferred_category,'todos') AS preferred_category,
          COALESCE(avatar_url,'')        AS avatar_url,
          created_at, updated_at
   FROM users WHERE id=? LIMIT 1",
  'i', [$uid]
)[0] ?? null;

if (!$row) json_out(['ok'=>false,'error'=>'No encontrado'], 404);

// Esto te muestra EXACTAMENTE lo que hay en tabla ahora mismo
json_out(['ok'=>true, 'user'=>$row]);
