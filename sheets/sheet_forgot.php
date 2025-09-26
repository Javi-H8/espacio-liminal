<?php $csrf = htmlspecialchars(csrf_token(), ENT_QUOTES); ?>
<dialog id="sheetForgot" class="sheet sheet--bottom" aria-labelledby="forgotTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="forgotTitle" class="sheet__title">Resetear contraseña</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <p class="muted">Introduce tu correo y te enviaremos un código.</p>
      <label class="field">
        <span class="field__label">Email</span>
        <input class="field__input" name="email" type="email" placeholder="tu@email.com" required>
      </label>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="reset-pass">GUARDAR</button>
    </footer>
  </form>
</dialog>
<!--
  sheet_forgot.php
  Hoja para resetear la contraseña (desde perfil_editar.php y sheet_password.php)