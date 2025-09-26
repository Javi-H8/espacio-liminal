<?php require_once __DIR__ . '/config/bootstrap.php'; $build = defined('BUILD')?BUILD:date('Ymd'); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Inicio · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- 1) VARIABLES SIEMPRE PRIMERO -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css?v=<?=htmlspecialchars($build)?>">

  <!-- 2) UTILIDADES / LAYOUT / COMPONENTES -->
  <link rel="stylesheet" href="assets/css/core/utilities.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/layout/topbar.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/layout/blocks.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/buttons-links.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/nav.css?v=<?=htmlspecialchars($build)?>">

  <!-- 3) CSS DE PÁGINA -->
  <link rel="stylesheet" href="assets/css/pages/home-dash.css?v=<?=htmlspecialchars($build)?>">

  <!-- 4) RESPONSIVE AL FINAL -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css?v=<?=htmlspecialchars($build)?>">

  <!-- PWA -->
  <link rel="manifest" href="/espacio-liminal/pwa/manifest.webmanifest?v=<?=htmlspecialchars($build)?>">
  <meta name="theme-color" content="#0f0f0f">
  <script>window.BUILD="<?=htmlspecialchars($build)?>";</script>
  <script defer src="assets/js/app.js?v=<?=htmlspecialchars($build)?>"></script>
  <script defer src="assets/js/pwa.js?v=<?=htmlspecialchars($build)?>"></script>
</head>

<?php
header('Content-Type: text/html; charset=utf-8');
$active = 'inicio';

/* Demo de datos – puedes sustituirlos por valores reales */
$userName  = 'Rubén';
$userPlace = 'Espacio Tenerife';
$points    = 1600;
$goal      = 2500;
$percent   = max(0, min(100, round(($points / $goal) * 100)));
?>

<body>

  <!-- Top: título simple -->
  <header class="topbar">
    <span></span>
    <h1>Inicio</h1>
    <span class="topbar__profile">
      <!-- avatar mini (opcional) -->
      <!-- <img src="assets/img/avatar.jpg" alt="" /> -->
    </span>
  </header>

  <main class="container home-dash has-sticky-nav">

    <!-- Saludo -->
    <section class="hd-welcome card">
      <div class="hd-eyebrow">¡Bienvenido, <span><?=htmlspecialchars($userName)?></span>!</div>
      <div class="hd-place"><?=htmlspecialchars($userPlace)?></div>

      <div class="hd-points">
        <div class="hd-row">
          <div class="hd-current"><?=$points?> <span class="hd-sum">/ <?=$goal?></span></div>
        </div>
        <div class="hd-bar">
          <span class="hd-bar__fill" style="width: <?=$percent?>%"></span>
        </div>
        <div class="hd-row hd-row--meta">
          <span class="muted">Puntos obtenidos en la aplicación</span>
          <a class="link" href="#">Ver más</a>
        </div>
      </div>
    </section>

    <!-- Acciones rápidas -->
    <section class="hd-actions">
      <a class="hd-card" href="#">
        <div class="hd-card__head">
          <h3>Reserva tu vuelo</h3>
          <span class="hd-arrow">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>

      <a class="hd-card" href="#">
        <div class="hd-card__head">
          <h3>Reserva tu transporte</h3>
          <span class="hd-arrow">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>

      <a class="hd-card hd-card--wide" href="#">
        <div class="hd-card__head">
          <h3>Reserva tu hotel</h3>
          <span class="hd-arrow">↗</span>
        </div>
        <p class="muted">Precios especiales</p>
        <p class="hd-note">Usa tus puntos para obtener descuentos</p>
      </a>
    </section>

    <!-- Recomendaciones -->
    <section class="hd-recos">
      <div class="hd-recos__head">
        <h3>Recomendaciones</h3>
        <a class="link" href="#">Ver más</a>
      </div>
      <p class="muted">Explora los destinos</p>
      <!-- Aquí más cards/lista cuando tengas contenido -->
    </section>

  </main>

  <?php include __DIR__.'/partials/nav.php'; ?>

</body>
</html>
