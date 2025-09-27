<?php $csrf = function_exists('csrf_token') ? htmlspecialchars(csrf_token(),ENT_QUOTES) : ''; ?>
<dialog id="sheetName" class="sheet sheet--bottom" aria-labelledby="nameTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>
    <header class="sheet__head">
      <h2 id="nameTitle" class="sheet__title">Cambiar nombre</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>
    <div class="sheet__body">
      <label class="field">
        <span class="field__label">Nombre</span>
        <input class="field__input" name="name" type="text" placeholder="Tu nombre" required>
      </label>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>
    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-name">GUARDAR</button>
    </footer>
  </form>
</dialog>
