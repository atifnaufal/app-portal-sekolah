const CACHE_NAME = 'portal-sekolah-v2';
const urlsToCache = [
  '/',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
  'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});

// PUSH NOTIFICATION HANDLING
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : { title: 'Notifikasi Baru', body: 'Ada pembaruan di Portal Sekolah.' };

  const options = {
    body: data.body,
    icon: 'https://png.pngtree.com/png-clipart/20230124/original/pngtree-high-school-kids-holding-big-red-and-white-flags-png-image_8927815.png',
    badge: 'https://png.pngtree.com/png-clipart/20230124/original/pngtree-high-school-kids-holding-big-red-and-white-flags-png-image_8927815.png',
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
