<dialog id="sheetLogout" class="sheet sheet--bottom" aria-labelledby="logoutTitle">
  <form method="dialog" class="sheet__panel">
    <div class="sheet__handle" aria-hidden="true"></div>

    <header class="sheet__head">
      <h2 id="logoutTitle" class="sheet__title">Cerrar sesión</h2>
      <button type="button" class="sheet__close" data-dialog-close aria-label="Cerrar">✕</button>
    </header>

    <div class="sheet__body">
      <p>La información del perfil queda guardada para un inicio más fácil cuando vuelvas. ¡Hasta pronto!</p>
    </div>

    <footer class="sheet__foot">
      <button class="btn btn--danger btn--xl" data-action="logout">CERRAR&nbsp;&nbsp;SESIÓN</button>
    </footer>
  </form>
</dialog>
<!--
  sheet_logout.php
  Hoja para cerrar sesión (desde perfil_editar.php)