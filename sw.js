// Service Worker for PWA functionality
const CACHE_NAME = 'luis-marte-portfolio-v1';
const urlsToCache = [
    '/',
    '/styles.css',
    '/script.js',
    '/Img/001.png',
    '/Img/004.png',
    '/icon/html1.svg',
    '/icon/css1.svg',
    '/icon/Js2.svg',
    '/icon/icons8-c++-48.svg',
    '/icon/icons8-wordpress.svg',
    '/icon/Adobe3.svg',
    '/icon/Adobe2.svg',
    '/icon/Fig2.svg',
    '/icon/adobe.svg',
    '/icon/icons8-code-80.png',
    '/icon/capcut.svg',
    '/icon/icons8-canva.svg',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});

// Activate event
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
