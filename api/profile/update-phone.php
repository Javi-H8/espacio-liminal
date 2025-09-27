<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$phone = trim($data['phone'] ?? '');
if ($phone === '') { http_response_code(400); json_out(['ok'=>false,'error'=>'Número requerido']); }

$userId = 1;
$stmt = $mysqli->prepare("UPDATE users SET phone=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param('si', $phone, $userId);
$ok = $stmt->execute(); $stmt->close();

json_out(['ok'=>$ok ? true : false, 'phone'=>$phone]);
