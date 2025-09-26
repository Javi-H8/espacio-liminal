<?php
header('Content-Type: text/html; charset=utf-8');
$user = [
  "name"=>"Rubén Maldonado",
  "email"=>"rubenmaldonado@gmail.com",
  "gender"=>"Masculino",
  "phone"=>"+34 680 86 59 90",
  "locale"=>"es",
  "category"=>"nature"
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="assets/css/core/variables-base.css">
  <link rel="stylesheet" href="assets/css/core/utilities.css">

  <link rel="stylesheet" href="assets/css/layout/topbar.css">
  <link rel="stylesheet" href="assets/css/layout/blocks.css">

  <link rel="stylesheet" href="assets/css/components/buttons-links.css">
  <link rel="stylesheet" href="assets/css/components/forms.css">
  <link rel="stylesheet" href="assets/css/components/sheets.css">
  <link rel="stylesheet" href="assets/css/components/profile-and-misc.css">
  <link rel="stylesheet" href="assets/css/components/nav.css">

  <link rel="stylesheet" href="assets/css/pages/profile.css">

  <link rel="stylesheet" href="assets/css/responsive/tablet.css">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css">

  <meta charset="utf-8">
  <title>Editar perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0f0f0f">
  <link rel="stylesheet" href="assets/css/styles.css">
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <header class="topbar">
    <a class="back" href="/espacio-liminal/">←</a>
    <h1>Editar perfil</h1><span></span>
  </header>

  <main class="container container--page">
    <section class="profile-head">
      <div class="avatar-wrap">
        <img src="assets/img/avatar.jpg" alt="avatar">
        <span class="badge">1</span>
      </div>
      <button class="chip" id="btnChangePhoto">CAMBIAR  FOTO</button>
    </section>

    <ul class="list">
      <li><span class="label">Nombre</span><span class="value"><?=htmlspecialchars($user["name"])?></span><button class="edit" data-edit="name">✎</button></li>
      <li><span class="label">Email</span><span class="value"><?=htmlspecialchars($user["email"])?></span><button class="edit" data-edit="email">✎</button></li>
      <li><span class="label">Contraseña</span><span class="value">********</span><button class="edit" data-open="#sheetPassword">✎</button></li>
      <li><span class="label">Género</span><span class="value"><?=htmlspecialchars($user["gender"])?></span><button class="edit" data-edit="gender">✎</button></li>
      <li><span class="label">Número</span><span class="value"><?=htmlspecialchars($user["phone"])?></span><button class="edit" data-edit="phone">✎</button></li>
    </ul>

    <div class="links"><button class="link" data-open="#sheetForgot">¡Olvidé mi contraseña!</button></div>
    <div class="actions"><button class="btn btn-outline" data-open="#sheetLogout">Cerrar sesión  ➝</button></div>
    <div class="links mt"><a class="link" href="/espacio-liminal/politicas.php">Política y privacidad</a></div>
  </main>

  <?php include __DIR__ . "/partials/nav.php"; ?>

  <?php
    // Incluye sheets si existen (evita warnings si aún no están)
    foreach (['sheet_categoria','sheet_idioma','sheet_password','sheet_forgot','sheet_otp','sheet_logout'] as $s) {
      $path = __DIR__ . "/sheets/$s.php";
      if (file_exists($path)) include $path;
    }
  ?>
</body>
</html>
