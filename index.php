<?php
/* ==========================================================================
   index.php — Home / Dashboard de Espacio Liminal
   Cosas que hago aquí:
   - Cargo bootstrap (DB, helpers, .env) y una “mini auth” para tener user_id.
   - Cojo el usuario LOGUEADO desde la BD (nada de hardcode).
   - Intento leer sus puntos desde una tabla “user_points” (si existe).
     Si esa tabla aún no existe o no hay fila: tiro de un valor por defecto.
   - Dejo $build para cache busting (si no hay BUILD definida, uso fecha).
   ========================================================================== */

require_once __DIR__ . '/config/bootstrap.php';   // DB ($mysqli), helpers (hash_pass, json_out, etc.)
require_once __DIR__ . '/config/session.php';     // “shim” de dev: $_SESSION['user_id']=1 si no hay login

header('Content-Type: text/html; charset=utf-8');

/* Cache busting de mis assets (CSS/JS/manifest): uso constante BUILD si existe,
   y si no, la fecha en formato Ymd (suficiente para dev). */
$build = defined('BUILD') ? BUILD : date('Ymd');

/* Pillamos el ID del usuario logueado (si más adelante tienes login real, esto no cambia). */
$userId = auth_user_id();

/* —— 1) Leemos los datos del usuario desde la BD —— */
$user = [
  'name'   => 'Invitado',
  'email'  => '',
  'locale' => 'es',
  'place'  => 'Espacio Tenerife',   // si quieres que esto también venga de BD, añade un campo en users
];
if ($userId) {
  $stmt = $mysqli->prepare("SELECT name, email, locale FROM users WHERE id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
      $stmt->bind_result($name, $email, $locale);
      if ($stmt->fetch()) {
        $user['name']   = $name ?: $user['name'];
        $user['email']  = $email ?: '';
        $user['locale'] = $locale ?: 'es';
      }
    }
    $stmt->close();
  }
}

/* —— 2) Leemos los puntos del usuario (si hay tabla user_points) —— 
   Estructura esperada (simple): user_points(user_id INT PK/FK, total_points INT, goal INT)
   - Si la tabla no existe (MySQL error 1146) o no hay fila → pongo valores por defecto. */
$points  = 1600;  // fallback
$goal    = 2500;  // fallback
if ($userId) {
  $stmt = $mysqli->prepare("SELECT total_points, goal FROM user_points WHERE user_id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
      $stmt->bind_result($p, $g);
      if ($stmt->fetch()) {
        // si hay fila, actualizo; si viene NULL, mantengo fallback
        if ($p !== null) $points = (int)$p;
        if ($g !== null && $g > 0) $goal = (int)$g;
      }
    }
    /* NOTA: si la tabla no existiera, $stmt->execute() devolvería false y podrías
       chequear $mysqli->errno === 1146. Para ir rápido, no paro la página por esto. */
    $stmt->close();
  }
}

/* —— 3) Cálculos de UI —— */
$percent = max(0, min(100, (int)round(($points / max(1,$goal)) * 100)));  // % seguro (evito división por 0)
$active  = 'inicio';  // por si la nav necesita saber dónde estamos
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Inicio · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0f0f0f">
  <meta name="description" content="Panel de inicio de Espacio Liminal: puntos, acciones rápidas y recomendaciones.">

  <!-- Base para que app.js construya bien las URLs (ruta de la app) -->
  <script>
    window.APP_BASE = "/espacio-liminal/";     // si despliegas en otra carpeta, cambia esto y listo
    window.BUILD    = "<?=htmlspecialchars($build)?>";
  </script>

  <!-- 1) VARIABLES CSS SIEMPRE PRIMERO -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css?v=<?=htmlspecialchars($build)?>">

  <!-- 2) UTILIDADES / LAYOUT / COMPONENTES (orden lógico y sano) -->
  <link rel="stylesheet" href="assets/css/core/utilities.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/layout/topbar.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/layout/blocks.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/buttons-links.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/nav.css?v=<?=htmlspecialchars($build)?>">

  <!-- 3) CSS DE PÁGINA -->
  <link rel="stylesheet" href="assets/css/pages/home-dash.css?v=<?=htmlspecialchars($build)?>">

  <!-- 4) RESPONSIVE AL FINAL (tablet → desktop) -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css?v=<?=htmlspecialchars($build)?>">

  <!-- PWA con versión para cache busting -->
  <link rel="manifest" href="/espacio-liminal/pwa/manifest.webmanifest?v=<?=htmlspecialchars($build)?>">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- JS defer (UI + PWA). Ojo: app.js usa window.APP_BASE de arriba -->
  <script defer src="assets/js/app.js?v=<?=htmlspecialchars($build)?>"></script>
  <script defer src="assets/js/pwa.js?v=<?=htmlspecialchars($build)?>"></script>

  <!-- Si alguien navega sin JS, que al menos no se rompa el layout -->
  <noscript>
    <style>.nav,.topbar{position:static}</style>
  </noscript>
</head>

<body>

  <!-- Barra superior sencillita -->
  <header class="topbar" role="banner">
    <span aria-hidden="true"></span>
    <h1>Inicio</h1>
    <span class="topbar__profile">
      <!-- si quieres avatar mini: <img src="assets/img/avatar.jpg" alt="Tu avatar" decoding="async" loading="lazy" /> -->
    </span>
  </header>

  <!-- Contenido principal -->
  <main class="container home-dash has-sticky-nav" role="main">

    <!-- Saludo y resumen de puntos -->
    <section class="hd-welcome card" aria-labelledby="saludoTitle">
      <h2 id="saludoTitle" class="sr-only">Resumen de bienvenida</h2>

      <div class="hd-eyebrow">
        ¡Bienvenido, <span><?=htmlspecialchars($user['name'])?></span>!
      </div>

      <!-- Sitio / sede / lugar — ahora mismo fijo, si quieres lo pasamos a BD -->
      <div class="hd-place"><?=htmlspecialchars($user['place'])?></div>

      <div class="hd-points">
        <div class="hd-row" aria-live="polite">
          <div class="hd-current">
            <?= (int)$points ?> <span class="hd-sum">/ <?= (int)$goal ?></span>
          </div>
        </div>

        <!-- Barra de progreso accesible -->
        <div class="hd-bar" role="progressbar"
             aria-valuemin="0" aria-valuemax="<?= (int)$goal ?>"
             aria-valuenow="<?= (int)$points ?>"
             aria-label="Progreso de puntos hacia el objetivo">
          <span class="hd-bar__fill" style="width: <?= (int)$percent ?>%"></span>
        </div>

        <div class="hd-row hd-row--meta">
          <span class="muted">Puntos obtenidos en la aplicación</span>
          <a class="link" href="#">Ver más</a>
        </div>
      </div>
    </section>

    <!-- Acciones rápidas. Si en el futuro son enlaces externos, añade rel="noopener" -->
    <section class="hd-actions" aria-labelledby="accionesTitle">
      <h2 id="accionesTitle" class="sr-only">Acciones rápidas</h2>

      <a class="hd-card" href="#" aria-label="Reserva tu vuelo">
        <div class="hd-card__head">
          <h3>Reserva tu vuelo</h3>
          <span class="hd-arrow" aria-hidden="true">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>

      <a class="hd-card" href="#" aria-label="Reserva tu transporte">
        <div class="hd-card__head">
          <h3>Reserva tu transporte</h3>
          <span class="hd-arrow" aria-hidden="true">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>

      <a class="hd-card hd-card--wide" href="#" aria-label="Reserva tu hotel">
        <div class="hd-card__head">
          <h3>Reserva tu hotel</h3>
          <span class="hd-arrow" aria-hidden="true">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>
    </section>

    <!-- Recomendaciones (placeholder de momento) -->
    <section class="hd-recos" aria-labelledby="recosTitle">
      <div class="hd-recos__head">
        <h2 id="recosTitle">Recomendaciones</h2>
        <a class="link" href="#">Ver más</a>
      </div>
      <p class="muted">Explora los destinos</p>
      <!-- Aquí meteremos cards cuando haya data -->
    </section>

  </main>

  <?php @include __DIR__.'/partials/nav.php'; /* include en 1 línea, sin dramas: nav inferior (mobile) / sticky (desktop) */ ?>

</body>
</html>
