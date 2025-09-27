<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$gender = trim($data['gender'] ?? '');
$allowed = ['Masculino','Femenino','Sin especificar','Otro'];
if (!in_array($gender, $allowed, true)) { http_response_code(400); json_out(['ok'=>false,'error'=>'Valor no válido']); }

$userId = 1;
$stmt = $mysqli->prepare("UPDATE users SET gender=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param('si', $gender, $userId);
$ok = $stmt->execute(); $stmt->close();

json_out(['ok'=>$ok ? true : false, 'gender'=>$gender]);
