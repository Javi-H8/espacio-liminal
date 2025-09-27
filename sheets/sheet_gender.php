<?php $csrf = function_exists('csrf_token') ? htmlspecialchars(csrf_token(),ENT_QUOTES) : ''; ?>
<dialog id="sheetGender" class="sheet sheet--bottom" aria-labelledby="genderTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>
    <header class="sheet__head">
      <h2 id="genderTitle" class="sheet__title">Seleccionar género</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>
    <div class="sheet__body">
      <fieldset class="radio-list">
        <label><input type="radio" name="gender" value="Masculino"> Masculino</label>
        <label><input type="radio" name="gender" value="Femenino"> Femenino</label>
        <label><input type="radio" name="gender" value="Sin especificar"> Sin especificar</label>
        <label><input type="radio" name="gender" value="Otro"> Otro</label>
      </fieldset>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>
    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-gender">GUARDAR</button>
    </footer>
  </form>
</dialog>
