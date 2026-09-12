/*
 * Service worker School Safe Zone — ditulis manual agar ringan dan mudah dibaca.
 * Strategi: network-first untuk halaman HTML, cache-first untuk aset statis.
 * Tidak pernah meng-cache /livewire/*, /api/*, dan /q/* (SPEC §10).
 */

const CACHE_VERSION = 'ssz-v1';
const OFFLINE_URL = '/offline';

const PRECACHE = [
    OFFLINE_URL,
    '/images/logo-sman1ciruas.png',
    '/images/logo-polres-serang.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_VERSION)
            .then((cache) => cache.addAll(PRECACHE).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

/** URL yang tidak boleh disimpan di cache. */
function isBypassed(url) {
    return (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/q/') ||
        url.pathname.startsWith('/media/') ||
        url.pathname.startsWith('/push/') ||
        url.pathname.startsWith('/admin/stiker/')
    );
}

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        /\.(css|js|woff2?|png|jpe?g|svg|webp)$/.test(url.pathname)
    );
}

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin || isBypassed(url)) {
        return;
    }

    // Aset statis: cache-first.
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                    }

                    return response;
                });
            })
        );

        return;
    }

    // Halaman HTML: network-first, jatuh ke cache lalu halaman offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));

                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL)))
        );
    }
});

/* ---------------- Web Push ---------------- */

self.addEventListener('push', (event) => {
    if (! event.data) {
        return;
    }

    let payload = {};

    try {
        payload = event.data.json();
    } catch (e) {
        payload = { title: 'School Safe Zone', body: event.data.text() };
    }

    const title = payload.title || 'School Safe Zone';

    const options = {
        body: payload.body || '',
        icon: payload.icon || '/icons/icon-192.png',
        badge: payload.badge || '/icons/icon-192.png',
        tag: payload.tag || 'ssz',
        renotify: true,
        data: payload.data || {},
        vibrate: [80, 40, 80],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(target) && 'focus' in client) {
                    return client.focus();
                }
            }

            return self.clients.openWindow(target);
        })
    );
});
