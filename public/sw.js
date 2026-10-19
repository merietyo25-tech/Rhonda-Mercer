/* ═══════════════════════════════════════════════════════════════
  Application client — Service Worker v12
   Cache-first assets · Network-first HTML
   Push Notifications VAPID — design fintech pro
   ═══════════════════════════════════════════════════════════════ */
// Nom versionne : le bump force les appareils deja installes a recharger les fichiers.
const CACHE = 'mf-v12';
// Icone derivee du logo televerse en admin (route dynamique, pas un fichier fige).
const ICON  = '/site-icon-192.png';
const BADGE = '/images/icon-badge.png';
const SHELL = ['/app', '/login'];

/* ── Install ────────────────────────────────────────────────────── */
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE)
            .then(c => c.addAll(SHELL))
            .then(() => self.skipWaiting())
    );
});

/* ── Activate (purge old caches) ───────────────────────────────── */
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

/* ── Fetch ──────────────────────────────────────────────────────── */
self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    const url = new URL(e.request.url);
    if (!url.protocol.startsWith('http')) return;
    if (url.pathname.startsWith('/storage/')) return;

    /* Assets Vite — Cache-First */
    if (url.pathname.startsWith('/build/assets/')) {
        e.respondWith(
            caches.open(CACHE).then(c =>
                c.match(e.request).then(cached => {
                    if (cached) return cached;
                    return fetch(e.request).then(resp => {
                        if (resp.ok) c.put(e.request, resp.clone());
                        return resp;
                    });
                })
            )
        );
        return;
    }

    /* Images & fonts — Cache-First */
    if (/\.(png|jpg|jpeg|gif|svg|ico|webp|woff2?|ttf|eot|otf)(\?.*)?$/.test(url.pathname)) {
        e.respondWith(
            caches.match(e.request).then(cached => {
                if (cached) return cached;
                return fetch(e.request).then(resp => {
                    if (resp.ok) caches.open(CACHE).then(c => c.put(e.request, resp.clone()));
                    return resp;
                }).catch(() => cached);
            })
        );
        return;
    }

    /* Tout le reste — Network-First */
    e.respondWith(
        fetch(e.request)
            .then(resp => {
                if (resp.ok) {
                    caches.open(CACHE).then(c => c.put(e.request, resp.clone()));
                }
                return resp;
            })
            .catch(() =>
                caches.match(e.request).then(cached => cached || caches.match('/app'))
            )
    );
});

/* ── Push Notifications — design fintech pro ────────────────────── */
const TYPE_CONFIG = {
    transfer: {
        title_prefix: '💸',
        actions: [
            { action: 'view',  title: 'Voir le virement' },
            { action: 'close', title: 'Fermer' },
        ],
    },
    credit: {
        title_prefix: '✅',
        actions: [
            { action: 'view',  title: 'Voir mon solde' },
            { action: 'close', title: 'Fermer' },
        ],
    },
    loan_update: {
        title_prefix: '📄',
        actions: [
            { action: 'view',  title: 'Voir le dossier' },
            { action: 'close', title: 'Fermer' },
        ],
    },
    system: {
        title_prefix: '🔔',
        actions: [
            { action: 'view',  title: 'Ouvrir' },
            { action: 'close', title: 'Fermer' },
        ],
    },
};

self.addEventListener('push', e => {
    const defaults = {
        title: 'Santander Bank',
        body:  '',
        tag: 'mf',
        url:   '/app/notifications',
        type:  'system',
    };

    let data = defaults;
    try {
        if (e.data) data = { ...defaults, ...e.data.json() };
    } catch (_) {}

    const cfg = TYPE_CONFIG[data.type] || TYPE_CONFIG[data.tag] || TYPE_CONFIG.system;

    const notifTitle = data.title || 'Santander Bank';
    const notifBody  = data.body  || '';

    e.waitUntil(
        self.registration.showNotification(notifTitle, {
            body:               notifBody,
            icon:               ICON,
            badge:              BADGE,
            vibrate:            [100, 60, 100, 60, 300],
            tag:                data.tag  || 'mf-notif',
            renotify:           true,
            requireInteraction: false,
            timestamp:          Date.now(),
            dir:                'ltr',
            actions:            cfg.actions,
            data: {
                url:  data.url  || '/app/notifications',
                type: data.type || 'system',
            },
        })
    );
});

/* ── Notification click ─────────────────────────────────────────── */
self.addEventListener('notificationclick', e => {
    e.notification.close();

    if (e.action === 'close') return;

    const target = (e.notification.data && e.notification.data.url) || '/app/notifications';

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            /* Si l'app est déjà ouverte → naviguer dedans */
            for (const c of list) {
                if (c.url.includes('/app') && 'focus' in c) {
                    c.navigate(target);
                    return c.focus();
                }
            }
            /* Sinon ouvrir une nouvelle fenêtre */
            if (clients.openWindow) return clients.openWindow(target);
        })
    );
});

/* ── Push subscription change (renouvellement auto navigateur) ───── */
/* Le SW n'a pas accès au token CSRF → l'endpoint est exclu du middleware CSRF
   Les cookies de session sont envoyés automatiquement (same-origin)             */
self.addEventListener('pushsubscriptionchange', e => {
    e.waitUntil(
        self.registration.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: e.oldSubscription
                ? e.oldSubscription.options.applicationServerKey
                : e.newSubscription.options.applicationServerKey,
        }).then(sub => {
            const subJson = sub.toJSON();
            return fetch('/app/push/subscribe', {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:        JSON.stringify({
                    endpoint:   sub.endpoint,
                    public_key: subJson.keys?.p256dh || '',
                    auth_token: subJson.keys?.auth   || '',
                }),
            });
        }).catch(() => {})
    );
});
