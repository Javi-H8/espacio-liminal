<?php
/**
 * =========================================================================
 * INDEX (Inicio “panel” sólido y con estilo uniforme)
 * -------------------------------------------------------------------------
 * - Mismo stack de CSS que hemos creado (core → layout → components → pages).
 * - Nav inferior SIEMPRE visible en todo el site (fija) y sin tapar contenido
 *   gracias a <body class="with-bottom-nav"> + padding-bottom global (reset.css).
 * - Manejo fino de errores: si la DB falla o no existe db_exec(), NO revientas.
 * - Progreso de puntos si user logueado; sino, CTA para login/registro.
 * =========================================================================
 */

declare(strict_types=1);

/* ===== 0) Diagnóstico en LOCAL (para cazar el 500 rapidito) =====
   - En localhost: muestro errores a saco.
   - En producción: apagados, que quede fino. */
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost','127.0.0.1','::1'], true);
if ($isLocal) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
}

/* ===== 1) Arranque del proyecto =====
   - bootstrap.php debería definir db_exec() y conectar a MySQL.
   - session.php: sesión y helpers tipo auth_user_id(). */
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

/* Pequeño helper para versionar assets sin que BUILD te dé guerra si no existe */
$ASSET_V = defined('BUILD') ? (string)BUILD : '1';

/* ===== 2) Usuario + puntos (si se puede) =====
   - Si algo falla (no hay db_exec, excepción, etc.), NO casco la página.
   - Saco bandera $dbProblem para mostrar barrita roja solo en local. */
$uid       = null;
$user      = null;
$pts       = null;
$dbProblem = false;

try {
  $uid = auth_user_id(); // si tienes token/sesión, te lo da
  if ($uid) {
    if (!function_exists('db_exec')) {
      $dbProblem = true; // no existe el helper → lo marco y sigo
    } else {
      // Usuario básico
      $user = db_exec("SELECT id,name,email FROM users WHERE id=? LIMIT 1", 'i', [$uid])[0] ?? null;
      // Puntos (si tu tabla existe, perfecto; si no, tampoco muero)
      $pts  = db_exec("SELECT total_points, goal FROM user_points WHERE user_id=? LIMIT 1", 'i', [$uid])[0] ?? null;
    }
  }
} catch (Throwable $e) {
  // Cualquier cosa rara con la DB → bandera y pa'lante
  $dbProblem = true;
}

/* ===== 3) Cálculo de progreso =====
   - Si no hay datos, saco defaults decentes para que el UI no llore. */
$curPoints   = $pts ? (int)$pts['total_points'] : 0;
$goalTotal   = $pts ? max(1, (int)$pts['goal']) : 2500; // meta por defecto
$progressPct = (int)min(100, max(0, round($curPoints * 100 / max(1, $goalTotal))));

/* ===== 4) Marco activo de nav =====
   - Para que en la nav inferior pinte “Inicio” como seleccionado. */
$active = 'inicio';

/* ===== 5) APP_BASE para JS (si lo usas en app.js) ===== */
echo '<script>window.APP_BASE = "/espacio-liminal/";</script>';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Inicio · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- color de la barra del navegador en móvil (a juego con tu dark) -->
  <meta name="theme-color" content="#0f0f0f">

  <!-- ==================== CSS: core → layout → components → pages → responsive ==================== -->
  <!-- CORE (identidad visual + reset + tipografía base) -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/core/reset.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/core/typography.css?v=<?= htmlspecialchars($ASSET_V) ?>">

  <!-- LAYOUT (topbar pegadita con blur) -->
  <link rel="stylesheet" href="assets/css/layout/topbar.css?v=<?= htmlspecialchars($ASSET_V) ?>">

  <!-- COMPONENTS (botones, formularios y NAV INFERIOR FIJA SIEMPRE) -->
  <link rel="stylesheet" href="assets/css/components/buttons-links.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/components/forms.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/components/nav.css?v=<?= htmlspecialchars($ASSET_V) ?>">

  <!-- PÁGINA (home: hero + chips + grid de bloques) -->
  <link rel="stylesheet" href="assets/css/pages/home.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <!-- Reaprovecho base visual pw-* por coherencia con perfil (tarjetas, inputs, etc.) -->
  <link rel="stylesheet" href="assets/css/pages/profile.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/pages/profile.mobile.css?v=<?= htmlspecialchars($ASSET_V) ?>">
  <link rel="stylesheet" href="assets/css/pages/profile.web.css?v=<?= htmlspecialchars($ASSET_V) ?>">

  <!-- RESPONSIVE (no escondo nav en desktop porque tú la quieres SIEMPRE) -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css?v=<?=BUILD?>">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css?v=<?=BUILD?>">
  <link rel="stylesheet" href="assets/css/responsive/ultrawide.css?v=<?=BUILD?>">

  <!-- PWA nice-to-have, por si lo estabas usando -->
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="manifest" href="pwa/manifest.json">
</head>

<!-- MUY IMPORTANTE: class="with-bottom-nav" = deja aire inferior global
     para que la nav fija de abajo NO tape el contenido ni en móvil ni en PC -->
<body class="with-bottom-nav">

  <!-- Aviso DEV si hubo problema con la DB (solo en local para no asustar al personal) -->
  <?php if ($isLocal && $dbProblem): ?>
    <div style="position:sticky;top:0;z-index:9999;background:#ff0044;color:#fff;padding:8px 12px;font-weight:700">
      DEV: problema con la base de datos o falta <code>db_exec()</code> en <code>config/bootstrap.php</code>.
      No casco la página (tranqui). Revisa credenciales y logs de Apache/MySQL.
    </div>
  <?php endif; ?>

  <!-- ==================== TOPBAR (arriba del todo) ==================== -->
  <header class="topbar" role="banner" aria-label="Barra superior">
    <!-- Botón “atrás” (no te saca de la web, hace history.back) -->
    <a class="topbar__action back" href="#" aria-label="Volver atrás" onclick="history.back();return false;">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m14 7-5 5 5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>

    <!-- Título centrado (con elipsis si se desmadra) -->
    <h1>Inicio</h1>

    <!-- Acceso rápido al perfil -->
    <a class="topbar__action" href="perfil.php" aria-label="Mi perfil">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5.33 0-8 2.67-8 6v2h16v-2c0-3.33-2.67-6-8-6Z" fill="currentColor"/></svg>
    </a>
  </header>

  <!-- ==================== CONTENIDO PRINCIPAL ==================== -->
  <main class="home" role="main">
    <!-- ===== HERO / BIENVENIDA + PUNTOS ===== -->
    <section class="card home-hero" aria-labelledby="home-hero-title">
      <div class="home-hero__text">
        <p class="eyebrow">Tu espacio de viaje</p>
        <h1 id="home-hero-title">
          <?php if ($user): ?>
            Hola, <span><?= htmlspecialchars($user['name'] ?: 'viajer@') ?></span>
          <?php else: ?>
            Bienvenido a <span>Espacio Liminal</span>
          <?php endif; ?>
        </h1>
        <p class="lead">
          Planifica en dos clics, acumula puntos y desbloquea ventajas. Todo con la misma estética fina que estamos usando en el proyecto.
        </p>

        <!-- CTA (si no estás logueado, te invito a entrar/registrarte) -->
        <?php if (!$user): ?>
          <div class="home-cta">
            <a class="button" href="login.php">Entrar</a>
            <a class="button button--ghost" href="register.php">Crear cuenta</a>
          </div>
        <?php endif; ?>

        <!-- Chips (categorías rápidas). Si no quieres, las quitas y ya. -->
        <ul class="chip-list" role="list">
          <li><button class="chip is-active" type="button">Destacados</button></li>
          <li><button class="chip" type="button">Naturaleza</button></li>
          <li><button class="chip" type="button">Aventura</button></li>
          <li><button class="chip" type="button">Cultural</button></li>
          <li><button class="chip" type="button">Ocio</button></li>
        </ul>
      </div>

      <!-- Tarjetita de puntos (solo si hay user). Si no, la omito y queda simétrico. -->
      <?php if ($user): ?>
        <div class="card" aria-live="polite" style="padding:16px">
          <div class="muted" style="margin-bottom:6px">Tus puntos</div>

          <!-- Fila cifras gordas -->
          <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:8px">
            <div style="font-size:28px;font-weight:800">
              <?= number_format($curPoints, 0, ',', '.') ?>
            </div>
            <div class="muted">de <?= number_format($goalTotal, 0, ',', '.') ?></div>
          </div>

          <!-- Barra de progreso accesible -->
          <div class="hd-bar" role="progressbar"
               aria-label="Progreso de puntos"
               aria-valuemin="0" aria-valuemax="100"
               aria-valuenow="<?= $progressPct ?>"
               style="height:10px;border:1px solid var(--border);border-radius:999px;overflow:hidden;background:#111">
            <span class="hd-bar__fill" style="display:block;height:100%;width:<?= $progressPct ?>%;background:linear-gradient(90deg,var(--accent),#b3ff00)"></span>
          </div>

          <p class="muted" style="margin-top:8px">Sigue usando la app para conseguir ventajas y descuentos.</p>
        </div>
      <?php endif; ?>
    </section>

    <!-- ===== ACCIONES RÁPIDAS (3 tarjetas) ===== -->
    <section class="blocks" aria-label="Accesos rápidos">
      <a class="block" href="#">
        <h3>Reserva tu vuelo</h3>
        <p>Precios especiales para miembros. Usa tus puntos para rascar descuento.</p>
      </a>

      <a class="block" href="#">
        <h3>Transporte</h3>
        <p>Trenes, buses y traslados en un clic. Sin comisiones raras.</p>
      </a>

      <a class="block" href="#">
        <h3>Hotel</h3>
        <p>Encuentra alojamiento al mejor precio. Cancelación flexible.</p>
      </a>
    </section>

    <!-- ===== RECOMENDACIONES (listado simple) ===== -->
    <section class="card" style="margin-top:16px;padding:16px" aria-labelledby="recos-title">
      <header class="hd-recos__head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <h3 id="recos-title">Recomendaciones</h3>
        <a class="link" href="#">Ver más</a>
      </header>

      <ul class="list list--places" style="list-style:none;margin:0;padding:0;display:grid;gap:10px">
        <li>
          <a class="place" href="#" style="display:flex;gap:10px;align-items:center;text-decoration:none">
            <span class="place__thumb" style="width:48px;height:48px;background:#111;border:1px solid var(--border);border-radius:10px;display:block"></span>
            <span class="place__meta" style="display:grid">
              <strong class="place__name">Restaurantes cerca</strong>
              <small class="place__sub muted">Ahorra tiempo buscando</small>
            </span>
          </a>
        </li>
        <li>
          <a class="place" href="#" style="display:flex;gap:10px;align-items:center;text-decoration:none">
            <span class="place__thumb" style="width:48px;height:48px;background:#111;border:1px solid var(--border);border-radius:10px;display:block"></span>
            <span class="place__meta" style="display:grid">
              <strong class="place__name">Rutas alternativas</strong>
              <small class="place__sub muted">Evita atascos y peajes</small>
            </span>
          </a>
        </li>
        <li>
          <a class="place" href="#" style="display:flex;gap:10px;align-items:center;text-decoration:none">
            <span class="place__thumb" style="width:48px;height:48px;background:#111;border:1px solid var(--border);border-radius:10px;display:block"></span>
            <span class="place__meta" style="display:grid">
              <strong class="place__name">Ocio en la zona</strong>
              <small class="place__sub muted">Planazos por menos de 15 €</small>
            </span>
          </a>
        </li>
      </ul>
    </section>

    <!-- ===== DEBUG RÁPIDO (solo si hay problemas de DB y estás en local) ===== -->
    <?php if ($isLocal && $dbProblem): ?>
      <section class="card" style="padding:16px;margin-top:16px">
        <strong>Debug rápido (DB):</strong>
        <ol style="margin:8px 0 0 18px">
          <li>¿Existe <code>config/bootstrap.php</code> y define <code>db_exec()</code>?</li>
          <li>¿Credenciales MySQL OK? (host, db, user, pass)</li>
          <li>Logs Apache: <em>xampp/apache/logs/error.log</em> (Win) o <em>/var/log/apache2/error.log</em> (Linux)</li>
        </ol>
      </section>
    <?php endif; ?>
  </main>

  <!-- ==================== NAV INFERIOR SIEMPRE VISIBLE ==================== -->
  <?php
    // Tu partial con la nav inferior. Asegúrate de que tiene:
    // <nav id="bottomNav" class="nav"> ... </nav>
    // y que usa $active ('inicio'|'itinerario'|'perfil'|'asistente'|'mapa'...)
    include __DIR__ . '/partials/nav.php';
  ?>

  <!-- JS principal (si tienes acciones para los chips, CTA, etc.) -->
  <script src="assets/js/app.js" defer></script>
</body>
</html>
