<?php
/* ========================================================================
   Editar perfil
   - Carga bootstrap (env, sesión, helpers)
   - Lista editable y accesos a sheets (contraseña, forgot, OTP, logout…)
   ======================================================================== */
require_once __DIR__ . '/config/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');

$user = [
  "name"     => "Rubén Maldonado",
  "email"    => "rubenmaldonado@gmail.com",
  "gender"   => "Masculino",
  "phone"    => "+34 680 86 59 90",
  "locale"   => "es",
  "category" => "nature"
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0f0f0f">

  <!-- CORE -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css">
  <link rel="stylesheet" href="assets/css/core/utilities.css">

  <!-- LAYOUT -->
  <link rel="stylesheet" href="assets/css/layout/topbar.css">
  <link rel="stylesheet" href="assets/css/layout/blocks.css">

  <!-- COMPONENTS -->
  <link rel="stylesheet" href="assets/css/components/buttons-links.css">
  <link rel="stylesheet" href="assets/css/components/forms.css">
  <link rel="stylesheet" href="assets/css/components/sheets.css">
  <link rel="stylesheet" href="assets/css/components/profile-and-misc.css">
  <link rel="stylesheet" href="assets/css/components/nav.css">

  <!-- PÁGINA -->
  <link rel="stylesheet" href="assets/css/pages/profile.css">

  <!-- RESPONSIVE -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css">

  <!-- JS general -->
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <header class="topbar">
    <a class="back" href="/espacio-liminal/" aria-label="Volver">←</a>
    <h1>Editar perfil</h1><span></span>
  </header>

  <main class="container container--page has-sticky-nav">
    <!-- Cabecera con avatar y “Cambiar foto” -->
    <section class="profile-head">
      <div class="avatar-wrap">
        <img src="assets/img/avatar.jpg" alt="avatar">
        <span class="badge">1</span>
      </div>
      <button class="chip" id="btnChangePhoto" type="button">CAMBIAR&nbsp;&nbsp;FOTO</button>
    </section>

    <!-- Lista editable (cada fila puede abrir un sheet o inline edit) -->
    <ul class="list">
      <li>
        <span class="label">Nombre</span>
        <span class="value js-name"><?=htmlspecialchars($user["name"])?></span>
        <button class="edit" data-edit="name" aria-label="Editar nombre">✎</button>
      </li>
      <li>
        <span class="label">Email</span>
        <span class="value js-email"><?=htmlspecialchars($user["email"])?></span>
        <button class="edit" data-edit="email" aria-label="Editar email">✎</button>
      </li>
      <li>
        <span class="label">Contraseña</span>
        <span class="value">********</span>
        <!-- Abre sheetPassword (ojo: sin #) -->
        <button class="edit" data-dialog-open="sheetPassword" aria-label="Cambiar contraseña">✎</button>
      </li>
      <li>
        <span class="label">Género</span>
        <span class="value js-gender"><?=htmlspecialchars($user["gender"])?></span>
        <button class="edit" data-edit="gender" aria-label="Seleccionar género">✎</button>
      </li>
      <li>
        <span class="label">Número</span>
        <span class="value js-phone"><?=htmlspecialchars($user["phone"])?></span>
        <button class="edit" data-edit="phone" aria-label="Editar teléfono">✎</button>
      </li>
    </ul>

    <!-- Enlaces y acciones varias -->
    <div class="links">
      <!-- Abre sheetForgot -->
      <button class="link" type="button" data-dialog-open="sheetForgot">¡Olvidé mi contraseña!</button>
    </div>

    <div class="actions">
      <!-- Abre sheetLogout -->
      <button class="btn btn-outline" type="button" data-dialog-open="sheetLogout">Cerrar sesión  ➝</button>
    </div>

    <div class="links mt">
      <a class="link" href="/espacio-liminal/politicas.php">Política y privacidad</a>
    </div>
  </main>

  <?php include __DIR__ . "/partials/nav.php"; ?>

  <?php
    /* Sheets/Bottom Sheets:
       - Se incluyen solo si existen, así no saltan warnings en dev.
       - Asegúrate de que los IDs dentro de cada archivo coinciden EXACTO
         con lo que abrimos arriba: sheet_categoria, sheet_idioma, sheet_password,
         sheet_forgot, sheet_otp, sheet_logout (sin # en data-dialog-open).
    */
    foreach (['sheet_categoria','sheet_idioma','sheet_password','sheet_forgot','sheet_otp','sheet_logout'] as $s) {
      $path = __DIR__ . "/sheets/$s.php";
      if (file_exists($path)) include $path;
    }
  ?>
</body>
</html>
