<?php
require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* Limpieza de sesión y cookies de sesión */
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();

/* (Opcional) si usas tokens persistentes, bórralos de BD aquí */

json_out(['ok'=>true]);
