<?php
declare(strict_types=1);

/* =========================================================================
   REGISTER — crear cuenta sin romper nada y sin rutas fijas (prometido)
   -----------------------------------------------------------------
   - Cargo bootstrap y sesión (url()/asset()/redirect(), DB, CSRF… todo dentro).
   - Si ya estoy logueado, me voy a home con redirect('') (adiós /espacio-liminal/ a pelo).
   - Pinto el form con:
       * CSS por asset() (cache-busting y rutas correctas)
       * Front que llama a la API por fetch a URL absoluta (montada en PHP)
       * Fallback sin JS enviando POST clásico a la misma API
   - UX: mensajes inline (evito alert feo), desactivo botón mientras envío.
   ========================================================================= */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

// Si ya estoy dentro, pa casa
if (auth_user_id()) { redirect(''); }

// CSRF para proteger el POST (tanto JSON como form clásico)
$csrf = csrf_token();

// URLs que paso al front para no hardcodear nada
$HOME_URL       = url('');
$API_REGISTER   = url('api/auth/register.php');
$LOGIN_URL      = url('login.php');

// (Opcional) si tu API devuelve redirect en el JSON, el front la respetará.
// Si no, a HOME_URL y listo.
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear cuenta · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- =========================================================
       CSS: SIEMPRE con asset() para rutas y caché OK
       ========================================================= -->
  <link rel="stylesheet" href="<?= asset('assets/css/core/variables-base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/reset.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/typography.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/utilities.css') ?>">

  <link rel="stylesheet" href="<?= asset('assets/css/components/forms.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/buttons-links.css') ?>">

  <link rel="stylesheet" href="<?= asset('assets/css/pages/auth.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/responsive/tablet.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/responsive/desktop.css') ?>">

  <!-- =========================================================
       Bootstrap de rutas JS (para no escribir “/espacio-liminal/” nunca más)
       ========================================================= -->
  <script>
    window.APP_BASE     = "<?= rtrim(url(''), '/') ?>/";
    window.HOME_URL     = "<?= htmlspecialchars($HOME_URL, ENT_QUOTES) ?>";
    window.API_REGISTER = "<?= htmlspecialchars($API_REGISTER, ENT_QUOTES) ?>";
    window.LOGIN_URL    = "<?= htmlspecialchars($LOGIN_URL, ENT_QUOTES) ?>";
    window.u = function (p=""){ return window.APP_BASE + String(p).replace(/^\//,''); };
  </script>
  <style>
    /* Un par de toques UX para que quede fino */
    .btn[disabled]{ opacity:.6; cursor:progress; }
    .auth .msg-error{ color:#ff6b6b; margin-top:.5rem; min-height:1.25rem; }
    .auth .msg-ok{ color:#4cd964; margin-top:.5rem; min-height:1.25rem; }
    .hint{ font-size:.9rem; opacity:.8; margin-top:.25rem; }
  </style>
</head>
<body class="theme-dark">

  <main class="auth" role="main">
    <section class="card" aria-labelledby="registerTitle">
      <h1 id="registerTitle">Crear cuenta</h1>

      <!-- =========================================================
           Fallback sin JS (por si alguien va en modo “no-script”)
           - action directo a la API (misma URL que usa fetch)
           - Si tu API SOLO acepta JSON, crea api/auth/register_post.php
             que lea POST clásico y delegue en la lógica.
           ========================================================= -->
      <noscript>
        <form method="post" action="<?= htmlspecialchars($API_REGISTER) ?>" class="row" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

          <label>
            <span>Nombre</span>
            <input type="text" name="name" required minlength="2" maxlength="100" placeholder="Tu nombre">
          </label>

          <label>
            <span>Email</span>
            <input type="email" name="email" required placeholder="tucorreo@ejemplo.com" autocomplete="email">
          </label>

          <label>
            <span>Contraseña</span>
            <input type="password" name="pass" required minlength="8" placeholder="••••••••"
                   autocomplete="new-password">
            <small class="hint">Mínimo 8 caracteres. Mejor si metes mayús/minús/números.</small>
          </label>

          <label style="display:flex;gap:8px;align-items:center">
            <input type="checkbox" name="remember" value="1">
            <span>Mantener sesión iniciada</span>
          </label>

          <div class="actions">
            <button class="btn btn--primary" type="submit">Crear cuenta</button>
            <a class="btn btn--ghost" href="<?= htmlspecialchars($LOGIN_URL) ?>">Ya tengo cuenta</a>
          </div>
        </form>
      </noscript>

      <!-- =========================================================
           Form normal (JS) — envío por fetch JSON a la API
           - novalidate para usar mi validación (si prefieres la del navegador, quítalo)
           ========================================================= -->
      <form id="formRegister" class="row" autocomplete="on" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label>
          <span>Nombre</span>
          <input type="text" name="name" required minlength="2" maxlength="100" placeholder="Tu nombre">
        </label>

        <label>
          <span>Email</span>
          <input type="email" name="email" required placeholder="tucorreo@ejemplo.com" autocomplete="email">
        </label>

        <label>
          <span>Contraseña</span>
          <input type="password" name="pass" required minlength="8" placeholder="••••••••"
                 autocomplete="new-password">
          <small class="hint">Mínimo 8 caracteres. Mejor con mayúsculas, minúsculas y números.</small>
        </label>

        <label style="display:flex;gap:8px;align-items:center">
          <input type="checkbox" name="remember" value="1">
          <span>Mantener sesión iniciada</span>
        </label>

        <div class="actions">
          <button class="btn btn--primary" type="submit">Crear cuenta</button>
          <a class="btn btn--ghost" href="<?= htmlspecialchars($LOGIN_URL) ?>">Ya tengo cuenta</a>
        </div>

        <!-- Mensajera bonita (en vez de alert) -->
        <p id="regMsg" class="msg-error" role="alert" aria-live="polite"></p>
      </form>
    </section>
  </main>

  <script>
  // Registro por fetch: payload JSON, CSRF y sin rutas fijas
  document.addEventListener('DOMContentLoaded', () => {
    const form   = document.getElementById('formRegister');
    const btn    = form?.querySelector('button[type="submit"]');
    const msgBox = document.getElementById('regMsg');

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
        name:     String(fd.get('name')  || '').trim(),
        email:    String(fd.get('email') || '').trim(),
        pass:     String(fd.get('pass')  || ''),
        remember: !!fd.get('remember'),
        csrf:     String(fd.get('csrf')  || '')
      };

      // Validación front básica (lo justo para no hacer roundtrips tontos)
      if (!payload.name || payload.name.length < 2) {
        showMsg('El nombre es demasiado corto'); return;
      }
      if (!payload.email || !/^\S+@\S+\.\S+$/.test(payload.email)) {
        showMsg('El email no tiene buena pinta'); return;
      }
      if (!payload.pass || payload.pass.length < 8) {
        showMsg('La contraseña debe tener al menos 8 caracteres'); return;
      }

      try {
        btn && (btn.disabled = true);

        const res = await fetch(window.API_REGISTER, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });

        // Intento parsear JSON aunque venga 4xx/5xx
        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data || data.ok === false) {
          showMsg(data?.error || 'No se pudo crear la cuenta');
          return;
        }

        // Registro OK → la API puede dar redirect; si no, voy a HOME_URL
        const go = (data.redirect && typeof data.redirect === 'string') ? data.redirect : window.HOME_URL;
        showMsg('¡Cuenta creada! Te estamos llevando dentro…', true);
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
