<?php require_once __DIR__.'/../config/bootstrap.php';
use PHPMailer\PHPMailer\PHPMailer;

$email = strtolower(trim($_POST['email'] ?? ''));
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) json_out(['ok'=>false,'msg'=>'Email inválido']);

$code = str_pad((string)random_int(0,9999),4,'0',STR_PAD_LEFT);
$exp  = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

$ins = $mysqli->prepare("INSERT INTO password_otps(email,code,expires_at) VALUES(?,?,?)");
$ins->bind_param('sss',$email,$code,$exp); $ins->execute(); $ins->close();

$mail = new PHPMailer(true);
try{
  $mail->isSMTP(); $mail->Host=$_ENV['SMTP_HOST']; $mail->SMTPAuth=true;
  $mail->Username=$_ENV['SMTP_USER']; $mail->Password=$_ENV['SMTP_PASS'];
  $mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS; $mail->Port=(int)$_ENV['SMTP_PORT'];
  $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']); $mail->addAddress($email);
  $mail->Subject='Tu código de verificación'; $mail->Body="Tu código es: $code (expira en 10 minutos)";
  $mail->send();
  json_out(['ok'=>true,'msg'=>'OTP enviado']);
}catch(Throwable $e){ json_out(['ok'=>false,'msg'=>'No se pudo enviar el email']); }
