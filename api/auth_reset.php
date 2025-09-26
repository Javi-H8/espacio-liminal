<?php require_once __DIR__.'/../config/bootstrap.php';
$email = strtolower(trim($_POST['email'] ?? '')); $new = $_POST['new_password'] ?? '';
if(!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($new)<6) json_out(['ok'=>false,'msg'=>'Datos inválidos']);

$hash = hash_pass($new);
$u = $mysqli->prepare("UPDATE users SET password=? WHERE email=?");
$u->bind_param('ss',$hash,$email); $u->execute(); $u->close();
json_out(['ok'=>true,'msg'=>'Contraseña actualizada']);
