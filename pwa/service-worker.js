/* ===== Espacio Liminal · Service Worker =====
   Objetivo: cobertura de rutas bajo /espacio-liminal/, versiones por build,
   navegación offline amigable y estrategias simples por tipo de recurso.
*/

const BASE = '/espacio-liminal/';                  // subcarpeta del sitio
const SW_VERSION = 'v3-20250926';                  // súbelo en cada release
const STATIC_CACHE = `liminal-static-${SW_VERSION}`;
const RUNTIME_CACHE = `liminal-runtime-${SW_VERSION}`;

/* Lista mínima de ficheros que deben estar disponibles siempre,
   incluso sin red (shell básico + estilos principales). */
const STATIC_ASSETS = [
  BASE,                               // /espacio-liminal/ → home
  BASE + 'pwa/offline.html',
  BASE + 'assets/css/core/variables-base.css',
  BASE + 'assets/css/core/utilities.css',
  BASE + 'assets/css/layout/topbar.css',
  BASE + 'assets/css/components/nav.css',
  BASE + 'assets/css/layout/blocks.css',
  BASE + 'assets/css/pages/home-dash.css',
  BASE + 'assets/css/responsive/desktop.css',
  BASE + 'assets/js/app.js'
];

/* Instalación: precache de assets críticos y activación inmediata */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

/* Activación: navigationPreload + limpieza de versiones antiguas + control */
self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    if (self.registration.navigationPreload) {
      await self.registration.navigationPreload.enable();
    }
    const keys = await caches.keys();
    await Promise.all(
      keys.filter(k => ![STATIC_CACHE, RUNTIME_CACHE].includes(k))
          .map(k => caches.delete(k))
    );
    await self.clients.claim();
  })());
});

/* Utilidad: detectar si es navegación (HTML) */
const isNav = (req) => req.mode === 'navigate';

/* Estrategias:
   - HTML (navegación): network-first → offline.html si falla
   - CSS/JS: stale-while-revalidate
   - imágenes: cache-first
*/
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Garantiza que solo se controla lo que cae bajo el scope/base
  if (!url.pathname.startsWith(BASE)) return;

  // 1) Navegación a páginas (HTML)
  if (isNav(req)) {
    event.respondWith((async () => {
      try {
        // Usa preload si está (mejora TTFB)
        const preload = await event.preloadResponse;
        if (preload) return preload;

        const net = await fetch(req);
        // cachea una copia básica del HTML de runtime (opcional)
        const runtime = await caches.open(RUNTIME_CACHE);
        runtime.put(req, net.clone());
        return net;
      } catch {
        // Si no hay red ni HTML caché → offline.html
        const staticCache = await caches.open(STATIC_CACHE);
        return (await staticCache.match(BASE + 'pwa/offline.html')) ||
               new Response('Offline', { status: 503 });
      }
    })());
    return;
  }

  // 2) CSS/JS → Stale-While-Revalidate
  if (/\.(css|js)$/.test(url.pathname)) {
    event.respondWith((async () => {
      const runtime = await caches.open(RUNTIME_CACHE);
      const cached = await runtime.match(req);
      const update = fetch(req).then(res => {
        runtime.put(req, res.clone());
        return res;
      }).catch(() => null);
      return cached || update || fetch(req);
    })());
    return;
  }

  // 3) Imágenes → Cache-First (simple)
  if (/\.(png|jpg|jpeg|webp|svg|ico)$/.test(url.pathname)) {
    event.respondWith((async () => {
      const runtime = await caches.open(RUNTIME_CACHE);
      const cached = await runtime.match(req);
      if (cached) return cached;
      try {
        const net = await fetch(req);
        runtime.put(req, net.clone());
        return net;
      } catch {
        return new Response('', { status: 404 });
      }
    })());
    return;
  }

  // 4) Otros (APIs, etc.) → dejar pasar o personalizar aquí si hace falta
});
