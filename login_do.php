<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.php'); exit; }

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$pass  = (string)($_POST['password'] ?? '');
$csrf  = $_POST['csrf'] ?? null;
csrf_verify($csrf);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass==='') {
  header('Location: login.php?e=1'); exit;
}

$row = db_exec("SELECT id,password_hash FROM users WHERE email=?", 's', [$email])[0] ?? null;
if (!$row || !password_verify($pass, $row['password_hash'])) { header('Location: login.php?e=1'); exit; }

auth_login((int)$row['id'], !empty($_POST['remember']));
header('Location: /espacio-liminal/');
