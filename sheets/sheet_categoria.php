<?php $csrf = htmlspecialchars(csrf_token(), ENT_QUOTES); ?>
<dialog id="sheetCategoria" class="sheet sheet--bottom" aria-labelledby="catTitle" aria-describedby="catDesc">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="catTitle" class="sheet__title">Seleccionar categoría</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <p id="catDesc" class="muted">¿Cuál es más acorde para mi viaje?</p>
      <ul class="radio-list" role="radiogroup" aria-labelledby="catTitle">
        <li><label class="radio-row"><span class="radio-row__txt">🌿 Naturaleza</span> <input type="radio" name="cat" value="naturaleza"><span class="radio-row__mark"></span></label></li>
        <li><label class="radio-row"><span class="radio-row__txt">🟠 Ocio</span>        <input type="radio" name="cat" value="ocio"><span class="radio-row__mark"></span></label></li>
        <li><label class="radio-row"><span class="radio-row__txt">🔵 Aventura</span>    <input type="radio" name="cat" value="aventura"><span class="radio-row__mark"></span></label></li>
        <li><label class="radio-row"><span class="radio-row__txt">🟣 Cultural</span>    <input type="radio" name="cat" value="cultural"><span class="radio-row__mark"></span></label></li>
        <li><label class="radio-row"><span class="radio-row__txt">⚪ Todos</span>       <input type="radio" name="cat" value="todos"><span class="radio-row__mark"></span></label></li>
        <li><label class="radio-row"><span class="radio-row__txt">🛠️ Selección personalizada</span><input type="radio" name="cat" value="custom"><span class="radio-row__mark"></span></label></li>
      </ul>
      <input type="hidden" name="csrf" value="<?=$csrf?>">
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--primary btn--xl" data-action="save-cat">GUARDAR</button>
    </footer>
  </form>
</dialog>
