<?php require_once __DIR__.'/../config/bootstrap.php';
$name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $pass = $_POST['password'] ?? '';
if(!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<6) json_out(['ok'=>false,'msg'=>'Datos inválidos']);

$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?"); $stmt->bind_param('s',$email); $stmt->execute(); $stmt->store_result();
if($stmt->num_rows>0) json_out(['ok'=>false,'msg'=>'Email ya registrado']); $stmt->close();

$hash = hash_pass($pass);
$stmt = $mysqli->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
$stmt->bind_param('sss',$name,$email,$hash); $stmt->execute(); $uid=$stmt->insert_id; $stmt->close();

$mysqli->query("INSERT INTO user_profiles(user_id) VALUES ($uid)");
json_out(['ok'=>true,'user_id'=>$uid]);
