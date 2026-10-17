<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="{{ site_name() }}">
  <meta name="theme-color" content="#008457">
  <meta name="description" content="{{ site_name() }} — Espace client mobile">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', site_name())</title>

  <link rel="manifest" href="{{ route('pwa.manifest') }}">
  {{-- Icônes PWA --}}
  <link rel="apple-touch-icon" sizes="180x180" href="/site-icon-180.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/site-icon-512.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/site-icon-192.png">
  {{-- Couvre favicon.ico vide pour les navigateurs/crawlers qui le demandent --}}
  <link rel="shortcut icon" href="/site-icon-192.png" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @vite(['resources/css/client-app.css', 'resources/js/client-app.js'])

  {{-- Init theme AVANT le rendu pour éviter le flash blanc/noir.
       Thème sombre par défaut (le clair fatiguait trop les yeux) ; l'utilisateur
       garde la main via le sélecteur de thème, stocké sous la même clé 'v2'. --}}
  <script>
    (function(){
      var t = localStorage.getItem('solberg-theme-v2') || 'light';
      document.documentElement.dataset.theme = t;
    })();
  </script>
  <script>window._copiedLabel = '{{ __("app.copied") }}';</script>

  @stack('styles')
</head>
<body x-data>

{{-- ══ SPLASH SCREEN ══ --}}
<div id="cxa-splash" aria-hidden="true">
  <x-logo variant="full" theme="dark" size="lg" id="cxa-splash-logo" />
</div>
<style>
#cxa-splash{
  position:fixed;inset:0;z-index:9999;
  background:#312B81;
  display:flex;align-items:center;justify-content:center;
  animation:splashFade 0.4s ease 1.4s forwards;
  pointer-events:none;
}
#cxa-splash-logo{
  animation:splashLogo 0.55s cubic-bezier(.22,1,.36,1) 0.1s both;
}
@keyframes splashLogo{
  from{opacity:0;transform:scale(.7)}
  to  {opacity:1;transform:scale(1)}
}
@keyframes splashFade{
  to{opacity:0;visibility:hidden}
}
</style>
<script>
(function(){
  /* Ne montrer le splash qu'au lancement PWA standalone ou premier chargement */
  var shown = sessionStorage.getItem('cxa_splash');
  var isStandalone = window.matchMedia('(display-mode: standalone)').matches
                   || window.navigator.standalone === true;
  if (shown && !isStandalone) {
    document.getElementById('cxa-splash').style.display = 'none';
  } else {
    sessionStorage.setItem('cxa_splash', '1');
    setTimeout(function(){
      var s = document.getElementById('cxa-splash');
      if (s) s.remove();
    }, 2000);
  }
})();
</script>

{{-- ══ SHELL (scroll container — sans overflow:hidden) ══ --}}
<div class="ca-shell">

  {{-- ── TOPBAR ── --}}
  @hasSection('topbar')
    @yield('topbar')
  @else
  <header class="ca-topbar @yield('topbar_class')">
    @hasSection('back_btn')
    <a href="@yield('back_url', route('client.app.home'))" class="ca-topbar__back" aria-label="Retour">
      <i class="fas fa-arrow-left"></i>
    </a>
    @else
    <div style="width:38px"></div>
    @endif

    <span class="ca-topbar__title">@yield('page_title', site_name())</span>

    @hasSection('topbar_action')
    @yield('topbar_action')
    @else
    <div style="width:38px"></div>
    @endif
    @include('partials.client-chip')
  </header>
  @endif

  {{-- Bandeau permanent : identité non vérifiée (l'accès aux virements l'exige) --}}
  @php
    $kycUser   = auth()->user();
    $kycRecord = $kycUser?->kycVerification;
    $kycStatus = $kycRecord->status ?? \App\Models\KycVerification::STATUS_NON_SOUMIS;
    $kycBanner = $kycUser && $kycStatus !== \App\Models\KycVerification::STATUS_APPROUVE
                 && ! request()->routeIs('client.app.kyc.*');
    $kycPending = $kycStatus === \App\Models\KycVerification::STATUS_EN_ATTENTE;
  @endphp
  @if($kycBanner)
  <div class="ca-kyc-banner {{ $kycPending ? 'is-pending' : '' }}" role="status">
    <i class="fas {{ $kycPending ? 'fa-hourglass-half' : 'fa-id-card' }}"></i>
    <div class="ca-kyc-banner__txt">
      {{ $kycPending ? __('onboarding.kyc_banner_pending') : __('kyc.required_notice') }}
    </div>
    <a href="{{ route('client.app.kyc.show') }}" class="ca-kyc-banner__btn">
      {{ $kycPending ? __('onboarding.kyc_banner_view') : __('onboarding.kyc_banner_cta') }}
    </a>
  </div>
  @endif

  {{-- Bandeau permanent : compte bloqué par l'administration --}}
  @if(auth()->user()?->hasBlockedAccount())
  <div class="ca-blocked-banner" role="alert" style="display:flex;align-items:center;gap:.85rem;margin:.75rem 1.25rem 0;padding:.85rem 1rem;border-radius:var(--ca-radius-md);background:rgba(239,68,68,.11);border:1px solid rgba(239,68,68,.35);border-left:3px solid #ef4444;color:var(--ca-text)">
    <i class="fas fa-ban" style="color:#ef4444;font-size:1.2rem;flex-shrink:0"></i>
    <div style="flex:1;min-width:0;font-size:.82rem;line-height:1.5">
      <strong style="display:block;color:#f87171">{{ __('app.account_blocked_title') }}</strong>
      {{ __('app.account_blocked_text') }}
    </div>
    <a href="{{ route('client.app.support') }}" style="flex-shrink:0;white-space:nowrap;padding:.5rem .95rem;border-radius:999px;background:rgba(239,68,68,.16);color:#f87171;font-size:.78rem;font-weight:700;text-decoration:none">{{ __('cards.contact') }}</a>
  </div>
  @endif

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="ca-flash ca-flash--ok" role="alert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
  @endif
  @if(session('error') && ! ($kycBanner && session('error') === __('kyc.required_notice')))
  <div class="ca-flash ca-flash--err" role="alert">
    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
  </div>
  @endif

  {{-- ── CONTENU PRINCIPAL ── --}}
  <main class="ca-main @yield('main_class')" id="ca-main-content">
    @yield('content')
  </main>

</div>{{-- /.ca-shell --}}

{{-- ══════════════════════════════════════════════════════════════════
     BOTTOM NAVIGATION — hors du shell pour eviter le clip iOS Safari
     ══════════════════════════════════════════════════════════════════ --}}
@if (true)
<nav class="ca-nav {{ View::hasSection('no_bottom_nav') ? 'ca-nav--mobile-hidden' : '' }}" role="navigation" aria-label="{{ __('app.nav_label', [], app()->getLocale()) ?? 'Navigation' }}">

  {{-- Marque : visible uniquement dans la barre latérale (tablette / PC) --}}
  <a href="{{ route('client.app.home') }}" class="ca-nav__brand" aria-label="{{ site_name() }}">
    <x-logo variant="icon" theme="light" size="sm" />
    <span class="ca-nav__brand-name">{{ site_name() }}</span>
  </a>

  {{-- Accueil --}}
  <a href="{{ route('client.app.home') }}"
     class="ca-nav-item {{ request()->routeIs('client.app.home') ? 'active' : '' }}"
     aria-label="{{ __('app.nav_home') }}">
    <i class="fas fa-house"></i>
    <span>{{ __('app.nav_home') }}</span>
  </a>

  {{-- Envoyer (barre latérale uniquement) --}}
  <a href="{{ route('client.app.transfer.send') }}"
     class="ca-nav-item ca-nav-item--side {{ request()->routeIs('client.app.transfer.*') ? 'active' : '' }}"
     aria-label="{{ __('app.action_send') }}">
    <i class="fas fa-paper-plane"></i>
    <span>{{ __('app.action_send') }}</span>
  </a>

  {{-- Cartes --}}
  <a href="{{ route('client.app.cards') }}"
     class="ca-nav-item {{ request()->routeIs('client.app.cards') ? 'active' : '' }}"
     aria-label="{{ __('cards.nav') }}">
    <i class="fas fa-credit-card"></i>
    <span>{{ __('cards.nav') }}</span>
  </a>

  {{-- IBAN --}}
  <a href="{{ route('client.app.payment-methods') }}"
     class="ca-nav-item {{ request()->routeIs('client.app.payment-methods', 'client.app.transfer.receive') ? 'active' : '' }}"
     aria-label="IBAN">
    <i class="fas fa-building-columns"></i>
    <span>IBAN</span>
  </a>

  {{-- Mouvements + notifications (barre latérale uniquement) --}}
  <a href="{{ route('client.app.movements') }}"
     class="ca-nav-item ca-nav-item--side {{ request()->routeIs('client.app.movements') ? 'active' : '' }}"
     aria-label="{{ __('app.movements_title') }}">
    <i class="fas fa-arrow-right-arrow-left"></i>
    <span>{{ __('app.movements_title') }}</span>
  </a>
  <a href="{{ route('client.app.notifications') }}"
     class="ca-nav-item ca-nav-item--side {{ request()->routeIs('client.app.notifications') ? 'active' : '' }}"
     aria-label="{{ __('app.notifications_title') }}">
    <i class="fas fa-bell"></i>
    <span>{{ __('app.notifications_title') }}</span>
  </a>

  {{-- Profil --}}
  <a href="{{ route('client.app.profile') }}"
     class="ca-nav-item {{ request()->routeIs('client.app.profile') ? 'active' : '' }}"
     aria-label="{{ __('app.nav_profile') }}">
    <i class="fas fa-circle-user"></i>
    <span>{{ __('app.nav_profile') }}</span>
  </a>

</nav>
@endif

@stack('scripts')

{{-- ══ Confirmation d'action (formulaires / liens portant data-confirm) ══ --}}
<div id="cxa-confirm" role="dialog" aria-modal="true" aria-labelledby="cxa-confirm-title" hidden>
  <div class="cxa-confirm__backdrop" data-cxa-cancel></div>
  <div class="cxa-confirm__box">
    <div class="cxa-confirm__icon" id="cxa-confirm-icon"><i class="fas fa-circle-question"></i></div>
    <h3 id="cxa-confirm-title" class="cxa-confirm__title">{{ __('app.confirm_title') }}</h3>
    <p id="cxa-confirm-msg" class="cxa-confirm__msg"></p>
    <div class="cxa-confirm__actions">
      <button type="button" class="cxa-confirm__btn cxa-confirm__btn--ghost" data-cxa-cancel>{{ __('app.confirm_cancel') }}</button>
      <button type="button" class="cxa-confirm__btn cxa-confirm__btn--ok" id="cxa-confirm-ok">{{ __('app.confirm_ok') }}</button>
    </div>
  </div>
</div>
<style>
/* Les fenêtres modales passent au-dessus du menu : l'animation d'entrée ne doit pas laisser de
   contexte d'empilement sur .ca-shell une fois terminée (sinon tout modal y reste coincé sous la barre de navigation). */
.ca-shell { animation-fill-mode: backwards !important; }
.pwa-fab { z-index: 900 !important; }
/* Entrées de menu réservées à la barre latérale (tablette / PC) */
.ca-nav-item--side { display: none !important; }
@media (min-width: 768px) { .ca-nav-item--side { display: flex !important; } }
#cxa-confirm { position: fixed; inset: 0; z-index: 30000; display: flex; align-items: flex-end; justify-content: center; padding: 1rem; }
#cxa-confirm[hidden] { display: none; }
@media (min-width: 640px) { #cxa-confirm { align-items: center; } }
.cxa-confirm__backdrop { position: absolute; inset: 0; background: rgba(3, 12, 10, .62); backdrop-filter: blur(3px); }
.cxa-confirm__box { position: relative; width: 100%; max-width: 400px; background: var(--ca-bg2, #12332a); color: var(--ca-text, #fff); border: 1px solid var(--ca-border, rgba(255,255,255,.12));
  border-radius: 22px; padding: 1.6rem 1.4rem 1.25rem; text-align: center; box-shadow: 0 24px 70px rgba(0,0,0,.45); animation: cxaConfirmIn .18s ease-out; }
@keyframes cxaConfirmIn { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
.cxa-confirm__icon { width: 54px; height: 54px; margin: 0 auto .85rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
  background: rgba(63,203,146,.16); color: var(--ca-accent, #3FCB92); }
.cxa-confirm__icon.is-danger { background: rgba(255,90,90,.14); color: var(--ca-negative, #ff5a5a); }
.cxa-confirm__title { margin: 0 0 .4rem; font-size: 1.05rem; font-weight: 800; }
.cxa-confirm__msg { margin: 0 0 1.25rem; font-size: .84rem; line-height: 1.55; color: var(--ca-text-2, rgba(255,255,255,.7)); overflow-wrap: anywhere; }
.cxa-confirm__actions { display: flex; gap: .6rem; }
.cxa-confirm__btn { flex: 1; min-height: 48px; border-radius: 999px; font-family: inherit; font-size: .88rem; font-weight: 800; cursor: pointer; border: 1px solid transparent; }
.cxa-confirm__btn--ghost { background: transparent; color: var(--ca-text-2, rgba(255,255,255,.75)); border-color: var(--ca-border, rgba(255,255,255,.18)); }
.cxa-confirm__btn--ok { color: #fff; background: linear-gradient(135deg, #3FCB92, #008457); }
.cxa-confirm__btn--ok.is-danger { background: linear-gradient(135deg, #f87171, #dc2626); }
</style>
<script>
(function () {
  var box = document.getElementById('cxa-confirm'); if (!box) return;
  var msg = document.getElementById('cxa-confirm-msg'), ttl = document.getElementById('cxa-confirm-title'),
      ok = document.getElementById('cxa-confirm-ok'), ico = document.getElementById('cxa-confirm-icon'), pending = null;
  var defTitle = ttl.textContent, defOk = ok.textContent;

  function close() { box.hidden = true; pending = null; }
  function open(el, run) {
    if (!box.hidden) return;
    msg.textContent = el.getAttribute('data-confirm') || '';
    ttl.textContent = el.getAttribute('data-confirm-title') || defTitle;
    ok.textContent  = el.getAttribute('data-confirm-ok') || defOk;
    var danger = el.getAttribute('data-confirm-danger') === '1';
    ico.classList.toggle('is-danger', danger); ok.classList.toggle('is-danger', danger);
    pending = run; box.hidden = false; ok.focus();
  }
  box.querySelectorAll('[data-cxa-cancel]').forEach(function (b) { b.addEventListener('click', close); });
  ok.addEventListener('click', function () { var run = pending; close(); if (run) run(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) close(); });

  // Formulaires : la soumission est interceptée, puis rejouée une fois confirmée.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest ? e.target.closest('form[data-confirm]') : null;
    if (!form || form.dataset.cxaOk === '1') return;
    e.preventDefault(); e.stopImmediatePropagation();
    open(form, function () { form.dataset.cxaOk = '1'; (form.requestSubmit ? form.requestSubmit() : form.submit()); setTimeout(function () { delete form.dataset.cxaOk; }, 400); });
  }, true);

  // Liens et boutons hors formulaire.
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el || el.tagName === 'FORM' || el.closest('form[data-confirm]') || el.dataset.cxaOk === '1') return;
    e.preventDefault(); e.stopImmediatePropagation();
    open(el, function () { el.dataset.cxaOk = '1'; el.click(); });
  }, true);
})();
</script>


{{-- ══ Push Notifications ══ --}}
<div id="cxa-push-banner" style="display:none;position:fixed;bottom:calc(var(--ca-nav-h) + env(safe-area-inset-bottom,0px) + .5rem);left:.875rem;right:.875rem;z-index:9000;background:#312B81;border:1px solid rgba(63,203,146,.35);border-radius:16px;padding:.875rem 1rem;box-shadow:0 8px 32px rgba(49,43,129,.5);display:none;align-items:center;gap:.875rem">
  <div style="width:42px;height:42px;border-radius:13px;background:rgba(63,203,146,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <i class="fas fa-bell" style="color:#D9F2E7;font-size:1.1rem"></i>
  </div>
  <div style="flex:1;min-width:0">
    <div id="cxa-push-title" style="font-size:.84rem;font-weight:700;color:#fff;margin-bottom:.15rem">{{ __('app.push_enable') }}</div>
    <div id="cxa-push-text" style="font-size:.72rem;color:rgba(255,255,255,.45);line-height:1.4">{{ __('app.push_hint') }}</div>
  </div>
  <div style="display:flex;flex-direction:column;gap:.4rem;flex-shrink:0">
    <button id="cxa-push-allow" style="background:linear-gradient(90deg,#D9F2E7,#3FCB92);color:#312B81;border:none;padding:.42rem .875rem;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;min-width:92px;display:inline-flex;align-items:center;justify-content:center;gap:.4rem"><span class="cxa-push-spin" style="display:none;width:12px;height:12px;border:2px solid rgba(49,43,129,.25);border-top-color:#312B81;border-radius:50%;animation:cxaPushSpin .7s linear infinite"></span><span class="cxa-push-lbl">{{ __('app.push_allow') }}</span></button>
    <button id="cxa-push-later" style="background:none;border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.45);padding:.38rem .875rem;border-radius:8px;font-size:.72rem;cursor:pointer;white-space:nowrap">{{ __('app.push_later') }}</button>
  </div>
</div>

<style>@keyframes cxaPushSpin{to{transform:rotate(360deg)}} #cxa-push-allow:disabled,#cxa-push-later:disabled{opacity:.6;cursor:wait}</style>
<script>window.SOLBERG_VAPID_KEY = '{{ config("services.vapid.public_key") }}';</script>
<script>
(function () {
  const CSRF        = '{{ csrf_token() }}';
  const STORAGE_KEY = 'cxa_push_asked';

  document.addEventListener('DOMContentLoaded', async function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    const reg = await navigator.serviceWorker.ready;

    // Si les clés VAPID ont changé, invalider l'ancienne souscription
    const storedVapid = localStorage.getItem('cxa_vapid_pub');
    const currentVapid = window.SOLBERG_VAPID_KEY || '';
    if (storedVapid && storedVapid !== currentVapid) {
      const oldSub = await reg.pushManager.getSubscription();
      if (oldSub) {
        await fetch('/app/push/unsubscribe', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body:    JSON.stringify({ endpoint: oldSub.endpoint }),
        }).catch(() => {});
        await oldSub.unsubscribe().catch(() => {});
      }
      localStorage.removeItem('cxa_vapid_pub');
      localStorage.setItem(STORAGE_KEY, 'reset'); // Forcer ré-affichage bannière
    }

    // Déjà abonné dans le navigateur avec les bonnes clés → re-sync DB, et on ne redemande plus jamais
    const existing = await reg.pushManager.getSubscription();
    if (existing && (!storedVapid || storedVapid === currentVapid)) {
      if (typeof pushSubscribe === 'function') {
        await pushSubscribe(reg, CSRF).catch(() => {});
      }
      localStorage.setItem(STORAGE_KEY, 'granted');
      return;
    }

    if (Notification.permission === 'denied') {
      localStorage.setItem(STORAGE_KEY, 'denied');
      return;
    }

    const stored = localStorage.getItem(STORAGE_KEY);

    // Autorisation déjà donnée au navigateur mais pas encore d'abonnement : on s'abonne en silence, sans bannière
    if (Notification.permission === 'granted') {
      if (typeof pushSubscribe === 'function') {
        await pushSubscribe(reg, CSRF).catch(() => {});
      }
      localStorage.setItem(STORAGE_KEY, 'granted');
      return;
    }

    // Réponse déjà donnée (ou activation impossible sur cet appareil) : la bannière ne revient pas
    if (stored === 'denied' || stored === 'granted' || stored === 'done') return;

    // "Plus tard" → vérifier si le délai est écoulé
    if (stored && stored.startsWith('later:')) {
      const retryAt = parseInt(stored.split(':')[1], 10);
      if (Date.now() < retryAt) return;
      localStorage.removeItem(STORAGE_KEY);
    }

    const banner  = document.getElementById('cxa-push-banner');
    const allow   = document.getElementById('cxa-push-allow');
    const later   = document.getElementById('cxa-push-later');
    const spin    = allow?.querySelector('.cxa-push-spin');
    const lbl     = allow?.querySelector('.cxa-push-lbl');
    const titleEl = document.getElementById('cxa-push-title');
    const textEl  = document.getElementById('cxa-push-text');
    const T = {
      loading: @json(__('app.push_loading')),
      done:    @json(__('app.push_enabled')),
      failed:  @json(__('app.push_failed')),
      denied:  @json(__('app.push_denied')),
    };

    function setLoading(on) {
      if (!allow) return;
      allow.disabled = on; if (later) later.disabled = on;
      if (spin) spin.style.display = on ? 'inline-block' : 'none';
      if (lbl && on) lbl.textContent = T.loading;
    }
    function closeBanner(delay) { setTimeout(function () { if (banner) banner.style.display = 'none'; }, delay || 0); }

    // Montrer la bannière après 3 secondes
    setTimeout(function () { if (banner) banner.style.display = 'flex'; }, 3000);

    allow?.addEventListener('click', async function () {
      if (allow.disabled) return;
      setLoading(true);                      // chargement affiché dès le clic, jusqu'à la réponse
      let outcome = 'done', message = T.failed;
      try {
        const perm = await Notification.requestPermission();
        if (perm === 'granted') {
          const sub = typeof pushSubscribe === 'function' ? await pushSubscribe(reg, CSRF) : null;
          if (sub) { outcome = 'granted'; message = T.done; }
        } else if (perm === 'denied') {
          outcome = 'denied'; message = T.denied;
        } else {                              // fenêtre du navigateur fermée sans réponse : on redemandera dans 1 jour
          outcome = 'later:' + (Date.now() + 24 * 3600 * 1000); message = '';
        }
      } catch (e) { console.error('[push]', e); }

      localStorage.setItem(STORAGE_KEY, outcome);   // quelle que soit l'issue, la bannière ne revient pas à chaque page
      setLoading(false);
      if (message) {
        if (titleEl) titleEl.textContent = message;
        if (textEl)  textEl.style.display = 'none';
        if (allow)   allow.style.display = 'none';
        if (later)   later.style.display = 'none';
        closeBanner(2200);
      } else {
        closeBanner(0);
      }
    });

    later?.addEventListener('click', function () {
      closeBanner(0);
      const retry = Date.now() + 7 * 24 * 3600 * 1000;
      localStorage.setItem(STORAGE_KEY, 'later:' + retry);
    });
  });
})();
</script>

{{-- ══ Son & Polling notifications ══ --}}
<script>
// Synthese sonore Web Audio API (aucun fichier externe)
window.SolbergSound = (function () {
  let ctx = null;
  function ac() {
    if (!ctx) ctx = new (window.AudioContext || window.webkitAudioContext)();
    return ctx;
  }
  return {
    coin() {
      try {
        const c = ac();
        for (let i = 0; i < 3; i++) {
          const t = c.currentTime + i * 0.13;
          const o = c.createOscillator();
          const g = c.createGain();
          o.connect(g); g.connect(c.destination);
          o.type = 'triangle';
          o.frequency.setValueAtTime(1400, t);
          o.frequency.exponentialRampToValueAtTime(900, t + 0.07);
          g.gain.setValueAtTime(0.28, t);
          g.gain.exponentialRampToValueAtTime(0.001, t + 0.11);
          o.start(t); o.stop(t + 0.12);
        }
      } catch(e) {}
    },
    bell() {
      try {
        const c = ac();
        const t = c.currentTime;
        const o = c.createOscillator();
        const g = c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type = 'sine';
        o.frequency.setValueAtTime(880, t);
        o.frequency.exponentialRampToValueAtTime(620, t + 0.35);
        g.gain.setValueAtTime(0.32, t);
        g.gain.exponentialRampToValueAtTime(0.001, t + 0.55);
        o.start(t); o.stop(t + 0.56);
      } catch(e) {}
    }
  };
})();

// Polling des notifications toutes les 30 secondes
(function () {
  const POLL_MS = 30000;
  let lastCount = parseInt(sessionStorage.getItem('cxa_notif_count') || '0', 10);

  function updateDot(count) {
    const dot = document.getElementById('notif-dot');
    if (!dot) return;
    dot.style.display = count > 0 ? '' : 'none';
  }

  async function poll() {
    try {
      const r = await fetch('/app/notifications/unread-count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!r.ok) return;
      const data = await r.json();
      const count = data.count || 0;

      updateDot(count);

      if (count > lastCount) {
        if (data.type === 'transfer') {
          window.SolbergSound.coin();
        } else {
          window.SolbergSound.bell();
        }
      }
      lastCount = count;
      sessionStorage.setItem('cxa_notif_count', String(count));
    } catch (e) {}
  }

  document.addEventListener('DOMContentLoaded', function () {
    poll();
    setInterval(poll, POLL_MS);
  });
})();
</script>
@unless (View::hasSection('no_bottom_nav'))
@include('partials.pwa-install', ['bottom' => 'var(--ca-fab-bottom)', 'z' => 8000])
@endunless
</body>
</html>
