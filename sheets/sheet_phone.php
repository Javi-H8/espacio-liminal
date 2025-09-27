<?php $csrf = function_exists('csrf_token') ? htmlspecialchars(csrf_token(),ENT_QUOTES) : ''; ?>
<dialog id="sheetPhone" class="sheet sheet--bottom" aria-labelledby="phoneTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>
    <header class="sheet__head">
      <h2 id="phoneTitle" class="sheet__title">Cambiar número</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>
    <div class="sheet__body">
      <label class="field">
        <span class="field__label">Número de teléfono</span>
        <input class="field__input" name="phone" type="tel" placeholder="+34 600 00 00 00" required>
      </label>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>
    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-phone">GUARDAR</button>
    </footer>
  </form>
</dialog>
