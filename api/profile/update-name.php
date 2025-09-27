<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($data['name'] ?? '');
if ($name === '') { http_response_code(400); json_out(['ok'=>false,'error'=>'Nombre requerido']); }

$userId = 1; // TODO: sustituir por el ID de usuario en sesión
$stmt = $mysqli->prepare("UPDATE users SET name=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param('si', $name, $userId);
$ok = $stmt->execute(); $stmt->close();

json_out(['ok'=>$ok ? true : false, 'name'=>$name]);
