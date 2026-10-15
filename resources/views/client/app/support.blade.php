@extends('layouts.client-app')
@section('title', 'Support — ' . site_name())
@section('no_bottom_nav', '1')

{{-- ── Topbar custom plein écran ── --}}
@section('topbar')
<header class="sc-topbar">
  <a href="{{ route('client.app.home') }}" class="sc-back" aria-label="Retour">
    <i class="fas fa-chevron-left"></i>
  </a>

  <div class="sc-agent-info">
    <div class="sc-agent-ico">
      <i class="fas fa-headset"></i>
      <span class="sc-online-dot"></span>
    </div>
    <div>
      <div class="sc-agent-name">Support {{ site_name() }}</div>
      <div class="sc-agent-status">
        <span class="sc-pulse"></span> En ligne
      </div>
    </div>
  </div>

  <div style="width:40px"></div>
</header>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════
   SUPPORT CHAT — préfixe sc-
   Full screen, pas de nav bottom
   ═══════════════════════════════════════════ */

/* ── Topbar ── */
:root {
  --sc-safe-top: env(safe-area-inset-top, 0px);
  --sc-topbar-h: 62px;
  /* Hauteur réelle = 62px + safe-area (encoche iPhone) */
  --sc-topbar-total: calc(var(--sc-topbar-h) + var(--sc-safe-top));
}
.sc-topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: var(--sc-topbar-total);
  z-index: 200;
  background: var(--ca-bg);
  border-bottom: 1px solid var(--ca-border-2);
  display: flex;
  align-items: flex-end;  /* contenu collé en bas pour rester au-dessus de la safe-area */
  gap: .5rem;
  padding: 0 .625rem .75rem;
  padding-top: var(--sc-safe-top);
  box-shadow: 0 1px 8px rgba(0,0,0,.18);
}
.sc-back {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: var(--ca-teal-l);
  font-size: 1.05rem;
  text-decoration: none;
  flex-shrink: 0;
  transition: background .15s;
}
.sc-back:hover { background: var(--ca-bg2); }

.sc-agent-info {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .75rem;
}
.sc-agent-ico {
  position: relative;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: linear-gradient(145deg, rgba(0,132,87,.35), rgba(0,132,87,.1));
  border: 1.5px solid rgba(0,132,87,.3);
  display: flex; align-items: center; justify-content: center;
  font-size: .95rem;
  color: var(--ca-teal-l);
  flex-shrink: 0;
}
.sc-online-dot {
  position: absolute;
  bottom: 1px; right: 1px;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: #4ade80;
  border: 2px solid var(--ca-bg);
}
.sc-agent-name { font-size: .875rem; font-weight: 700; color: var(--ca-text); line-height: 1.15; }
.sc-agent-status {
  font-size: .65rem;
  color: #4ade80;
  display: flex; align-items: center; gap: .3rem;
  margin-top: .1rem;
}
.sc-pulse {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: #4ade80;
  animation: sc-blink 2s ease-in-out infinite;
}
@keyframes sc-blink { 0%,100%{opacity:1} 50%{opacity:.25} }

/* ── Full screen container ── */
.sc-wrap {
  position: fixed;
  top: var(--sc-topbar-total);
  left: 0; right: 0;
  /* Hauteur = viewport visible - topbar.
     dvh = Dynamic Viewport Height (se réduit quand le clavier apparaît, iOS 15.4+/Chrome 108+).
     Le JS Visual Viewport ci-dessous prend le relais sur les anciens navigateurs. */
  height: calc(100dvh - var(--sc-topbar-total));
  display: flex;
  flex-direction: column;
  background: var(--ca-bg);
  z-index: 100;
  /* Empêche le rebond élastique iOS de faire défiler le fond */
  overscroll-behavior: none;
}

/* ── Messages zone ── */
.sc-msgs {
  flex: 1;
  overflow-y: auto;
  padding: 1rem .875rem .75rem;
  display: flex;
  flex-direction: column;
  gap: .25rem;
  overscroll-behavior-y: contain;
  -webkit-overflow-scrolling: touch;
}
.sc-msgs::-webkit-scrollbar { width: 2px; }
.sc-msgs::-webkit-scrollbar-thumb { background: var(--ca-border); border-radius: 99px; }

/* Date separator */
.sc-date {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .58rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: var(--ca-text-3);
  margin: .75rem 0 .375rem;
}
.sc-date::before,.sc-date::after {
  content: ''; flex: 1;
  height: 1px;
  background: var(--ca-border-2);
}

/* Bubble group */
.sc-bw { display: flex; flex-direction: column; margin-bottom: .125rem; }
.sc-bw--me   { align-items: flex-end; }
.sc-bw--them { align-items: flex-start; }
.sc-bw--bot  { align-items: flex-start; }

/* Bubbles */
.sc-bubble {
  max-width: 75%;
  padding: .6rem .875rem;
  border-radius: 20px;
  font-size: .8125rem;
  line-height: 1.55;
  word-break: break-word;
}
.sc-bw--me .sc-bubble {
  background: var(--ca-teal-l);
  color: #0F0D36;
  border-bottom-right-radius: 5px;
}
.sc-bw--them .sc-bubble {
  background: var(--ca-bg3);
  color: var(--ca-text);
  border: 1px solid var(--ca-border-2);
  border-bottom-left-radius: 5px;
}
.sc-bw--bot .sc-bubble {
  background: linear-gradient(135deg, rgba(0,132,87,.14), rgba(0,132,87,.06));
  color: var(--ca-text);
  border: 1.5px solid rgba(0,132,87,.22);
  border-bottom-left-radius: 5px;
}
.sc-bot-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: .57rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .07em;
  color: var(--ca-teal-l);
  margin-bottom: .2rem; opacity: .8;
}
.sc-bubble img {
  max-width: 200px;
  border-radius: 10px;
  display: block;
  cursor: pointer;
  margin-top: .3rem;
}
.sc-meta {
  font-size: .57rem;
  color: var(--ca-text-3);
  margin-top: .2rem;
  padding: 0 .3rem;
}

/* Empty state */
.sc-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem 1.5rem;
  pointer-events: none;
}
.sc-empty-ico {
  width: 72px; height: 72px;
  border-radius: 50%;
  background: linear-gradient(145deg, rgba(0,132,87,.18), rgba(0,132,87,.06));
  border: 1.5px solid rgba(0,132,87,.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.875rem;
  color: var(--ca-teal-l);
  margin: 0 auto 1rem;
  opacity: .7;
}
.sc-empty-title {
  font-size: .9rem;
  font-weight: 700;
  color: var(--ca-text);
  margin-bottom: .4rem;
  opacity: .6;
}
.sc-empty-sub {
  font-size: .775rem;
  color: var(--ca-text-3);
  line-height: 1.6;
  opacity: .5;
}

/* ── Preview strip (image avant envoi) ── */
.sc-preview {
  display: none;
  flex-shrink: 0;
  padding: .5rem .875rem;
  border-top: 1px solid var(--ca-border-2);
  background: var(--ca-bg2);
}
.sc-preview__inner {
  position: relative;
  display: inline-block;
}
.sc-preview__inner img {
  max-height: 72px;
  border-radius: 8px;
  display: block;
}
.sc-preview__rm {
  position: absolute; top: -6px; right: -6px;
  width: 20px; height: 20px;
  border-radius: 50%;
  background: #f87171;
  border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: .5rem; color: #fff;
}

/* ── Input bar ── */
.sc-bar {
  display: flex;
  align-items: flex-end;
  gap: .5rem;
  flex-shrink: 0;
  padding: .625rem .875rem;
  /* safe-area-inset-bottom = zone home indicator iPhone */
  padding-bottom: calc(.625rem + env(safe-area-inset-bottom, 0px));
  border-top: 1px solid var(--ca-border-2);
  background: var(--ca-bg);
  /* Garantit que la barre est toujours au-dessus de tout overlay natif */
  position: relative;
  z-index: 10;
}
.sc-btn-ico {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: 1.5px solid var(--ca-border);
  background: var(--ca-bg2);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem;
  color: var(--ca-text-3);
  flex-shrink: 0;
  transition: .15s;
}
.sc-btn-ico:hover,
.sc-btn-ico:active {
  border-color: var(--ca-teal-l);
  color: var(--ca-teal-l);
}
.sc-input {
  flex: 1;
  background: var(--ca-bg2);
  border: 1.5px solid var(--ca-border);
  border-radius: 22px;
  padding: .625rem 1rem;
  font-size: .8125rem;
  color: var(--ca-text);
  font-family: inherit;
  resize: none;
  outline: none;
  max-height: 110px;
  overflow-y: auto;
  transition: border-color .15s;
  line-height: 1.5;
  display: block;
}
.sc-input:focus { border-color: var(--ca-teal-l); }
.sc-input::placeholder { color: var(--ca-text-3); }
.sc-send {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: var(--ca-teal-l);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem;
  color: #0F0D36;
  flex-shrink: 0;
  opacity: .3;
  transition: opacity .15s, transform .1s;
}
.sc-send.active { opacity: 1; }
.sc-send.active:active { transform: scale(.88); }

/* ── Lightbox ── */
.sc-lightbox {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,.9);
  z-index: 9999;
  align-items: center;
  justify-content: center;
}
.sc-lightbox.open { display: flex; }
.sc-lightbox img {
  max-width: 90vw;
  max-height: 90dvh;
  border-radius: 12px;
  box-shadow: 0 8px 40px rgba(0,0,0,.6);
}
.sc-lightbox__close {
  position: absolute; top: 16px; right: 16px;
  width: 38px; height: 38px;
  border-radius: 50%;
  background: rgba(255,255,255,.14);
  border: none; cursor: pointer;
  color: #fff; font-size: 1rem;
  display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
.sc-lightbox__close:hover { background: rgba(255,255,255,.25); }

@keyframes scFadeUp {
  from { opacity:0; transform:translateY(8px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
@endpush

@section('content')
@php $csrfToken = csrf_token(); @endphp

{{-- ── Chat container full screen ── --}}
<div class="sc-wrap" id="scWrap">

  {{-- Messages ── --}}
  <div class="sc-msgs" id="scMsgs">

    @if($chatData->isEmpty())
    <div class="sc-empty" id="scEmpty">
      <div class="sc-empty-ico"><i class="fas fa-headset"></i></div>
      <div class="sc-empty-title">Bonjour, comment pouvons-nous vous aider ?</div>
      <div class="sc-empty-sub">Écrivez un message ou envoyez une image, votre conseiller vous répondra rapidement.</div>
    </div>
    @else
    <div id="scEmpty" style="display:none"></div>
    @endif

    <div id="scBubbles"></div>
  </div>

  {{-- Preview image avant envoi ── --}}
  <div class="sc-preview" id="scPreview">
    <div class="sc-preview__inner" id="scPreviewInner"></div>
  </div>

  {{-- Barre de saisie ── --}}
  <div class="sc-bar">
    <input type="file" id="scFileInput" accept="image/*" style="display:none">

    <button class="sc-btn-ico" title="Joindre une image"
            onclick="document.getElementById('scFileInput').click()">
      <i class="fas fa-image"></i>
    </button>

    <textarea
      class="sc-input"
      id="scInput"
      rows="1"
      placeholder="Votre message…"
      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,110)+'px';scUpdateSend()"
    ></textarea>

    <button class="sc-send" id="scSendBtn" onclick="scSend()">
      <i class="fas fa-paper-plane"></i>
    </button>
  </div>

</div>

{{-- Lightbox ── --}}
<div class="sc-lightbox" id="scLightbox" onclick="scCloseLightbox()">
  <button class="sc-lightbox__close" onclick="scCloseLightbox();event.stopPropagation()">
    <i class="fas fa-times"></i>
  </button>
  <img id="scLightboxImg" src="" alt="">
</div>

<script>
const SC_CSRF     = '{{ $csrfToken }}';
const SC_POLL_URL = '{{ route("client.app.support.poll") }}';
const SC_SEND_URL = '{{ route("client.app.support.store") }}';
let scLastId      = {{ $lastId }};
let scPendingFile = null;
let scPollTimer;
const scRendered  = new Set();
let scLastDateKey = null;

/* ── Render initial ── */
const scInitData = @json($chatData);
scInitData.forEach(m => scRenderBubble(m, false));
scScrollBottom();
scStartPolling();

/* ── Polling toutes les 3s ── */
function scStartPolling() {
  scPollTimer = setInterval(async () => {
    try {
      const r = await fetch(`${SC_POLL_URL}?after=${scLastId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      if (!r.ok) return;
      const d = await r.json();
      if (d.messages && d.messages.length) {
        d.messages.forEach(m => scRenderBubble(m, true));
        scScrollBottom();
      }
    } catch(e) {}
  }, 3000);
}

/* ── Render d'une bulle ── */
function scRenderBubble(msg, animate) {
  if (scRendered.has(msg.id)) return;
  scRendered.add(msg.id);
  if (msg.id > scLastId) scLastId = msg.id;

  document.getElementById('scEmpty').style.display = 'none';
  const wrap  = document.getElementById('scBubbles');
  const isMe  = msg.sender_type === 'client';
  const isBot = msg.is_bot === true;

  /* Séparateur de date */
  if (msg.date_key !== scLastDateKey) {
    scLastDateKey = msg.date_key;
    const sep = document.createElement('div');
    sep.className = 'sc-date';
    sep.textContent = msg.date_label;
    wrap.appendChild(sep);
  }

  const bw = document.createElement('div');
  bw.className = `sc-bw ${isMe ? 'sc-bw--me' : (isBot ? 'sc-bw--bot' : 'sc-bw--them')}`;
  if (animate) bw.style.animation = 'scFadeUp .22s ease';

  let inner = '';
  if (msg.body) inner += `<div>${scEsc(msg.body)}</div>`;
  if (msg.file_type === 'image' && msg.file_url)
    inner += `<img src="${msg.file_url}" loading="lazy" onclick="scOpenLightbox('${msg.file_url}')">`;

  const badge = isBot
    ? `<div class="sc-bot-badge"><i class="fas fa-robot"></i> Assistant IA</div>` : '';
  const meta  = isMe
    ? `<span>${msg.time}</span>`
    : `<span>${isBot ? 'Assistant' : 'Conseiller'} · ${msg.time}</span>`;

  bw.innerHTML = `${badge}<div class="sc-bubble">${inner}</div><div class="sc-meta">${meta}</div>`;
  wrap.appendChild(bw);
}

function scEsc(s) {
  return s
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/\n/g,'<br>');
}

/* ── Envoi ── */
async function scSend() {
  const input   = document.getElementById('scInput');
  const sendBtn = document.getElementById('scSendBtn');
  const body    = input.value.trim();
  if (!body && !scPendingFile) return;

  // Désactiver le bouton pendant l'envoi
  sendBtn.disabled = true;
  sendBtn.style.opacity = '.4';

  const fd = new FormData();
  fd.append('_token', SC_CSRF);
  if (body)          fd.append('body', body);
  if (scPendingFile) fd.append('file', scPendingFile);

  input.value = '';
  input.style.height = 'auto';
  scClearPreview();
  scUpdateSend();

  try {
    const r = await fetch(SC_SEND_URL, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: fd
    });

    if (!r.ok) {
      // Afficher un message d'erreur inline
      scShowError('Erreur ' + r.status + ' — réessayez dans un instant.');
      return;
    }

    const d = await r.json();
    if (d.message) {
      scRenderBubble(d.message, true);
      scScrollBottom();
    }
  } catch(e) {
    scShowError('Impossible d\'envoyer le message. Vérifiez votre connexion.');
  } finally {
    sendBtn.disabled = false;
    scUpdateSend();
  }
}

function scShowError(text) {
  const wrap = document.getElementById('scBubbles');
  const el   = document.createElement('div');
  el.style.cssText = 'text-align:center;font-size:.7rem;color:#f87171;padding:.4rem 0;opacity:.8';
  el.textContent = text;
  wrap.appendChild(el);
  scScrollBottom();
  setTimeout(() => el.remove(), 5000);
}

/* ── Fichier image ── */
document.getElementById('scFileInput').addEventListener('change', function () {
  const f = this.files[0];
  if (!f) return;
  scPendingFile = f;
  const url   = URL.createObjectURL(f);
  const inner = document.getElementById('scPreviewInner');
  inner.innerHTML = `
    <img src="${url}" style="max-height:72px;border-radius:8px;display:block">
    <button class="sc-preview__rm" onclick="scClearPreview()"><i class="fas fa-times"></i></button>`;
  document.getElementById('scPreview').style.display = 'block';
  scUpdateSend();
  this.value = '';
});

function scClearPreview() {
  scPendingFile = null;
  document.getElementById('scPreviewInner').innerHTML = '';
  document.getElementById('scPreview').style.display = 'none';
  scUpdateSend();
}

function scUpdateSend() {
  const has = document.getElementById('scInput').value.trim() || scPendingFile;
  document.getElementById('scSendBtn').classList.toggle('active', !!has);
}

function scScrollBottom() {
  const el = document.getElementById('scMsgs');
  requestAnimationFrame(() => { el.scrollTop = el.scrollHeight; });
}

/* Entrée = envoi, Maj+Entrée = saut de ligne */
document.getElementById('scInput').addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); scSend(); }
});

/* ── Lightbox ── */
function scOpenLightbox(src) {
  document.getElementById('scLightboxImg').src = src;
  document.getElementById('scLightbox').classList.add('open');
}
function scCloseLightbox() {
  document.getElementById('scLightbox').classList.remove('open');
}

/* Arrêt/reprise du polling selon visibilité */
document.addEventListener('visibilitychange', () => {
  if (document.hidden) clearInterval(scPollTimer);
  else scStartPolling();
});

/* ── Visual Viewport API — fix clavier mobile (iOS Safari) ──────────────
   iOS Safari ne réduit pas window.innerHeight quand le clavier apparaît ;
   il utilise visualViewport.height. On recalcule la hauteur du sc-wrap
   pour que la barre de saisie reste toujours visible au-dessus du clavier. */
(function () {
  const vv   = window.visualViewport;
  const wrap = document.getElementById('scWrap');
  const bar  = document.querySelector('.sc-bar');
  if (!vv || !wrap) return;

  function scFitToViewport() {
    const topbar = document.querySelector('.sc-topbar');
    const topH   = topbar ? topbar.getBoundingClientRect().height : 62;
    /* vv.offsetTop = défilement vertical du viewport (iOS fait monter la page) */
    const wrapH  = vv.height - topH;
    wrap.style.height = Math.max(120, wrapH) + 'px';
    /* Repositionner si iOS a scrollé le contenu vers le haut */
    wrap.style.top = (vv.offsetTop + topH) + 'px';
    /* Scroll auto vers le bas pour que le dernier message reste visible */
    const msgs = document.getElementById('scMsgs');
    if (msgs) requestAnimationFrame(() => { msgs.scrollTop = msgs.scrollHeight; });
  }

  vv.addEventListener('resize', scFitToViewport);
  vv.addEventListener('scroll', scFitToViewport);
  /* Recalcul initial (au cas où la page s'ouvre clavier ouvert) */
  scFitToViewport();
})();
</script>
@endsection
