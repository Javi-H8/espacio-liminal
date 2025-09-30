<?php
/**
 * PERFIL (modo web con nav inferior) — sin romper nada del backend ni del JS, palabrita :)
 * - Sigo con tu flujo: bootstrap, sesión, CSRF, ENUMs, etc.
 * - Aquí SOLO meto la barra de navegación de abajo y el “aire” para que no tape nada.
 */
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';
$active = 'inicio'; // o 'perfil', 'mapa', etc. según la página

$uid = auth_user_id();
if (!$uid) { redirect('login.php'); }

// Me traigo mis datos (igual que antes)
$u = db_exec(
  "SELECT name,email,COALESCE(phone,'') phone, gender, locale,
          preferred_category, COALESCE(avatar_url,'') avatar_url
   FROM users WHERE id=? LIMIT 1", 'i', [$uid]
)[0] ?? null;

if (!$u) { http_response_code(500); echo "No encuentro tu usuario (id $uid)"; exit; }

$csrf = csrf_token();

// Mapas para mostrar etiqueta bonita (la BBDD guarda el ENUM real)
$GENDER_LABEL = [
  'male'         => 'Masculino',
  'female'       => 'Femenino',
  'unspecified'  => 'Sin especificar',
  'other'        => 'Otro',
];
$CATEGORY_LABEL = [
  'naturaleza' => 'Naturaleza',
  'ocio'       => 'Ocio',
  'aventura'   => 'Aventura',
  'cultural'   => 'Cultural',
  'todos'      => 'Todos',
  'custom'     => 'Personalizada',
];

/* ====================== IMPORTANTE: activo de la nav ======================
   - Yo aquí marco 'perfil' para que tu nav pinte ese icono activo.
   - Ojo: esto lo uso abajo cuando hago el include del nav.
=========================================================================== */
$active = 'perfil';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Tus CSS base -->
  <link rel="stylesheet" href="<?= asset('assets/css/core/variables-base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/reset.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/typography.css') ?>">

  <!-- Estilos de perfil (los tuyos) -->
  <link rel="stylesheet" href="<?= asset('assets/css/pages/profile.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/pages/profile.mobile.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/pages/profile.web.css') ?>">

  <!--  Aquí engancho el CSS de la barra inferior (tu componente) -->
  <link rel="stylesheet" href="<?= asset('assets/css/components/nav.css') ?>">

  <!-- Tu JS de perfil (intacto) -->
  <script src="<?= asset('assets/js/perfil_editar.js') ?>" defer></script>

  <!-- (Opcional) Autohide del nav: se oculta al bajar y aparece al subir -->
  <script defer>
  // Mini script para esconder/mostrar la nav al hacer scroll. Si no te mola, lo borras y listo.
  document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('bottomNav');
    if (!nav) return;
    let lastY = window.scrollY;
    let ticking = false;

    const onScroll = () => {
      const y = window.scrollY;
      // Si bajo mucho → escondo; si subo → enseño
      if (y > lastY + 8) { nav.classList.add('is-hidden'); } 
      else if (y < lastY - 8) { nav.classList.remove('is-hidden'); }
      lastY = y;
      ticking = false;
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(onScroll);
        ticking = true;
      }
    }, { passive: true });
  });
  </script>
</head>
<body class="theme-dark">

  <!-- Cabecera “web” de la página (simple, sin tocar tu topbar global) -->
  <header class="pw-head">
    <div class="pw-head__inner">
      <h1 class="pw-title">Editar perfil</h1>
      <nav class="pw-bread"><a href="<?= url('') ?>">Inicio</a> / <span>Perfil</span></nav>
    </div>
  </header>

  <!-- Shell de 2 columnas (desktop) / 1 columna (móvil)
       NOTA: le pongo has-sticky-nav para dar aire abajo (que la nav no tape nada) -->
  <main class="pw-shell has-sticky-nav" role="main">

    <!-- Columna izquierda: avatar -->
    <aside class="pw-left">
      <section class="pw-card pw-card--avatar">
        <div class="pw-avatar">
          <div class="pw-avatar__ring">
            <?php
              $rawAvatar = $u['avatar_url'] ?: 'assets/img/avatar-default.png';
              $avatarSrc = preg_match('~^https?://~i', $rawAvatar) ? $rawAvatar : asset($rawAvatar);
              ?>
              <img id="avatarPreview" src="<?= htmlspecialchars($avatarSrc) ?>" alt="Tu avatar">
          </div>
        </div>

        <form data-avatar class="pw-avatar__form" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="pw-file">
          <button class="pw-btn pw-btn--lime" type="submit">CAMBIAR  FOTO</button>
          <span class="pw-msg" aria-live="polite"></span>
        </form>

        <p class="pw-note">JPG/PNG/WebP · Máx 2&nbsp;MB. Se recorta en círculo.</p>
      </section>
    </aside>

    <!-- Columna derecha: tus campos editables (tal cual los teníamos) -->
    <section class="pw-right">

      <div class="pw-group">
        <h2 class="pw-group__title">Datos de la cuenta</h2>

        <!-- Nombre -->
        <div class="pw-item profile-row" data-field="name">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Nombre</div>
              <div class="pw-value" data-value="name"><?= htmlspecialchars($u['name']) ?></div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="name" aria-label="Editar nombre">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>
          <div class="edit-pane pw-pane">
            <form data-submit="name" class="pw-form-inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" required minlength="2" maxlength="120" class="pw-input">
              <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
              <span class="pw-msg msg" aria-live="polite"></span>
            </form>
          </div>
        </div>

        <!-- Email (solo lectura) -->
        <div class="pw-item">
          <div class="pw-item__row">
            <div class="pw-item__text">
              <div class="pw-label">Email</div>
              <div class="pw-value"><?= htmlspecialchars($u['email']) ?></div>
            </div>
          </div>
        </div>

        <!-- Contraseña -->
        <div class="pw-item profile-row" data-field="password">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Contraseña</div>
              <div class="pw-value">********</div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="password" aria-label="Cambiar contraseña">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>

          <div class="edit-pane pw-pane">
            <form data-submit="password" class="pw-form-grid">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="password" name="current" placeholder="Actual" class="pw-input" required>
              <input type="password" name="new"     placeholder="Nueva (mín. 8)" minlength="8" class="pw-input" required>
              <input type="password" name="new2"    placeholder="Repite nueva"   minlength="8" class="pw-input" required>
              <div class="pw-form__actions">
                <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
                <span class="pw-msg msg" aria-live="polite"></span>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="pw-group">
        <h2 class="pw-group__title">Preferencias</h2>

        <!-- Género -->
        <div class="pw-item profile-row" data-field="gender">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Género</div>
              <div class="pw-value" data-value="gender"><?= htmlspecialchars($GENDER_LABEL[$u['gender']] ?? 'Sin especificar') ?></div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="gender" aria-label="Editar género">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>
          <div class="edit-pane pw-pane">
            <form data-submit="gender" class="pw-form-inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <select name="gender" class="pw-select">
                <?php foreach (['male','female','unspecified','other'] as $gv): ?>
                  <option value="<?= $gv ?>" <?= $u['gender']===$gv?'selected':'' ?>>
                    <?= $GENDER_LABEL[$gv] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
              <span class="pw-msg msg" aria-live="polite"></span>
            </form>
          </div>
        </div>

        <!-- Teléfono -->
        <div class="pw-item profile-row" data-field="phone">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Número</div>
              <div class="pw-value" data-value="phone"><?= $u['phone'] ?: '—' ?></div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="phone" aria-label="Editar número">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>
          <div class="edit-pane pw-pane">
            <form data-submit="phone" class="pw-form-inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="tel" name="phone" value="<?= htmlspecialchars($u['phone']) ?>" maxlength="30"
                     class="pw-input" placeholder="+34 680 86 59 90">
              <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
              <span class="pw-msg msg" aria-live="polite"></span>
            </form>
          </div>
        </div>

        <!-- Idioma -->
        <div class="pw-item profile-row" data-field="locale">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Idioma</div>
              <div class="pw-value" data-value="locale"><?= strtoupper($u['locale']) ?></div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="locale" aria-label="Editar idioma">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>
          <div class="edit-pane pw-pane">
            <form data-submit="locale" class="pw-form-inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <select name="locale" class="pw-select">
                <?php
                  $langs = ['es'=>'Español','en'=>'English','fr'=>'Français','it'=>'Italiano'];
                  foreach ($langs as $code=>$label) {
                    $sel = ($u['locale']===$code)?' selected':'';
                    echo "<option value=\"$code\"$sel>$label</option>";
                  }
                ?>
              </select>
              <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
              <span class="pw-msg msg" aria-live="polite"></span>
            </form>
          </div>
        </div>

        <!-- Categoría -->
        <div class="pw-item profile-row" data-field="category">
          <div class="pw-item__row row-head">
            <div class="pw-item__text">
              <div class="pw-label">Categoría favorita</div>
              <div class="pw-value" data-value="category">
                <?= htmlspecialchars($CATEGORY_LABEL[$u['preferred_category']] ?? $u['preferred_category']) ?>
              </div>
            </div>
            <button type="button" class="pw-icon-btn" data-toggle="category" aria-label="Editar categoría">
              <svg viewBox="0 0 24 24" class="pw-i"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" fill="currentColor"/></svg>
            </button>
          </div>
          <div class="edit-pane pw-pane">
            <form data-submit="category" class="pw-form-inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <select name="preferred_category" class="pw-select">
                <?php
                  $catValues = ['naturaleza','ocio','aventura','cultural','todos','custom'];
                  foreach ($catValues as $cv) {
                    $sel = ($u['preferred_category']===$cv)?' selected':'';
                    $label = $CATEGORY_LABEL[$cv] ?? $cv;
                    echo "<option value=\"$cv\"$sel>$label</option>";
                  }
                ?>
              </select>
              <button class="pw-btn pw-btn--primary" type="submit">Guardar</button>
              <span class="pw-msg msg" aria-live="polite"></span>
            </form>
          </div>
        </div>
      </div>

      <!-- Accióncitas al pie (por si quieres tenerlas también en la vista web) -->
      <div class="pw-actions">
        <a href="<?= url('auth_forgot.php') ?>" class="pw-link pw-link--lime">
          ¡Olvidé mi contraseña!
          <svg viewBox="0 0 24 24" class="pw-i pw-i--arrow"><path d="M8 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="<?= url('logout.php') ?>" class="pw-link">
          Cerrar sesión
          <svg viewBox="0 0 24 24" class="pw-i pw-i--arrow"><path d="M8 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>

    </section>
  </main>

  <?php
/* =========================================================================
   NAV INFERIOR (perfil editar) — versión blindada y comentada a fuego
   ---------------------------------------------------------------------
   - $active: por si alguien no lo ha seteado arriba, lo dejo en 'perfil' para
     que pinte la pestaña correcta y no me salte nada.
   - function_exists('url'): si todavía no cargaron el bootstrap, me lo traigo
     yo solito para tener url(), asset(), redirect(), etc. y no cascar.
   - require_once: así me aseguro de que la nav se incluye una única vez,
     aunque por despiste la llamen dos veces desde la misma vista.
   - __DIR__: ruta ABSOLUTA de sistema (no depende del dominio ni subcarpeta),
     por eso esto NUNCA se rompe al subir a producción.
   ========================================================================= */

$active = $active ?? 'perfil'; // por defecto, esta vista es “perfil”

// Cinturón y tirantes: si aún no existen los helpers de URL, cargo el bootstrap.
if (!function_exists('url')) {
  require_once __DIR__ . '/config/bootstrap.php';
}

// Y ahora sí, pincho la nav inferior una sola vez.
require_once __DIR__ . '/partials/nav.php';
  ?>
</body>
</html>
