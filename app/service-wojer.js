// /medtucIoT/app/service-wojer.js

const CACHE_NAME = "medtuciot-v1";
const urlsToCache = [
  "/medtucIoT/app/dashboard.php",
  "/medtucIoT/app/manifest.json",
  "/medtucIoT/app/assets/css/style.css",
  "/medtucIoT/app/assets/css/auth.css",
  "/medtucIoT/app/assets/js/main.js",
  "/medtucIoT/app/assets/js/addSensor.js",
  "/medtucIoT/app/assets/js/charts_sensores.js",
  "/medtucIoT/app/assets/js/bridge.js",
  "/medtucIoT/app/assets/js/pwa.js",
  "/medtucIoT/app/assets/img/logo-small.png",
  "/medtucIoT/app/assets/img/iconSP.png"
];

self.addEventListener("install", event => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      await Promise.all(urlsToCache.map(async url => {
        try {
          const res = await fetch(url, { cache: "no-cache" });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          await cache.put(url, res.clone());
        } catch (err) {
          console.warn(`❌ falló cachear ${url}:`, err.message);
        }
      }));
      await self.skipWaiting();
    })()
  );
});

self.addEventListener("activate", event => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(
        keys.map(key => key !== CACHE_NAME ? caches.delete(key) : null)
      );
      await self.clients.claim();
    })()
  );
});

self.addEventListener("fetch", event => {
  // sólo GET y dentro de /medtucIoT/app/
  if (event.request.method !== "GET" ||
      !event.request.url.startsWith(location.origin + "/medtucIoT/app/")) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then(async cached => {
      if (cached) return cached;
      try {
        const networkResponse = await fetch(event.request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(event.request, networkResponse.clone());
        return networkResponse;
      } catch {
        // fallback a dashboard.php si es navegación
        if (event.request.mode === "navigate") {
          return caches.match("/medtucIoT/app/dashboard.php");
        }
      }
    })
  );
});
