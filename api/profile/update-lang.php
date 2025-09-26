<?php
require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) { http_response_code(401); json_out(['ok'=>false,'error'=>'No autenticado']); }

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$lang = $data['lang'] ?? '';
$csrf = $data['csrf'] ?? null;

/* Si tienes verificación CSRF, úsala */
if (function_exists('csrf_verify')) { csrf_verify($csrf); }
/* Si tienes limitador, úsalo (por ejemplo, 30 peticiones/minuto) */
if (function_exists('rate_limiter')) { rate_limiter('profile_update', 30, 60); }

$allowed = ['es','en','fr','it'];
if (!in_array($lang, $allowed, true)) {
  http_response_code(400);
  json_out(['ok'=>false,'error'=>'Idioma no soportado']);
}

$userId = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("UPDATE users SET locale=? WHERE id=?");
if (!$stmt) { http_response_code(500); json_out(['ok'=>false,'error'=>'DB prepare']); }
$stmt->bind_param('si', $lang, $userId);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
  $_SESSION['locale'] = $lang; // refresco local rápido
  json_out(['ok'=>true]);
} else {
  http_response_code(500);
  json_out(['ok'=>false,'error'=>'No se pudo guardar']);
}
