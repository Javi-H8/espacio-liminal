<?php require_once __DIR__.'/../config/bootstrap.php';
$uid   = (int)($_POST['user_id'] ?? 0);
$phone = $_POST['phone'] ?? null; $locale = $_POST['locale'] ?? null; $cat = $_POST['category'] ?? null;
if(!$uid) json_out(['ok'=>false,'msg'=>'No auth']); // (cuando metas sesiones/tokens cambia esto)

$sql="UPDATE user_profiles SET phone=COALESCE(?,phone), locale=COALESCE(?,locale), category=COALESCE(?,category) WHERE user_id=?";
$stmt=$mysqli->prepare($sql); $stmt->bind_param('sssi',$phone,$locale,$cat,$uid); $stmt->execute(); $stmt->close();
json_out(['ok'=>true]);
