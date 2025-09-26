<?php require_once __DIR__.'/../config/bootstrap.php';
$email = strtolower(trim($_POST['email'] ?? '')); $pass = $_POST['password'] ?? '';
if(!filter_var($email,FILTER_VALIDATE_EMAIL) || !$pass) json_out(['ok'=>false,'msg'=>'Credenciales']);

$stmt = $mysqli->prepare("SELECT id,password FROM users WHERE email=?"); $stmt->bind_param('s',$email); $stmt->execute();
$stmt->bind_result($uid,$hash); if(!$stmt->fetch()) json_out(['ok'=>false,'msg'=>'Credenciales']); $stmt->close();
if(!check_pass($pass,$hash)) json_out(['ok'=>false,'msg'=>'Credenciales']);

json_out(['ok'=>true,'user_id'=>$uid]);
