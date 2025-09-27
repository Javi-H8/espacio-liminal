<?php
// db/seed.php
require_once __DIR__ . '/../config/bootstrap.php';

$name  = 'Admin Demo';
$email = 'admin@example.com';
$pass  = 'demo12345';

$hash = hash_pass($pass);

// evitar duplicados si ya existe
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute(); $stmt->store_result();
if ($stmt->num_rows > 0) {
  echo "Usuario ya existe: $email".PHP_EOL;
  exit(0);
}
$stmt->close();

$stmt = $mysqli->prepare("INSERT INTO users(name,email,password_hash,locale,preferred_category,gender) VALUES(?,?,?,?,?,?)");
$locale='es'; $cat='todos'; $gender='unspecified';
$stmt->bind_param('ssssss', $name, $email, $hash, $locale, $cat, $gender);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

echo "Usuario seed creado (id=$id) -> $email / $pass".PHP_EOL;
