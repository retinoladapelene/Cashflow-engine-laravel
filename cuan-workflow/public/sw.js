const CACHE_NAME = 'cuancapital-v14-laravel';
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
    // Skip Firestore, API requests, and browser extensions
    if (event.request.url.includes('firestore') ||
        event.request.url.includes('googleapis') ||
        event.request.url.startsWith('chrome-extension')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Only cache GET requests
                if (event.request.method === 'GET') {
                    // Clone the response because it's a stream and can only be consumed once
                    const responseToCache = response.clone();

                    caches.open(CACHE_NAME)
                        .then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                }

                return response;
            })
            .catch(() => {
                // Fallback to cache if network fails (Offline Mode)
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }

                    // If request is for an API, return JSON error
                    if (event.request.url.includes('/api/')) {
                        return new Response(JSON.stringify({ error: 'System under maintenance or offline' }), {
                            status: 503,
                            headers: { 'Content-Type': 'application/json' }
                        });
                    }

                    // If not in cache and not API, return a fallback response
                    return new Response('Network error and not in cache', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({
                            'Content-Type': 'text/plain'
                        })
                    });
                });
            })
    );
});
