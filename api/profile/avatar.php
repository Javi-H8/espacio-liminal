<?php
/**
 * api/profile/avatar.php
 * Sube avatar (JPG/PNG/WebP, máx 2MB) y guarda en users.avatar_url
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id();
if (!$uid) json_out(['ok'=>false,'error'=>'No autorizado'], 401);

csrf_verify($_POST['csrf'] ?? null);

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  json_out(['ok'=>false,'error'=>'Archivo no recibido'], 400);
}
$f = $_FILES['avatar'];
if ($f['size'] > 2*1024*1024) json_out(['ok'=>false,'error'=>'Pesa más de 2 MB'], 400);

$allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
$mime = mime_content_type($f['tmp_name']);
if (!isset($allowed[$mime])) json_out(['ok'=>false,'error'=>'Formato no permitido'], 400);
$ext = $allowed[$mime];

$base = __DIR__ . '/../../assets/uploads/avatars';
$sub  = date('Y/m');
$dir  = $base . '/' . $sub;
if (!is_dir($dir) && !mkdir($dir, 0775, true)) json_out(['ok'=>false,'error'=>'No puedo crear carpeta'], 500);

$name = 'u'.$uid.'_'.bin2hex(random_bytes(6)).'_'.time().'.'.$ext;
$dest = $dir . '/' . $name;
if (!move_uploaded_file($f['tmp_name'], $dest)) json_out(['ok'=>false,'error'=>'No pude guardar'], 500);

// URL pública (ajusta si tu proyecto no cuelga de /espacio-liminal)
$url = '/espacio-liminal/assets/uploads/avatars/'.$sub.'/'.$name;

$ok = db_exec_nonquery("UPDATE users SET avatar_url=?, updated_at=? WHERE id=?",
  'ssi', [$url, date('Y-m-d H:i:s'), $uid])['ok'];

if (!$ok) { @unlink($dest); json_out(['ok'=>false,'error'=>'No pude actualizar el perfil'], 500); }

json_out(['ok'=>true,'url'=>$url]);
