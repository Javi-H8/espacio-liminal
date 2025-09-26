<?php require_once __DIR__.'/../config/bootstrap.php';
$email = strtolower(trim($_POST['email'] ?? '')); $code = trim($_POST['code'] ?? '');
if(!filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{4}$/',$code)) json_out(['ok'=>false,'msg'=>'Datos inválidos']);

$q = $mysqli->prepare("SELECT id,expires_at,used FROM password_otps WHERE email=? AND code=? ORDER BY id DESC LIMIT 1");
$q->bind_param('ss',$email,$code); $q->execute(); $q->bind_result($id,$expires,$used);
if(!$q->fetch()) json_out(['ok'=>false,'msg'=>'Código incorrecto']); $q->close();
if($used) json_out(['ok'=>false,'msg'=>'Código ya usado']);
if(new DateTime()> new DateTime($expires)) json_out(['ok'=>false,'msg'=>'Código expirado']);

$mysqli->query("UPDATE password_otps SET used=1 WHERE id=$id");
json_out(['ok'=>true,'msg'=>'Código verificado']);
