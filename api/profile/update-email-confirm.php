<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$code = (string)($data['code'] ?? '');

$session = $_SESSION['email_change'] ?? null;
if (!$session) { http_response_code(400); json_out(['ok'=>false,'error'=>'No hay solicitud activa']); }
if ($session['exp'] < time()) { unset($_SESSION['email_change']); http_response_code(400); json_out(['ok'=>false,'error'=>'Código caducado']); }
if ($session['code'] !== $code) { http_response_code(400); json_out(['ok'=>false,'error'=>'Código incorrecto']); }

$email  = $session['email'];
$userId = (int)$session['user_id'];

$stmt = $mysqli->prepare("UPDATE users SET email=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param('si', $email, $userId);
$ok = $stmt->execute(); $stmt->close();

unset($_SESSION['email_change']);
json_out(['ok'=>$ok ? true:false, 'email'=>$email]);
