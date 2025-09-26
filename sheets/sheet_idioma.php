<?php
// Hoja: cambiar idioma (banderita + radio a la derecha)
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES);
?>
<dialog id="sheetIdioma" class="sheet sheet--bottom" aria-labelledby="langTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="langTitle" class="sheet__title">Seleccionar idioma</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <fieldset class="group">
        <legend class="group__legend sr-only">Idiomas disponibles</legend>
        <ul class="radio-list" role="radiogroup" aria-labelledby="langTitle">
          <li><label class="radio-row"><span class="radio-row__txt">🇪🇸 Español</span><input type="radio" name="lang" value="es"><span class="radio-row__mark"></span></label></li>
          <li><label class="radio-row"><span class="radio-row__txt">🇬🇧 Inglés</span><input type="radio" name="lang" value="en"><span class="radio-row__mark"></span></label></li>
          <li><label class="radio-row"><span class="radio-row__txt">🇫🇷 Francés</span><input type="radio" name="lang" value="fr"><span class="radio-row__mark"></span></label></li>
          <li><label class="radio-row"><span class="radio-row__txt">🇮🇹 Italiano</span><input type="radio" name="lang" value="it"><span class="radio-row__mark"></span></label></li>
        </ul>
      </fieldset>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-lang">GUARDAR</button>
    </footer>
  </form>
</dialog>
