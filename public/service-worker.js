/**
 * Service Worker - Portal Sekolah
 *
 * Strategy:
 *  - App shell (CSS/JS/icons) -> cache-first, populated on install
 *  - HTML navigations        -> stale-while-revalidate
 *  - Everything else         -> network-first, falling back to cache when offline
 *
 * Bump CACHE_NAME whenever the app shell changes so old caches are cleaned up.
 */

const CACHE_NAME = 'portal-sekolah-v1';

// Minimal app shell to pre-cache on install. Keep this list small and stable -
// build assets are hashed by Vite so they are cached on first request instead.
const APP_SHELL = [
    '/',
    '/manifest.json',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith('portal-sekolah-') && cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

function isHTMLRequest(request) {
    return request.mode === 'navigate' ||
        (request.headers.get('accept') || '').includes('text/html');
}

function isStaticAsset(request) {
    return ['style', 'script', 'font', 'image'].includes(request.destination);
}

// Stale-while-revalidate: return cached response immediately (if any) while
// updating the cache in the background from the network.
function staleWhileRevalidate(request) {
    return caches.open(CACHE_NAME).then((cache) =>
        cache.match(request).then((cachedResponse) => {
            const fetchPromise = fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.ok) {
                        cache.put(request, networkResponse.clone());
                    }
                    return networkResponse;
                })
                .catch(() => cachedResponse);

            return cachedResponse || fetchPromise;
        })
    );
}

// Cache-first for hashed static assets (CSS/JS/fonts/images built by Vite).
function cacheFirst(request) {
    return caches.open(CACHE_NAME).then((cache) =>
        cache.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.ok) {
                    cache.put(request, networkResponse.clone());
                }
                return networkResponse;
            });
        })
    );
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET requests - let POST/PUT/DELETE pass through untouched.
    if (request.method !== 'GET') {
        return;
    }

    // Ignore cross-origin requests (CDNs, APIs on other domains, etc).
    if (new URL(request.url).origin !== self.location.origin) {
        return;
    }

    if (isHTMLRequest(request)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    if (isStaticAsset(request)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Default: try the network first, fall back to cache when offline.
    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.ok) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            })
            .catch(() => caches.match(request))
    );
});
