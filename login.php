<?php
declare(strict_types=1);

/* =========================================================================
   LOGIN — versión blindada de rutas y con algún mimo de UX/seguridad
   -----------------------------------------------------------------
   - Cargo bootstrap (helpers url()/asset()/redirect(), DB, sesión, etc.)
   - Si ya estoy logueado, redirijo a home con redirect('') (nada de rutas fijas).
   - Pinto el login con:
       * CSS/JS por asset() (cache-busting y rutas correctas)
       * JS que llama a la API con fetch a URL absoluta vía PHP (sin hardcode)
       * Fallback sin JS: action POST directo a la API del login
   ========================================================================= */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

// Si ya estoy dentro, pa casa (home real según dominio/subcarpeta)
if (auth_user_id()) { redirect(''); }

// CSRF para proteger el POST (tanto vía API/JSON como vía POST clásico)
$csrf = csrf_token();

// URLs que le paso al front para NO hardcodear nada
$HOME_URL      = url('');                      // home
$API_LOGIN_URL = url('api/auth/login.php');    // endpoint JSON/POST del login
$REGISTER_URL  = url('register.php');          // registro (si aún no existe, apunta a donde quieras)

// (Opcional) Si la API de login devuelve también redirección (r.redirect),
//     el front la respetará; si no, uso HOME_URL por defecto tras loguear.
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Entrar · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- =========================================================
       CSS: siempre con asset() para cache-busting y rutas OK
       ========================================================= -->
  <link rel="stylesheet" href="<?= asset('assets/css/core/variables-base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/utilities.css') ?>">

  <link rel="stylesheet" href="<?= asset('assets/css/components/forms.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/buttons-links.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/nav.css') ?>">

  <link rel="stylesheet" href="<?= asset('assets/css/pages/auth.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/responsive/tablet.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/responsive/desktop.css') ?>">

  <!-- =========================================================
       Bootstrap JS de rutas en el front (sin rutas fijas)
       ========================================================= -->
  <script>
    // Base absoluta del sitio (con dominio + subcarpeta), montada en PHP
    window.APP_BASE    = "<?= rtrim(url(''), '/') ?>/";
    // URLs que voy a usar en el script sin hardcodear
    window.HOME_URL    = "<?= htmlspecialchars($HOME_URL, ENT_QUOTES) ?>";
    window.API_LOGIN   = "<?= htmlspecialchars($API_LOGIN_URL, ENT_QUOTES) ?>";
    window.REGISTER_URL= "<?= htmlspecialchars($REGISTER_URL, ENT_QUOTES) ?>";

    // Helpercillo por si quiero construir rutas desde JS
    window.u = function(path=""){ return window.APP_BASE + String(path).replace(/^\//,''); };
  </script>
  <style>
    /* Mini detalle UX: spinner en el botón mientras envío */
    .btn[disabled] { opacity:.6; cursor:progress; }
    .auth .msg-error{ color:#ff6b6b; margin-top:.5rem; min-height:1.25rem; }
    .auth .msg-ok{ color:#4cd964; margin-top:.5rem; min-height:1.25rem; }
  </style>
</head>
<body class="theme-dark">

  <main class="auth" role="main">
    <section class="card" aria-labelledby="loginTitle">
      <h1 id="loginTitle">Inicia sesión</h1>

      <!-- =========================================================
           Fallback sin JS:
           - action directo a la API (misma URL que usa fetch)
           - target noscript para no romper si el user no tiene JS
           - Si tu API solo acepta JSON, puedes crear un pequeño
             api/auth/login_post.php que lea POST y delegue.
           ========================================================= -->
      <noscript>
        <form method="post" action="<?= htmlspecialchars($API_LOGIN_URL) ?>">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <label>
            <span>Email</span>
            <input type="email" name="email" required placeholder="tucorreo@ejemplo.com" autocomplete="email">
          </label>
          <label>
            <span>Contraseña</span>
            <input type="password" name="pass" required minlength="8" placeholder="••••••••"
                   autocomplete="current-password">
          </label>
          <label style="display:flex;gap:8px;align-items:center">
            <input type="checkbox" name="remember" value="1">
            <span>Recuérdame en este dispositivo</span>
          </label>
          <div class="actions">
            <button class="btn btn--primary" type="submit">Entrar</button>
            <a class="btn btn--ghost" href="<?= htmlspecialchars($REGISTER_URL) ?>">Crear cuenta</a>
          </div>
        </form>
      </noscript>

      <!-- =========================================================
           Form con JS (por fetch JSON a la API)
           - NO pongo action para que no se dispare si hay JS
           - autocomplete on (que la peña lo agradece)
           ========================================================= -->
      <form id="formLogin" class="row" autocomplete="on" novalidate>
        <!-- CSRF para evitar POST cross-site -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label>
          <span>Email</span>
          <input type="email" name="email" required placeholder="tucorreo@ejemplo.com" autocomplete="email">
        </label>

        <label>
          <span>Contraseña</span>
          <input type="password" name="pass" required minlength="8" placeholder="••••••••"
                 autocomplete="current-password">
        </label>

        <label style="display:flex;gap:8px;align-items:center">
          <input type="checkbox" name="remember" value="1">
          <span>Recuérdame en este dispositivo</span>
        </label>

        <div class="actions">
          <button class="btn btn--primary" type="submit">Entrar</button>
          <a class="btn btn--ghost" href="<?= htmlspecialchars($REGISTER_URL) ?>">Crear cuenta</a>
        </div>

        <!-- Zona de mensajes (error/ok) para no tirar de alert feo -->
        <p id="loginMsg" class="msg-error" role="alert" aria-live="polite"></p>
      </form>
    </section>
  </main>

  <script>
  // Envío a mi API (JSON), con CSRF, como Dios manda :)
  document.addEventListener('DOMContentLoaded', () => {
    const form   = document.getElementById('formLogin');
    const btn    = form?.querySelector('button[type="submit"]');
    const msgBox = document.getElementById('loginMsg');

    const showMsg = (text, ok=false) => {
      if (!msgBox) return;
      msgBox.textContent = text || '';
      msgBox.className = ok ? 'msg-ok' : 'msg-error';
    };

    form?.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      showMsg('');

      const fd = new FormData(form);
      const payload = {
        email:    String(fd.get('email') || '').trim(),
        pass:     String(fd.get('pass')  || ''),
        remember: !!fd.get('remember'),
        csrf:     String(fd.get('csrf')  || '')
      };

      // Validación rápida en front (por si acaso)
      if (!payload.email || !payload.pass) {
        showMsg('Faltan el correo o la contraseña');
        return;
      }

      try {
        btn && (btn.disabled = true);

        const res = await fetch(window.API_LOGIN, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });

        // Si el backend devuelve 4xx/5xx, intento sacar el JSON igualmente
        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data || data.ok === false) {
          showMsg(data?.error || 'No se pudo iniciar sesión');
          return;
        }

        // Login OK → redirijo: si la API dice a dónde ir, la sigo; si no, a HOME_URL
        const go = (data.redirect && typeof data.redirect === 'string') ? data.redirect : window.HOME_URL;
        showMsg('¡Dentro!', true);
        location.href = go;

      } catch (e) {
        showMsg('Error de red, reintenta en unos segundos');
      } finally {
        btn && (btn.disabled = false);
      }
    });
  });
  </script>
</body>
</html>
