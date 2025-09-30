<?php
/* ========================================================================
   NAV INFERIOR — versión “blindada” para que las rutas no me den guerra
   --------------------------------------------------------------------
   Cosas importantes que hago aquí (a mi rollo de siempre):
   1) Si por lo que sea incluyen este partial sin pasar por bootstrap.php,
      yo mismo me traigo el bootstrap (que a su vez carga urls.php) para
      tener las funciones url(), asset(), redirect(), etc. y no petar.
   2) Mantengo la API de $active para marcar la pestaña activa:
      valores posibles: 'inicio' | 'itinerario' | 'perfil' | 'asistente' | 'mapa'
      (si no me lo pasas, yo lo dejo vacío y no me enfado).
   ======================================================================== */

if (!function_exists('url')) {
  //     Cinturón y tirantes: si aún no existe url(), me traigo el bootstrap
  //    (que a su vez carga .env, urls.php, session, etc.)
  require_once __DIR__ . '/../config/bootstrap.php';
}

// si no me pasas $active, yo lo dejo vacío para no romper nada
$active = $active ?? '';
?>
<!--
  NAV INFERIOR
  - id="bottomNav" para engancharlo desde JS (autohide, sombras, etc.)
  - Mantengo tus clases y estructura; añado "nav--autohide" por si quieres estilos de ocultación.
  - role/aria para accesibilidad decente (que luego Google nos mira feo).
-->
<nav id="bottomNav" class="nav nav--autohide" role="navigation" aria-label="Navegación principal">
  <!-- INICIO -->
  <a class="nav__item <?= $active==='inicio' ? 'is-active' : '' ?>" href="<?= url('') ?>">
    <!-- icono home, simple y al grano -->
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3 10v10h6v-6h6v6h6V10z"/></svg>
    <span>Inicio</span>
    <i class="nav__indicator"></i>
  </a>

  <!-- ITINERARIO (cuando tengas la página real, ya la tienes enlazada aquí) -->
  <a class="nav__item <?= $active==='itinerario' ? 'is-active' : '' ?>" href="<?= url('itinerario.php') ?>">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v2H5a2 2 0 0 0-2 2v2h18V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3H7Zm14 7H3v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9Zm-5 3h2v2h-2v-2Z"/></svg>
    <span>Itinerario</span>
    <i class="nav__indicator"></i>
  </a>

  <!-- PERFIL -->
  <a class="nav__item <?= $active==='perfil' ? 'is-active' : '' ?>" href="<?= url('perfil.php') ?>">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>
    <span>Perfil</span>
    <i class="nav__indicator"></i>
  </a>

  <!-- ASISTENTE -->
  <a class="nav__item <?= $active==='asistente' ? 'is-active' : '' ?>" href="<?= url('asistente.php') ?>">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-9 9c0 4.971 4.029 9 9 9s9-4.029 9-9a9 9 0 0 0-9-9Zm1 4v5h4v2h-6V7h2Z"/></svg>
    <span>Asistente</span>
    <i class="nav__indicator"></i>
  </a>

  <!-- MAPA -->
  <a class="nav__item <?= $active==='mapa' ? 'is-active' : '' ?>" href="<?= url('mapa.php') ?>">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm7.93 9h-3.09a15.7 15.7 0 0 0-1.08-5.03A8.01 8.01 0 0 1 19.93 11ZM8.24 6.97A15.7 15.7 0 0 0 7.16 11H4.07a8.01 8.01 0 0 1 4.17-4.03ZM7.16 13a15.7 15.7 0 0 0 1.08 5.03A8.01 8.01 0 0 1 4.07 13Zm8.6 0h3.09a8.01 8.01 0 0 1-4.17 4.03A15.7 15.7 0 0 0 15.76 13ZM9 13h6a13.7 13.7 0 0 1-1.18 4H10.18A13.7 13.7 0 0 1 9 13Zm0-2a13.7 13.7 0 0 1 1.18-4h3.64A13.7 13.7 0 0 1 15 11H9Z"/></svg>
    <span>Mapa</span>
    <i class="nav__indicator"></i>
  </a>
</nav>
