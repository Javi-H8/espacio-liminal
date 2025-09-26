<?php
/* ========================================================================
   Perfil (hub)
   - Carga bootstrap para tener sesión/helpers y futuro CSRF/BUILD
   - Vista general del usuario con accesos a ajustes y hojas (sheets)
   ======================================================================== */
require_once __DIR__ . '/config/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');

$active = 'perfil';
$user = [
  "name"   => "Rubén Maldonado",
  "email"  => "rubenmaldonado@gmail.com",
  "avatar" => "assets/img/avatar.jpg"
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CORE: variables primero, utilidades después -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css">
  <link rel="stylesheet" href="assets/css/core/utilities.css">

  <!-- LAYOUT / COMPONENTS -->
  <link rel="stylesheet" href="assets/css/layout/topbar.css">
  <link rel="stylesheet" href="assets/css/components/buttons-links.css">
  <link rel="stylesheet" href="assets/css/components/nav.css">

  <!-- PÁGINA -->
  <link rel="stylesheet" href="assets/css/pages/profile-hub.css">

  <!-- RESPONSIVE -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css">

  <!-- JS general (abre/cierra sheets, OTP, etc.) -->
  <script defer src="assets/js/app.js"></script>
</head>
<body>

  <!-- Topbar sencilla con volver -->
  <header class="topbar">
    <a class="back" href="/espacio-liminal/" aria-label="Volver">←</a>
    <h1>Perfil</h1><span></span>
  </header>

  <!-- Importante en desktop si la nav sube bajo la topbar -->
  <main class="container profile-hub has-sticky-nav">
    <!-- Avatar + nombre -->
    <div class="ph-head">
      <div class="ph-avatar">
        <img src="<?=htmlspecialchars($user['avatar'])?>" alt="Foto de perfil">
      </div>
      <div class="ph-id">
        <div class="ph-name"><?=htmlspecialchars($user['name'])?></div>
        <div class="ph-mail"><?=htmlspecialchars($user['email'])?></div>
      </div>
    </div>

    <!-- Atajos -->
    <section class="ph-group">
      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--orange" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Zm0 7L2 4v13l10 5 10-5V4l-10 5Z"/></svg>
        </span>
        <span class="ph-text">Mis puntos</span>
        <span class="ph-chevron">›</span>
      </a>

      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--green" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v10H4z"/><path d="M9 11h6v2H9z"/></svg>
        </span>
        <span class="ph-text">Mis escaneos</span>
        <span class="ph-chevron">›</span>
      </a>

      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--yellow" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="M7 10h10v4H7z"/></svg>
        </span>
        <span class="ph-text">Mis reservas</span>
        <span class="ph-chevron">›</span>
      </a>
    </section>

    <!-- Ajustes generales -->
    <h3 class="ph-title">Ajustes generales</h3>
    <section class="ph-group">
      <a class="ph-item" href="/espacio-liminal/perfil_editar.php">
        <span class="ph-ico" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Z"/><path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83Z"/></svg>
        </span>
        <span class="ph-text">Editar perfil</span>
        <span class="ph-chevron">›</span>
      </a>

      <!-- Cambiar idioma: abre sheetIdioma (ojo: sin #, es un ID) -->
      <button class="ph-item" type="button" data-dialog-open="sheetIdioma">
        <span class="ph-ico" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v6H4zM4 14h7v6H4zM13 14h7v6h-7z"/></svg>
        </span>
        <span class="ph-text">Cambiar idioma</span>
        <span class="ph-chevron">›</span>
      </button>

      <!-- Cambiar categoría: abre sheetCategoria -->
      <button class="ph-item" type="button" data-dialog-open="sheetCategoria">
        <span class="ph-ico ph-ico--dot" aria-hidden="true"></span>
        <span class="ph-text">Cambiar categoría</span>
        <span class="ph-chevron">›</span>
      </button>
    </section>

    <!-- Otros ajustes -->
    <h3 class="ph-title">Otros ajustes</h3>
    <section class="ph-group">
      <a class="ph-item" href="/espacio-liminal/politicas.php">
        <span class="ph-ico" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h9l3 3v17H6z"/><path d="M9 8h6M9 12h6M9 16h6"/></svg>
        </span>
        <span class="ph-text">Privacidad y Políticas</span>
        <span class="ph-chevron">›</span>
      </a>

      <a class="ph-item ph-item--accent" href="#">
        <span class="ph-ico ph-ico--accent" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z"/></svg>
        </span>
        <span class="ph-text">Ayuda y Tutoriales</span>
        <span class="ph-chevron">›</span>
      </a>
    </section>
  </main>

  <?php include __DIR__.'/partials/nav.php'; ?>

  <?php
    /* Sheets: se incluirán solo si existen, así no hay warnings */
    foreach (['sheet_categoria','sheet_idioma'] as $s) {
      $p = __DIR__."/sheets/$s.php";
      if (file_exists($p)) include $p;
    }
  ?>
</body>
</html>
