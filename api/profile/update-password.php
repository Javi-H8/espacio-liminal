<?php
require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) { http_response_code(401); json_out(['ok'=>false,'error'=>'No autenticado']); }

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$old  = (string)($data['old'] ?? '');
$n1   = (string)($data['n1']  ?? '');        // nueva contraseña
$csrf = $data['csrf'] ?? null;

if (function_exists('csrf_verify')) { csrf_verify($csrf); }
if (function_exists('rate_limiter')) { rate_limiter('password_change', 10, 60); }

if (strlen($n1) < 8) { http_response_code(400); json_out(['ok'=>false,'error'=>'La contraseña debe tener al menos 8 caracteres']); }

$userId = (int)$_SESSION['user_id'];

/* 1) Traer hash actual del usuario */
$stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id=? LIMIT 1");
if (!$stmt) { http_response_code(500); json_out(['ok'=>false,'error'=>'DB prepare 1']); }
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($hashActual);
$found = $stmt->fetch();
$stmt->close();

if (!$found || !$hashActual) {
  http_response_code(404);
  json_out(['ok'=>false,'error'=>'Usuario no encontrado']);
}

/* 2) Verificar contraseña actual */
if (!check_pass($old, $hashActual)) {
  http_response_code(400);
  json_out(['ok'=>false,'error'=>'Contraseña actual incorrecta']);
}

/* 3) Generar nuevo hash y guardar */
$nuevoHash = hash_pass($n1);
$stmt = $mysqli->prepare("UPDATE users SET password_hash=? WHERE id=?");
if (!$stmt) { http_response_code(500); json_out(['ok'=>false,'error'=>'DB prepare 2']); }
$stmt->bind_param('si', $nuevoHash, $userId);
$ok = $stmt->execute();
$stmt->close();

if ($ok) json_out(['ok'=>true]);
http_response_code(500);
json_out(['ok'=>false,'error'=>'No se pudo actualizar']);
