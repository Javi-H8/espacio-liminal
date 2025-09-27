<?php $csrf = function_exists('csrf_token') ? htmlspecialchars(csrf_token(),ENT_QUOTES) : ''; ?>
<dialog id="sheetEmail" class="sheet sheet--bottom" aria-labelledby="emailTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>
    <header class="sheet__head">
      <h2 id="emailTitle" class="sheet__title">Cambiar email</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>
    <div class="sheet__body">
      <label class="field">
        <span class="field__label">Nuevo email</span>
        <input class="field__input" name="email" type="email" placeholder="nombre@dominio.com" required>
      </label>
      <p class="muted">Se enviará un código de 4 dígitos para confirmar el cambio.</p>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>
    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="request-email-otp">ENVIAR CÓDIGO</button>
    </footer>
  </form>
</dialog>
