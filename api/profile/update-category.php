<?php
require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) { http_response_code(401); json_out(['ok'=>false,'error'=>'No autenticado']); }

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$cat  = $data['cat'] ?? '';
$csrf = $data['csrf'] ?? null;

if (function_exists('csrf_verify')) { csrf_verify($csrf); }
if (function_exists('rate_limiter')) { rate_limiter('profile_update', 30, 60); }

$allowed = ['naturaleza','ocio','aventura','cultural','todos','custom'];
if (!in_array($cat, $allowed, true)) {
  http_response_code(400);
  json_out(['ok'=>false,'error'=>'Categoría inválida']);
}

$userId = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("UPDATE users SET preferred_category=? WHERE id=?");
if (!$stmt) { http_response_code(500); json_out(['ok'=>false,'error'=>'DB prepare']); }
$stmt->bind_param('si', $cat, $userId);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
  $_SESSION['preferred_category'] = $cat;
  json_out(['ok'=>true]);
} else {
  http_response_code(500);
  json_out(['ok'=>false,'error'=>'No se pudo guardar']);
}
