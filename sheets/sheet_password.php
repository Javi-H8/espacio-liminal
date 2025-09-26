<dialog id='sheetPassword' class='sheet'>
  <div class='sheet-handle'></div>
  <div class='sheet-head'><h3>Cambiar contraseña</h3><button class='sheet-close' data-close>✕</button></div>
  <form class='form' id='formPassword'>
    <label>Contraseña actual<input name='current' type='password' placeholder='Inserta tu contraseña actual'></label>
    <label>Nueva contraseña<input name='new' type='password' placeholder='Inserta tu contraseña nueva'></label>
    <label>Confirmar nueva contraseña<input name='confirm' type='password' placeholder='Confirma tu contraseña nueva'></label>
    <div class='row between'>
      <button type='button' class='link-inline' data-open='#sheetForgot'>¿Necesitas ayuda?</button>
      <button type='button' class='link-inline' data-open='#sheetForgot'>Olvidé mi contraseña</button>
    </div>
    <button class='btn btn-accent w-100' type='submit'>GUARDAR</button>
  </form>
</dialog>
