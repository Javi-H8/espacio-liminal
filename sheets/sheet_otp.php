<dialog id="sheetOTP" class="sheet sheet--bottom" aria-labelledby="otpTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="otpTitle" class="sheet__title">Código de verificación</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <p class="muted">Se ha enviado un código de 4 dígitos a tu email.</p>
      <div class="otp" role="group" aria-label="Código de verificación">
        <input class="otp__digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Dígito 1">
        <input class="otp__digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Dígito 2">
        <input class="otp__digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Dígito 3">
        <input class="otp__digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Dígito 4">
      </div>
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="verify-code">GUARDAR</button>
    </footer>
  </form>
</dialog>
<!--
  sheet_otp.php
  Hoja para introducir el código OTP (desde sheet_password.php)