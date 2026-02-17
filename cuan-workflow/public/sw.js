const CACHE_NAME = 'cuancapital-v13-laravel';
const ASSETS_TO_CACHE = [
    '/',
    '/login',
    '/admin',
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/js/core/system-handler.js',
    '/assets/js/core/auth-engine.js',
    '/assets/js/core/admin-handler.js',
    '/assets/js/core/ad-arsenal.js',
    '/manifest.json',
    '/assets/icon/logo.svg',
    '/assets/icon/logo-2.svg'
];

// Install Event: Cache Critical Local Assets Only
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('SW: Pre-caching local assets');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// Activate Event: Clean Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('SW: Clearing old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event: Network First Strategy
self.addEventListener('fetch', (event) => {
    // Skip Firestore and API requests
    if (event.request.url.includes('firestore') || event.request.url.includes('googleapis')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone valid responses to cache (Runtime Caching)
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                return response;
            })
            .catch(() => {
                // Fallback to cache if network fails (Offline Mode)
                return caches.match(event.request);
            })
    );
});
