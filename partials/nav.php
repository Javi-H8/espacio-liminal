<?php
// establece $active en cada página antes de incluir este archivo: 'inicio'|'itinerario'|'perfil'|'asistente'|'mapa'
$active = $active ?? '';
?>
<nav class="nav" role="navigation" aria-label="Navegación principal">
  <a class="nav__item <?= $active==='inicio'?'is-active':'' ?>" href="/espacio-liminal/">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3 10v10h6v-6h6v6h6V10z"/></svg>
    <span>Inicio</span>
    <i class="nav__indicator"></i>
  </a>

  <a class="nav__item <?= $active==='itinerario'?'is-active':'' ?>" href="#">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v2H5a2 2 0 0 0-2 2v2h18V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3H7Zm14 7H3v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9Zm-5 3h2v2h-2v-2Z"/></svg>
    <span>Itinerario</span>
    <i class="nav__indicator"></i>
  </a>

  <a class="nav__item <?= $active==='perfil'?'is-active':'' ?>" href="/espacio-liminal/perfil.php">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>
    <span>Perfil</span>
    <i class="nav__indicator"></i>
  </a>

  <a class="nav__item <?= $active==='asistente'?'is-active':'' ?>" href="#">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-9 9c0 4.971 4.029 9 9 9s9-4.029 9-9a9 9 0 0 0-9-9Zm1 4v5h4v2h-6V7h2Z"/></svg>
    <span>Asistente</span>
    <i class="nav__indicator"></i>
  </a>

  <a class="nav__item <?= $active==='mapa'?'is-active':'' ?>" href="#">
    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm7.93 9h-3.09a15.7 15.7 0 0 0-1.08-5.03A8.01 8.01 0 0 1 19.93 11ZM8.24 6.97A15.7 15.7 0 0 0 7.16 11H4.07a8.01 8.01 0 0 1 4.17-4.03ZM7.16 13a15.7 15.7 0 0 0 1.08 5.03A8.01 8.01 0 0 1 4.07 13Zm8.6 0h3.09a8.01 8.01 0 0 1-4.17 4.03A15.7 15.7 0 0 0 15.76 13ZM9 13h6a13.7 13.7 0 0 1-1.18 4H10.18A13.7 13.7 0 0 1 9 13Zm0-2a13.7 13.7 0 0 1 1.18-4h3.64A13.7 13.7 0 0 1 15 11H9Z"/></svg>
    <span>Mapa</span>
    <i class="nav__indicator"></i>
  </a>
</nav>
