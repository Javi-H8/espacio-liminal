<?php $csrf = htmlspecialchars(csrf_token(), ENT_QUOTES); ?>
<dialog id="sheetPassword" class="sheet sheet--bottom" aria-labelledby="passTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="passTitle" class="sheet__title">Cambiar contraseña</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <label class="field">
        <span class="field__label">Contraseña actual</span>
        <input class="field__input" type="password" name="old" placeholder="Insertar tu contraseña actual" required>
      </label>
      <label class="field">
        <span class="field__label">Nueva contraseña</span>
        <input class="field__input" type="password" name="new" placeholder="Insertar tu contraseña nueva" required>
      </label>
      <label class="field">
        <span class="field__label">Confirmar nueva contraseña</span>
        <input class="field__input" type="password" name="new2" placeholder="Confirmar contraseña" required>
      </label>

      <div class="sheet__row" style="display:flex;justify-content:space-between;gap:12px">
        <button type="button" class="link" data-dialog-open="sheetForgot">¿Necesitas ayuda?</button>
        <button type="button" class="link" data-dialog-open="sheetOTP">Olvidé mi contraseña</button>
      </div>

      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-password">GUARDAR</button>
    </footer>
  </form>
</dialog>
<!--
  sheet_password.php
  Hoja para cambiar la contraseña (desde perfil_editar.php)