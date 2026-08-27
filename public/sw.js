// Naik ke v3 supaya semua klien lama membuang cache v2 yang berisi HTML
// dashboard basi (v2 me-precache '/' yang ternyata redirect ke /dashboard).
const CACHE_NAME = 'portal-sekolah-v3';

// HANYA aset statis milik pihak ketiga.
// JANGAN pernah me-precache '/' atau halaman HTML lain: responsnya bergantung
// pada sesi login dan berubah tiap deploy, jadi menyimpannya membuat browser
// menyajikan halaman lama selamanya.
const urlsToCache = [
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
  'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache).catch(() => {}))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(cacheName => cacheName !== CACHE_NAME)
          .map(cacheName => caches.delete(cacheName))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;

  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Navigasi halaman (HTML): SELALU jaringan dulu. Cache hanya dipakai kalau
  // koneksi mati, supaya perubahan setelah deploy langsung terlihat.
  if (req.mode === 'navigate' || req.destination === 'document') {
    event.respondWith(
      fetch(req).catch(() => caches.match('/'))
    );
    return;
  }

  // Aset statis: cache dulu, baru jaringan.
  event.respondWith(
    caches.match(req).then(cached => cached || fetch(req).then(res => {
      if (res.ok && (url.origin === location.origin || url.hostname.endsWith('cdn.jsdelivr.net') || url.hostname.endsWith('cdnjs.cloudflare.com'))) {
        const copy = res.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
      }
      return res;
    }))
  );
});

// PUSH NOTIFICATION HANDLING
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : { title: 'Notifikasi Baru', body: 'Ada pembaruan di Portal Sekolah.' };

  const options = {
    body: data.body,
    icon: '/logo_sekolah.png',
    badge: '/logo_sekolah.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});
