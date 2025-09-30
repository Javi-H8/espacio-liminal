<?php
/**
 * Paso 1 reset password: generar CÓDIGO para reset por email
 * - Entrada JSON: { csrf, email }
 * - No revelo si existe o no (respuesta genérica), pero guardo verificación si existe.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// OJO: aquí NO exigimos estar logueado (es para olvidos), así que no incluyo session.php

$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null); // si lo llamas desde una página pública, pinta el token igualmente

$email = strtolower(trim((string)($in['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_out(['ok'=>true]); // respuesta neutra para no filtrar usuarios
}

// ¿Existe ese email?
$user = db_exec("SELECT id FROM users WHERE email=?", 's', [$email])[0] ?? null;
if ($user) {
  $uid   = (int)$user['id'];
  $code  = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
  $token = bin2hex(random_bytes(16));
  $exp   = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

  // Limpio previos
  db_exec_nonquery("DELETE FROM user_verifications WHERE user_id=? AND purpose='password_reset'", 'i', [$uid]);

  db_exec_nonquery(
    "INSERT INTO user_verifications (user_id,purpose,target_value,code,token,expires_at,created_at)
     VALUES (?,?,?,?,?, ?, NOW())",
    'isssss', [$uid,'password_reset',$email,$code,$token,$exp]
  );

  // TODO: enviar email con $code o link con $token
  if (is_dev()) error_log("[DEV] Reset code $code para $email");
}

// Siempre devuelvo OK (no doy pistas)
json_out(['ok'=>true]);
