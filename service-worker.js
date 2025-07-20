const CACHE_NAME = "superpublicador-v5";
const urlsToCache = [
  "/",                      // tu página de inicio (index.php)
  "/index.php",             // si quieres cachear directamente index.php
  "/assets/css/style.css",
  "/assets/css/auth.css",
  "/assets/js/app.js",
  "/assets/js/auth.js",
  "/manifest.json",
  "/images/iconSP.png",
  "/images/logo.png"
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => key !== CACHE_NAME ? caches.delete(key) : null)
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", event => {
  event.respondWith(
    caches.match(event.request).then(resp => resp || fetch(event.request))
  );
});
