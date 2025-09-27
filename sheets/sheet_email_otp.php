<?php $csrf = function_exists('csrf_token') ? htmlspecialchars(csrf_token(),ENT_QUOTES) : ''; ?>
<dialog id="sheetEmailOTP" class="sheet sheet--bottom" aria-labelledby="emailOtpTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>
    <header class="sheet__head">
      <h2 id="emailOtpTitle" class="sheet__title">Código de verificación</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>
    <div class="sheet__body">
      <p class="muted">Introduce el código de 4 dígitos que te enviamos.</p>
      <div class="otp" role="group" aria-label="Código de verificación">
        <input class="otp__digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1">
        <input class="otp__digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1">
        <input class="otp__digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1">
        <input class="otp__digit" inputmode="numeric" autocomplete="one-time-code" maxlength="1">
      </div>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>
    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="confirm-email-otp">GUARDAR</button>
    </footer>
  </form>
</dialog>
