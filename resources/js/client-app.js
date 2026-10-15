import Alpine from 'alpinejs';
import {
    Chart,
    ArcElement,
    DoughnutController,
    LineElement,
    LineController,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(
    ArcElement, DoughnutController,
    LineElement, LineController, PointElement,
    LinearScale, CategoryScale,
    Tooltip, Legend, Filler
);

// ── Alpine keypad component ─────────────────────────────────────────
Alpine.data('keypad', (initial = '') => ({
    raw: String(initial),

    get display() {
        if (!this.raw) return '0';
        const n = parseFloat(this.raw);
        return isNaN(n) ? '0' : n.toLocaleString(document.documentElement.lang || 'fr', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    },

    get numericValue() {
        return parseFloat(this.raw) || 0;
    },

    press(key) {
        if (key === 'del') {
            this.raw = this.raw.slice(0, -1);
            return;
        }
        if (key === '.' && this.raw.includes('.')) return;
        if (this.raw.length >= 10) return;
        if (this.raw === '0' && key !== '.') { this.raw = key; return; }
        this.raw += key;
    },

    reset() { this.raw = ''; },
}));

// ── Push helpers ─────────────────────────────────────────────────────
const PUSH_VAPID_LS_KEY = 'cxa_vapid_pub';

function pushVapidKey() {
    return window.SOLBERG_VAPID_KEY || '';
}

function vapidKeyChanged() {
    const stored = localStorage.getItem(PUSH_VAPID_LS_KEY);
    return stored && stored !== pushVapidKey();
}

function urlBase64ToUint8Array(b64) {
    const padding = '='.repeat((4 - b64.length % 4) % 4);
    const base64  = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = window.atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

async function pushSubscribe(reg, csrf) {
    const vapidKey = pushVapidKey();
    if (!vapidKey) return null;

    // Si les clés VAPID ont changé, forcer la re-souscription
    let sub = await reg.pushManager.getSubscription();
    if (sub && vapidKeyChanged()) {
        await sub.unsubscribe();
        sub = null;
    }
    if (!sub) {
        sub = await reg.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });
    }
    const subJson = sub.toJSON();
    const res = await fetch('/app/push/subscribe', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body:    JSON.stringify({
            endpoint:   sub.endpoint,
            public_key: subJson.keys?.p256dh || '',
            auth_token: subJson.keys?.auth   || '',
        }),
    });
    if (res.ok) {
        localStorage.setItem(PUSH_VAPID_LS_KEY, vapidKey);
        localStorage.setItem('cxa_push_asked', 'granted');
    }
    return sub;
}

async function pushUnsubscribe(reg, csrf) {
    const sub = await reg.pushManager.getSubscription();
    if (sub) {
        await fetch('/app/push/unsubscribe', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body:    JSON.stringify({ endpoint: sub.endpoint }),
        });
        await sub.unsubscribe();
    }
    localStorage.removeItem(PUSH_VAPID_LS_KEY);
    localStorage.setItem('cxa_push_asked', 'denied');
}

// ── Alpine toggle component — Push Notifications ────────────────────
Alpine.data('togglePref', () => ({
    on:      false,
    loading: false,
    blocked: false,

    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            this.blocked = true;
            return;
        }
        if (Notification.permission === 'denied') {
            this.blocked = true;
            return;
        }
        try {
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            // Show as OFF if keys changed (subscription would fail server-side)
            this.on = !!sub && Notification.permission === 'granted' && !vapidKeyChanged();
        } catch (e) {}
    },

    async toggle() {
        if (this.loading || this.blocked) return;
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        this.loading = true;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        try {
            const reg = await navigator.serviceWorker.ready;

            if (this.on) {
                await pushUnsubscribe(reg, csrf);
                this.on = false;
            } else {
                if (Notification.permission === 'denied') {
                    this.blocked = true;
                    return;
                }
                const perm = Notification.permission === 'granted'
                    ? 'granted'
                    : await Notification.requestPermission();
                if (perm !== 'granted') {
                    localStorage.setItem('cxa_push_asked', 'denied');
                    return;
                }
                const sub = await pushSubscribe(reg, csrf);
                this.on = !!sub;
            }
        } catch (e) {
            console.error('[togglePref]', e);
        } finally {
            this.loading = false;
        }
    },
}));

// ── Dark / Light theme toggle ────────────────────────────────────────
Alpine.data('themeToggle', () => ({
    isDark: false,
    init() {
        const saved = localStorage.getItem('solberg-theme-v2') || 'dark';
        this.isDark = saved === 'dark';
        document.documentElement.dataset.theme = saved;
    },
    toggle() {
        this.isDark = !this.isDark;
        const theme = this.isDark ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('solberg-theme-v2', theme);
    },
}));

// ── Alpine lang dropdown ────────────────────────────────────────────
Alpine.data('langMenu', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
}));

// ── Chart helpers ───────────────────────────────────────────────────
window.buildDoughnutChart = function (canvasId, data, colors, labels) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('.7)', '.9)')),
                borderWidth: 1.5,
                hoverOffset: 8,
            }],
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#312B81',
                    borderColor: 'rgba(63,203,146,.3)',
                    borderWidth: 1,
                    titleColor: '#FFFFFF',
                    bodyColor: '#F5EDDD',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => ' ' + ctx.parsed.toLocaleString(document.documentElement.lang || 'fr', {
                            minimumFractionDigits: 2, maximumFractionDigits: 2,
                        }),
                    },
                },
            },
        },
    });
};

window.buildLineChart = function (canvasId, labels, values, currency) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: '#3FCB92',
                backgroundColor: 'rgba(63,203,146,.1)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#3FCB92',
                fill: true,
                tension: .4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#312B81',
                    borderColor: 'rgba(63,203,146,.3)',
                    borderWidth: 1,
                    titleColor: '#FFFFFF',
                    bodyColor: '#F5EDDD',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => ' ' + ctx.parsed.y.toLocaleString() + ' ' + (currency || ''),
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(14,59,46,.06)' },
                    ticks: { color: '#95A3B1', font: { size: 10 } },
                },
                y: {
                    grid: { color: 'rgba(14,59,46,.06)' },
                    ticks: { color: '#95A3B1', font: { size: 10 } },
                    beginAtZero: true,
                },
            },
        },
    });
};

// ── Clipboard copy ──────────────────────────────────────────────────
window.copyToClipboard = function (text, btnEl) {
    navigator.clipboard.writeText(text).then(() => {
        if (btnEl) {
            btnEl.classList.add('copied');
            const orig = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fas fa-check"></i> ' + (window._copiedLabel || 'Copie !');
            setTimeout(() => {
                btnEl.classList.remove('copied');
                btnEl.innerHTML = orig;
            }, 2000);
        }
    }).catch(() => {});
};

// ── PWA Service Worker ──────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}

// ── PWA Install banner ──────────────────────────────────────────────
let deferredPrompt = null;

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    const banner = document.getElementById('ca-install-banner');
    if (banner) banner.classList.add('show');
});

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('ca-install-btn');
    if (btn) {
        btn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            const banner = document.getElementById('ca-install-banner');
            if (banner) banner.classList.remove('show');
        });
    }
});

window.addEventListener('appinstalled', () => {
    const banner = document.getElementById('ca-install-banner');
    if (banner) banner.classList.remove('show');
});

// ── Start Alpine ────────────────────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();
