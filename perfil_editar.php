<?php
/* ==========================================================================
   perfil_editar.php — Pantalla de edición de perfil
   Qué hago aquí (en cristiano y sin novela):
   - Cargo bootstrap (DB, helpers) + un “shim” de sesión de dev (hasta que metas login real).
   - Leo los datos del usuario LOGUEADO desde la BD (cero hardcode).
   - Pinto lista editable (nombre, email, password, género, teléfono) que abre hojas (sheets).
   - Dejo APP_BASE y BUILD para que el front construya rutas bien y la caché no nos trollee.
   ========================================================================== */

require_once __DIR__ . '/config/bootstrap.php';  // $mysqli, helpers (hash_pass, json_out, etc.)
require_once __DIR__ . '/config/session.php';    // shim de dev: $_SESSION['user_id']=1 si no hay login

header('Content-Type: text/html; charset=utf-8');

/* Cache busting sencillito:
   - Si existe define('BUILD','20250927'), la uso.
   - Si no, YYYYmmdd para que el navegador refresque en dev sin volverse loco. */
$build = defined('BUILD') ? BUILD : date('Ymd');

/* ID del usuario logueado (cuando tengas auth real, esto no cambia) */
$userId = auth_user_id();

/* === Usuario desde BD (si no hay sesión, pinto cortesía y no rompo nada) ===
   Campos esperados en users: name, email, gender, phone, locale, preferred_category, avatar_url */
$user = [
  "name"     => "Invitado",
  "email"    => "",
  "gender"   => "Sin especificar",
  "phone"    => "",
  "locale"   => "es",
  "category" => "todos",
  "avatar"   => "assets/img/avatar.jpg", // fallback si no hay avatar_url en BD
];
if ($userId) {
  $stmt = $mysqli->prepare(
    "SELECT
        COALESCE(name,''), COALESCE(email,''), COALESCE(gender,'Sin especificar'),
        COALESCE(phone,''), COALESCE(locale,'es'), COALESCE(preferred_category,'todos'),
        COALESCE(avatar_url,'')
     FROM users WHERE id=? LIMIT 1"
  );
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
      $stmt->bind_result($name,$email,$gender,$phone,$locale,$cat,$avatar);
      if ($stmt->fetch()) {
        if ($name   !== '') $user['name']   = $name;
        if ($email  !== '') $user['email']  = $email;
        if ($gender !== '') $user['gender'] = $gender;
        if ($phone  !== '') $user['phone']  = $phone;
        $user['locale']   = $locale ?: 'es';
        $user['category'] = $cat    ?: 'todos';
        if ($avatar !== '') $user['avatar'] = $avatar;
      }
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar perfil · Espacio Liminal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0f0f0f">

  <!-- APP_BASE para que app.js construya URLs bien aunque mueva la app de carpeta -->
  <script>
    window.APP_BASE = "/espacio-liminal/";               // cambia si despliegas en otra ruta
    window.BUILD    = "<?=htmlspecialchars($build)?>";    // por si quieres comparar versiones en el front
  </script>

  <!-- CORE -->
  <link rel="stylesheet" href="assets/css/core/variables-base.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/core/utilities.css?v=<?=htmlspecialchars($build)?>">

  <!-- LAYOUT -->
  <link rel="stylesheet" href="assets/css/layout/topbar.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/layout/blocks.css?v=<?=htmlspecialchars($build)?>">

  <!-- COMPONENTS -->
  <link rel="stylesheet" href="assets/css/components/buttons-links.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/forms.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/sheets.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/profile-and-misc.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/components/nav.css?v=<?=htmlspecialchars($build)?>">

  <!-- PÁGINA -->
  <link rel="stylesheet" href="assets/css/pages/profile.css?v=<?=htmlspecialchars($build)?>">

  <!-- RESPONSIVE -->
  <link rel="stylesheet" href="assets/css/responsive/tablet.css?v=<?=htmlspecialchars($build)?>">
  <link rel="stylesheet" href="assets/css/responsive/desktop.css?v=<?=htmlspecialchars($build)?>">

  <!-- JS general (abre/cierra sheets, OTP, acciones de perfil…) -->
  <script defer src="assets/js/app.js?v=<?=htmlspecialchars($build)?>"></script>
</head>
<body>
  <!-- Topbar con “volver” (uso la ruta base para no romper si cambio de carpeta) -->
  <header class="topbar" role="banner">
    <a class="back" href="/espacio-liminal/" aria-label="Volver">←</a>
    <h1>Editar perfil</h1><span aria-hidden="true"></span>
  </header>

  <!-- “has-sticky-nav” por si en desktop la nav sube bajo la topbar -->
  <main class="container container--page has-sticky-nav" role="main">
    <!-- Cabecera con avatar y CTA de cambiar foto -->
    <section class="profile-head">
      <div class="avatar-wrap">
        <img src="<?=htmlspecialchars($user['avatar'])?>" alt="avatar" decoding="async" loading="lazy">
        <span class="badge">1</span> <!-- si luego quieres notis o nivel, aquí encaja -->
      </div>
      <button class="chip" id="btnChangePhoto" type="button">CAMBIAR&nbsp;&nbsp;FOTO</button>
    </section>

    <!-- Lista editable (cada fila abre su sheet correspondiente) -->
    <ul class="list">
      <li>
        <span class="label">Nombre</span>
        <span class="value js-name"><?=htmlspecialchars($user["name"])?></span>
        <button class="edit" type="button" data-dialog-open="sheetName" aria-label="Editar nombre">✎</button>
      </li>

      <li>
        <span class="label">Email</span>
        <span class="value js-email"><?=htmlspecialchars($user["email"])?></span>
        <button class="edit" type="button" data-dialog-open="sheetEmail" aria-label="Editar email">✎</button>
      </li>

      <li>
        <span class="label">Contraseña</span>
        <span class="value">********</span>
        <button class="edit" type="button" data-dialog-open="sheetPassword" aria-label="Cambiar contraseña">✎</button>
      </li>

      <li>
        <span class="label">Género</span>
        <span class="value js-gender"><?=htmlspecialchars($user["gender"])?></span>
        <button class="edit" type="button" data-dialog-open="sheetGender" aria-label="Seleccionar género">✎</button>
      </li>

      <li>
        <span class="label">Número</span>
        <span class="value js-phone"><?=htmlspecialchars($user["phone"])?></span>
        <button class="edit" type="button" data-dialog-open="sheetPhone" aria-label="Editar teléfono">✎</button>
      </li>
    </ul>
    
    <!-- Enlaces y acciones varias -->
    <div class="links">
      <!-- Forgot password → abre sheetForgot -->
      <button class="link" type="button" data-dialog-open="sheetForgot">¡Olvidé mi contraseña!</button>
    </div>

    <div class="actions">
      <!-- Logout → abre sheetLogout (confirmación antes de salir) -->
      <button class="btn btn-outline" type="button" data-dialog-open="sheetLogout">Cerrar sesión  ➝</button>
    </div>

    <div class="links mt">
      <a class="link" href="/espacio-liminal/politicas.php">Política y privacidad</a>
    </div>
  </main>

  <?php @include __DIR__ . "/partials/nav.php"; /* include en 1 línea: nav inferior (mobile) / sticky (desktop) */ ?>

  <?php
    /* Sheets (bottom sheets):
       - Las incluyo “si existen” para no llenar de warnings en desarrollo.
       - Los IDs dentro de cada archivo deben coincidir EXACTO con lo que abro arriba:
         sheetName, sheetEmail, sheetEmailOTP, sheetPhone, sheetGender,
         y las clásicas: sheetCategoria, sheetIdioma, sheetPassword, sheetForgot, sheetOTP, sheetLogout
    */
    foreach ([
      // ya existentes:
      'sheet_categoria','sheet_idioma','sheet_password','sheet_forgot','sheet_otp','sheet_logout',
      // nuevos:
      'sheet_name','sheet_email','sheet_email_otp','sheet_phone','sheet_gender'
    ] as $s) { $p = __DIR__ . "/sheets/$s.php"; if (file_exists($p)) include $p; }
  ?>  
</body>
</html>
