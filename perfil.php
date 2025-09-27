<?php
/* ==========================================================================
   perfil.php — Hub de Perfil (la “portada” del usuario)
   Qué hago aquí, en cristiano:
   - Cargo bootstrap (DB, helpers) y una mini sesión de dev (hasta tener login real).
   - Saco el usuario logueado desde la BD (nada de hardcode).
   - Pinto su nombre, email y avatar (si no hay avatar, pongo uno neutro).
   - Dejo APP_BASE y BUILD para que el front sepa construir URLs y cachear como Dios manda.
   ========================================================================== */

require_once __DIR__ . '/config/bootstrap.php';  // $mysqli, helpers (json_out, etc.)
require_once __DIR__ . '/config/session.php';    // shim de dev: $_SESSION['user_id']=1 si no hay login

header('Content-Type: text/html; charset=utf-8');

/* Cache busting: si existe define('BUILD', '20250927'), la uso; si no, YYYYmmdd */
$build = defined('BUILD') ? BUILD : date('Ymd');

/* Pillamos ID de usuario (cuando tengas auth real, esto no cambia) */
$userId = auth_user_id();

/* === Usuario desde la BD (nombre, email, locale, preferred_category, avatar_url) ===
   Si no hay fila (o aún no hay sesión), pinto valores de cortesía para que no reviente. */
$user = [
  'name'   => 'Invitado',
  'email'  => '',
  'avatar' => 'assets/img/avatar.jpg',  // si no tienes foto del user, tiro de este path
  'locale' => 'es',
  'cat'    => 'todos',
];
if ($userId) {
  $stmt = $mysqli->prepare(
    "SELECT name, email, COALESCE(avatar_url,''), COALESCE(locale,'es'), COALESCE(preferred_category,'todos')
       FROM users WHERE id=? LIMIT 1"
  );
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
      $stmt->bind_result($name,$email,$avatar,$locale,$cat);
      if ($stmt->fetch()) {
        if ($name)   $user['name']   = $name;
        if ($email)  $user['email']  = $email;
        if ($avatar) $user['avatar'] = $avatar;
        $user['locale'] = $locale ?: 'es';
        $user['cat']    = $cat    ?: 'todos';
      }
    }
    $stmt->close();
  }
}

/* Mapitas bonitos para enseñar “subtítulos” de idioma/categoría en humano.
   (Los uso en .js-lang-sub y .js-cat-sub para que el front pueda actualizarlos en vivo) */
$langMap = ['es'=>'Español','en'=>'Inglés','fr'=>'Francés','it'=>'Italiano'];
$catMap  = [
  'naturaleza'=>'Naturaleza','ocio'=>'Ocio','aventura'=>'Aventura',
  'cultural'=>'Cultural','todos'=>'Todos','custom'=>'Selección personalizada'
];

/* Para la nav inferior (por si marcas activo) */
$active = 'perfil';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Base de la app para que app.js construya bien las rutas (importante en /espacio-liminal/) -->
  <script>
    window.APP_BASE = "/espacio-liminal/";               // cambia si despliegas en otra carpeta
    window.BUILD    = "<?=htmlspecialchars($build)?>";    // útil si en el future quieres comparar versiones
  </script>

  <!-- CORE: variables primero, utilidades después -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/core/utilities.css?v=<?=htmlspecialchars($build)?>">

  <!-- LAYOUT / COMPONENTS compartidos -->
  <link rel="stylesheet" href="assets/css/layout/topbar.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/buttons-links.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/nav.css?v=<?=htmlspecialchars($build)?>">

  <!-- CSS específico de esta página -->
  <link rel="stylesheet" href="assets/css/pages/profile-hub.css?v=<?=htmlspecialchars($build)?>">

  <!-- RESPONSIVE al final -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css?v=<?=htmlspecialchars($build)?>">

  <!-- JS general de UI (abre/cierra sheets, OTP, acciones…) -->
  <script defer src="assets/js/app.js?v=<?=htmlspecialchars($build)?>"></script>
</head>
<body>

  <!-- Topbar con botón de volver (usa APP_BASE para no romper rutas) -->
  <header class="topbar" role="banner">
    <a class="back" href="/espacio-liminal/" aria-label="Volver">←</a>
    <h1>Perfil</h1><span aria-hidden="true"></span>
  </header>

  <!-- Contenedor principal. “has-sticky-nav” por si en desktop la nav sube bajo topbar -->
  <main class="container profile-hub has-sticky-nav" role="main">

    <!-- Cabecera de perfil: avatar + nombre + email -->
    <div class="ph-head">
      <div class="ph-avatar">
        <!-- Si el usuario no tiene avatar_url, este <img> tirará del fallback de arriba -->
        <img src="<?=htmlspecialchars($user['avatar'])?>" alt="Foto de perfil" decoding="async" loading="lazy">
      </div>
      <div class="ph-id">
        <div class="ph-name"><?=htmlspecialchars($user['name'])?></div>
        <div class="ph-mail"><?=htmlspecialchars($user['email'])?></div>
      </div>
    </div>

    <!-- Atajos rápidos (de momento son enlaces “placeholder”) -->
    <section class="ph-group" aria-labelledby="atajosTitle">
      <h2 id="atajosTitle" class="sr-only">Atajos de perfil</h2>

      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--orange" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Zm0 7L2 4v13l10 5 10-5V4l-10 5Z"/></svg>
        </span>
        <span class="ph-text">Mis puntos</span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>

      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--green" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v10H4z"/><path d="M9 11h6v2H9z"/></svg>
        </span>
        <span class="ph-text">Mis escaneos</span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>

      <a class="ph-item" href="#">
        <span class="ph-ico ph-ico--yellow" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="M7 10h10v4H7z"/></svg>
        </span>
        <span class="ph-text">Mis reservas</span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>
    </section>

    <!-- Ajustes generales -->
    <h3 class="ph-title">Ajustes generales</h3>
    <section class="ph-group">
      <!-- Editar perfil (lleva a la pantalla de edición con todas las hojas) -->
      <a class="ph-item" href="/espacio-liminal/perfil_editar.php">
        <span class="ph-ico" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Z"/><path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83Z"/></svg>
        </span>
        <span class="ph-text">Editar perfil</span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>

      <!-- Cambiar idioma: abre hoja “sheetIdioma” (sin # porque usamos data-dialog-open) -->
      <button class="ph-item" type="button" data-dialog-open="sheetIdioma">
        <span class="ph-ico" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v6H4zM4 14h7v6H4zM13 14h7v6h-7z"/></svg>
        </span>
        <span class="ph-text">
          Cambiar idioma
          <small class="muted js-lang-sub" style="display:block">
            <?= htmlspecialchars($langMap[$user['locale']] ?? $user['locale']) ?>
          </small>
        </span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </button>

      <!-- Cambiar categoría: abre hoja “sheetCategoria” -->
      <button class="ph-item" type="button" data-dialog-open="sheetCategoria">
        <span class="ph-ico ph-ico--dot" aria-hidden="true"></span>
        <span class="ph-text">
          Cambiar categoría
          <small class="muted js-cat-sub" style="display:block">
            <?= htmlspecialchars($catMap[$user['cat']] ?? $user['cat']) ?>
          </small>
        </span>
        <span class="ph-chevron" aria-hidden="true">›</span>
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
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>

      <a class="ph-item ph-item--accent" href="#">
        <span class="ph-ico ph-ico--accent" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z"/></svg>
        </span>
        <span class="ph-text">Ayuda y Tutoriales</span>
        <span class="ph-chevron" aria-hidden="true">›</span>
      </a>
    </section>
  </main>

  <?php @include __DIR__.'/partials/nav.php'; /* include en 1 línea: nav inferior (mobile) / sticky (desktop) */ ?>

  <?php
    /* Sheets: las incluyo “si existen” para no spamear warnings en desarrollo.
       Importante: los IDs dentro de esos archivos deben ser sheetIdioma y sheetCategoria. */
    foreach (['sheet_categoria','sheet_idioma'] as $s) {
      $p = __DIR__."/sheets/$s.php"; if (file_exists($p)) include $p;
    }
  ?>
</body>
</html>
