// Registro del SW con scope en /espacio-liminal/ y aviso de versión nueva
(() => {
  if (!('serviceWorker' in navigator)) return;

  const SCOPE = '/espacio-liminal/'; // si vas a raíz, cambia a '/'
  const SW_URL = SCOPE + 'pwa/service-worker.js?v=' + (window.BUILD || '');

  navigator.serviceWorker.register(SW_URL, { scope: SCOPE }).then(reg => {
    // Si hay un SW viejo esperando, se le pide saltar la espera
    if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });

    // Detectar instalación de un SW nuevo
    reg.addEventListener('updatefound', () => {
      const nw = reg.installing;
      if (!nw) return;
      nw.addEventListener('statechange', () => {
        if (nw.state === 'installed' && navigator.serviceWorker.controller) {
          // Aquí podrías mostrar un toast/badge “Nueva versión disponible”
          console.log('Nueva versión disponible. Recarga para actualizar.');
        }
      });
    });
  }).catch(console.error);

  // Cuando el controlador cambia (nuevo SW activo), puedes recargar
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    // location.reload(); // si prefieres recarga automática
  });

  // Mensajes entrantes del SW (por si en el futuro envías SKIP_WAITING)
  navigator.serviceWorker.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'READY_TO_UPDATE') {
      console.log('SW listo para actualizar');
    }
  });
})();
