<?php
declare(strict_types=1);

/* =========================================================================
   POLÍTICA DE PRIVACIDAD · Espacio Liminal
   -------------------------------------------------------------------------
   - Cargo bootstrap para tener url()/asset() y compañía.
   - Marco $active='perfil' para que la pestaña Perfil quede activa en la nav.
   - CUIDO las rutas: NADA de /espacio-liminal/ a pelo.
   - Contenido claro y con anclas internas para que el usuario navegue fácil.
   - Dejo un "aceptar" que por ahora guarda en localStorage y vuelve al perfil.
   ========================================================================= */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

$active = 'perfil'; // así en la nav inferior se marca Perfil (que es donde venía el usuario)

// Por si quieres marcar "aceptada" la política de forma simple sin tocar BD todavía:
// - Si un día lo quieres serio, hacemos /api/privacy/accept.php y guardamos en DB/fecha-ip.
$backUrl = url('perfil.php');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Política de Privacidad · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Core / components: evito rutas fijas y activo cache-busting via asset() -->
  <link rel="stylesheet" href="<?= asset('assets/css/core/variables-base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/base.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/core/utilities.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/buttons-links.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/forms.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/layout/topbar.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components/nav.css') ?>">

  <!-- CSS específico de esta página (lo creo abajo en /assets/css/pages/politicas.css) -->
  <link rel="stylesheet" href="<?= asset('assets/css/pages/politicas.css') ?>">
</head>
<body class="theme-dark">

  <!-- Topbar con botón de volver. Nada de rutas absolutas a mano, tiro de url('...'). -->
  <header class="topbar" role="banner">
    <a class="back" href="<?= $backUrl ?>" aria-label="Volver">←</a>
    <h1>Política de privacidad</h1>
    <span aria-hidden="true"></span>
  </header>

  <!-- Contenido principal -->
  <main class="container policy has-sticky-nav" role="main">
    <!-- Intro bonita, con badges de “última actualización” y “versión” -->
    <section class="card policy__card">
      <h2 class="policy__title">Tu privacidad es importante para nosotros</h2>
      <p class="policy__lead">
        En Espacio Liminal solo pedimos los datos justos para hacer que tu experiencia sea
        útil y sencilla. Aquí te explico qué recogemos, para qué lo usamos y cómo ejercer tus derechos.
      </p>
      <ul class="policy__meta" aria-label="Metadatos de la política">
        <li><strong>Última actualización:</strong> 30/09/2025</li>
        <li><strong>Versión:</strong> 1.0.0</li>
      </ul>

      <!-- Índice con anclas internas para ir al grano -->
      <nav class="policy__toc" aria-label="Índice de secciones">
        <a href="#datos-que-recogemos">Datos que recogemos</a>
        <a href="#para-que-usamos">Para qué usamos tus datos</a>
        <a href="#base-legal">Base legal</a>
        <a href="#cookies">Cookies</a>
        <a href="#terceros">Proveedores/terceros</a>
        <a href="#seguridad">Seguridad</a>
        <a href="#derechos">Tus derechos</a>
        <a href="#contacto">Contacto</a>
      </nav>
    </section>

    <!-- Datos que recogemos -->
    <section id="datos-que-recogemos" class="card policy__card">
      <h3>1) Datos que recogemos</h3>
      <ul class="policy__list">
        <li><strong>Cuenta:</strong> nombre, email y contraseña.</li>
        <li><strong>Preferencias:</strong> idioma, categoría favorita, ajustes de tema.</li>
        <li><strong>Contenido opcional:</strong> foto de perfil.</li>
      </ul>
      <p class="muted">
        Nunca pediremos datos que no necesitemos para el servicio. Si algo es opcional, lo marcamos como tal.
      </p>
    </section>

    <!-- Para qué usamos -->
    <section id="para-que-usamos" class="card policy__card">
      <h3>2) Para qué usamos tus datos</h3>
      <ul class="policy__list">
        <li>Crear y mantener tu cuenta, y personalizar tu experiencia.</li>
        <li>Mejorar la app (estadísticas agregadas y anónimas).</li>
        <li>Seguridad: detección de abusos y protección de acceso.</li>
        <li>Comunicaciones útiles.</li>
      </ul>
    </section>

    <!-- Base legal -->
    <section id="base-legal" class="card policy__card">
      <h3>3) Base legal</h3>
      <p>
        Tratamos tus datos conforme al RGPD en base a: (i) tu consentimiento (por ejemplo, al crear cuenta),
        (ii) la ejecución del contrato (prestación del servicio), y (iii) interés legítimo (mejoras y seguridad),
        siempre ponderando tu privacidad.
      </p>
    </section>

    <!-- Cookies -->
    <section id="cookies" class="card policy__card">
      <h3>4) Cookies</h3>
      <p>Usamos cookies técnicas para que tu sesión funcione y recordar ajustes (idioma, tema, etc.).</p>
      <p>No usamos cookies de terceros con fines publicitarios. Si añadimos analítica, te lo avisamos y pedimos consentimiento.</p>
    </section>

    <!-- Proveedores -->
    <section id="terceros" class="card policy__card">
      <h3>5) Proveedores y terceros</h3>
      <p>
        Podemos usar proveedores para alojamiento, envío de emails o análisis agregados. Todos ellos cumplen normativa aplicable
        y solo tratan datos para lo que les pedimos. Si operan fuera de la UE, aplicamos salvaguardas adecuadas.
      </p>
    </section>

    <!-- Seguridad -->
    <section id="seguridad" class="card policy__card">
      <h3>6) Seguridad</h3>
      <ul class="policy__list">
        <li>Contraseñas con hash Argon2id y políticas de acceso seguras.</li>
        <li>Comunicación cifrada cuando tu conexión es HTTPS.</li>
        <li>Medidas para prevenir abuso y accesos no autorizados.</li>
      </ul>
      <p class="muted">Aun así, ningún sistema es 100% infalible; si detectamos un incidente te informaremos según la ley.</p>
    </section>

    <!-- Derechos -->
    <section id="derechos" class="card policy__card">
      <h3>7) Tus derechos</h3>
      <p>Puedes ejercer acceso, rectificación, supresión, portabilidad y oposición/limitación del tratamiento.</p>
      <p>
        Escríbenos y te ayudamos con el proceso. Si no estás conforme, también puedes acudir a la autoridad de control.
      </p>
      <div class="policy__cta">
        <a class="btn btn--ghost" href="#contacto">Contactar</a>
      </div>
    </section>

    <!-- Contacto -->
    <section id="contacto" class="card policy__card">
      <h3>8) Contacto</h3>
      <p>Para cualquier duda o para ejercer tus derechos, contáctanos:</p>
      <ul class="policy__list">
        <li>Email: <a href="mailto:privacidad@espacio-liminal.com">privacidad@espacio-liminal.com</a></li>
      </ul>
      <div class="policy__actions">
        <!-- Botón principal: acepto y vuelvo al perfil -->
        <button id="btnAccept" class="btn btn--primary" type="button">Estoy de acuerdo</button>
        <!-- Secundario: volver sin aceptar (solo navegación) -->
        <a class="btn btn--ghost" href="<?= $backUrl ?>">Cancelar y volver</a>
      </div>
      <p class="muted policy__note">
        *Este botón guarda un “aceptado” en este dispositivo. Más adelante lo conecto a tu cuenta en la BD para que
        quede registrado con fecha y todo.
      </p>
    </section>

  </main>

  <!-- Nav inferior (mobile) / sticky (desktop). Incluyo con rutas de sistema; cero dramas. -->
  <?php require_once __DIR__ . '/partials/nav.php'; ?>

  <!-- JS mínimo para “Aceptar” ahora mismo (sin backend): guardo en localStorage y vuelvo al perfil) -->
  <script>
    document.getElementById('btnAccept')?.addEventListener('click', () => {
      try {
        localStorage.setItem('policy.accepted', new Date().toISOString());
      } catch (e) { /* si falla, pues tampoco pasa nada */ }
      location.href = "<?= $backUrl ?>";
    });
  </script>
</body>
</html>
