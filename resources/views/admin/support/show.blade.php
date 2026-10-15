@extends('layouts.dashboard')
@section('title', 'Support — ' . $client->name . ' — ' . site_name())
@section('page_title', 'Support')

@push('styles')
<style>
/* ══════════════════════════════════════════
   ADMIN SUPPORT SHOW — préfixe adsp-
   Layout : sidebar 300px | chat flex-1
   ══════════════════════════════════════════ */

.adsp-shell {
  display: flex;
  height: calc(100vh - 130px);
  min-height: 500px;
  border: 1px solid var(--c-border);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,.06);
}

/* ── Sidebar ── */
.adsp-side {
  width: 300px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  background: var(--c-surface);
  border-right: 1px solid var(--c-border);
}
.adsp-side-hdr {
  padding: 1rem 1.125rem .75rem;
  border-bottom: 1px solid var(--c-border);
  flex-shrink: 0;
}
.adsp-side-title {
  font-size: .9rem;
  font-weight: 800;
  color: var(--c-navy);
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .625rem;
}
.adsp-unread-pill {
  font-size: .6rem;
  font-weight: 900;
  padding: .15rem .5rem;
  border-radius: 999px;
  background: var(--c-accent);
  color: var(--c-navy);
}
.adsp-search { position: relative; }
.adsp-search i {
  position: absolute;
  left: .7rem; top: 50%;
  transform: translateY(-50%);
  font-size: .72rem;
  color: var(--c-muted);
  pointer-events: none;
}
.adsp-search input {
  width: 100%;
  padding: .5rem .75rem .5rem 2.1rem;
  border: 1.5px solid var(--c-border);
  border-radius: 8px;
  font-size: .8rem;
  color: var(--c-text);
  background: var(--c-bg);
  outline: none;
  font-family: inherit;
  transition: border-color .15s;
}
.adsp-search input:focus { border-color: var(--c-accent); }
.adsp-search input::placeholder { color: var(--c-muted); }

.adsp-filters {
  display: flex;
  gap: .35rem;
  padding: .625rem 1.125rem;
  border-bottom: 1px solid var(--c-border);
  flex-shrink: 0;
}
.adsp-filter-btn {
  flex: 1;
  padding: .35rem .5rem;
  font-size: .7rem;
  font-weight: 700;
  border-radius: 7px;
  border: 1.5px solid var(--c-border);
  background: transparent;
  color: var(--c-muted);
  cursor: pointer;
  transition: .15s;
  font-family: inherit;
}
.adsp-filter-btn.active {
  background: var(--c-navy);
  border-color: var(--c-navy);
  color: #fff;
}

.adsp-side-list { flex: 1; overflow-y: auto; }
.adsp-side-list::-webkit-scrollbar { width: 3px; }
.adsp-side-list::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 99px; }

.adsp-conv {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .875rem 1.125rem;
  border-bottom: 1px solid var(--c-border);
  text-decoration: none;
  transition: background .12s;
  cursor: pointer;
  position: relative;
}
.adsp-conv:last-child { border-bottom: 0; }
.adsp-conv:hover { background: rgba(0,0,0,.025); }
.adsp-conv.is-unread { background: rgba(0,132,87,.04); }
.adsp-conv.is-active {
  background: rgba(27,58,141,.06);
  border-left: 3px solid var(--c-navy);
  padding-left: calc(1.125rem - 3px);
}
.adsp-conv.is-active:hover { background: rgba(27,58,141,.09); }
.adsp-conv-avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--c-navy), #3A3590);
  color: var(--c-accent);
  font-weight: 900; font-size: .875rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; position: relative;
}
.adsp-conv-dot {
  position: absolute; bottom: 1px; right: 1px;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--c-accent);
  border: 2px solid var(--c-surface);
}
.adsp-conv-body { flex: 1; min-width: 0; }
.adsp-conv-name {
  font-size: .8rem; font-weight: 600; color: var(--c-navy);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.adsp-conv.is-unread .adsp-conv-name { font-weight: 800; }
.adsp-conv-preview {
  font-size: .7rem; color: var(--c-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  margin-top: .1rem;
}
.adsp-conv.is-unread .adsp-conv-preview { color: var(--c-text); font-weight: 500; }
.adsp-conv-meta { display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; flex-shrink: 0; }
.adsp-conv-time { font-size: .62rem; color: var(--c-muted); white-space: nowrap; }
.adsp-conv-badge {
  min-width: 18px; height: 18px; padding: 0 5px;
  border-radius: 999px;
  background: var(--c-accent); color: var(--c-navy);
  font-size: .6rem; font-weight: 900;
  display: flex; align-items: center; justify-content: center;
}

/* ── Main chat panel ── */
.adsp-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--c-bg);
  overflow: hidden;
}

/* Chat header */
.adsp-chat-hdr {
  display: flex;
  align-items: center;
  gap: .875rem;
  padding: .875rem 1.25rem;
  border-bottom: 1px solid var(--c-border);
  background: var(--c-surface);
  flex-shrink: 0;
}
.adsp-hdr-av {
  width: 42px; height: 42px; border-radius: 50%;
  background: linear-gradient(135deg, var(--c-navy), #3A3590);
  color: var(--c-accent); font-weight: 900; font-size: .9rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.adsp-hdr-info { flex: 1; }
.adsp-hdr-name { font-size: .9rem; font-weight: 800; color: var(--c-navy); }
.adsp-hdr-sub {
  font-size: .7rem; color: var(--c-muted);
  display: flex; align-items: center; gap: .4rem; margin-top: .1rem;
}
.adsp-hdr-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block; }
.adsp-hdr-acts { display: flex; gap: .5rem; }
.adsp-hdr-btn {
  width: 32px; height: 32px; border-radius: 8px;
  border: 1.5px solid var(--c-border);
  background: transparent; color: var(--c-muted);
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; cursor: pointer; text-decoration: none; transition: .15s;
}
.adsp-hdr-btn:hover { border-color: var(--c-navy); color: var(--c-navy); background: rgba(27,58,141,.05); }

/* Error strip */
#adspError {
  display: none;
  padding: .45rem .875rem;
  background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px;
  font-size: .75rem; color: #b91c1c;
  margin: .5rem 1.125rem 0;
}

/* Messages */
.adsp-msgs {
  flex: 1; overflow-y: auto;
  padding: 1.25rem 1.375rem;
  display: flex; flex-direction: column; gap: .375rem;
}
.adsp-msgs::-webkit-scrollbar { width: 4px; }
.adsp-msgs::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 99px; }

.adsp-day-sep {
  display: flex; align-items: center; gap: .75rem;
  margin: .625rem 0 .125rem;
}
.adsp-day-sep::before,.adsp-day-sep::after { content:''; flex:1; height:1px; background: var(--c-border); }
.adsp-day-sep span {
  font-size: .6rem; font-weight: 700; color: var(--c-muted);
  white-space: nowrap; text-transform: uppercase; letter-spacing: .07em;
}

.adsp-row { display: flex; align-items: flex-end; gap: .5rem; }
.adsp-row.is-out { flex-direction: row-reverse; }

.adsp-bav {
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg, var(--c-navy), #3A3590);
  color: var(--c-accent); font-weight: 900; font-size: .6rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.adsp-bav.bot { background: linear-gradient(135deg, #7c3aed, #5b21b6); color: #fff; font-size: .65rem; }

.adsp-bub {
  max-width: 62%;
  padding: .5rem .875rem;
  border-radius: 16px;
  font-size: .8125rem; line-height: 1.5;
  word-break: break-word;
}
.adsp-row:not(.is-out) .adsp-bub {
  background: var(--c-surface); border: 1px solid var(--c-border);
  color: var(--c-text); border-bottom-left-radius: 4px;
}
.adsp-row.is-out .adsp-bub {
  background: var(--c-navy); color: #fff;
  border-bottom-right-radius: 4px;
}
.adsp-row.is-bot .adsp-bub {
  background: #ede9fe; border: 1px solid #ddd6fe;
  color: #4c1d95; border-bottom-left-radius: 4px;
}
.adsp-bot-tag {
  font-size: .55rem; font-weight: 700; color: #7c3aed;
  background: #ede9fe; border: 1px solid #ddd6fe; border-radius: 4px;
  padding: .05rem .3rem; display: inline-block; margin-bottom: .25rem;
}
.adsp-bub-img {
  max-width: 220px; border-radius: 10px;
  display: block; cursor: pointer; margin-top: .25rem;
}
.adsp-bub-meta {
  font-size: .575rem; opacity: .6; margin-top: .25rem;
  display: flex; align-items: center; justify-content: flex-end; gap: .3rem;
}
.adsp-row:not(.is-out) .adsp-bub-meta { justify-content: flex-start; }
.adsp-spacer { width: 28px; flex-shrink: 0; }

.adsp-empty-chat {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  opacity: .3; gap: .5rem; padding: 2rem; text-align: center;
}
.adsp-empty-chat i { font-size: 2.5rem; color: var(--c-muted); }
.adsp-empty-chat p { font-size: .8rem; color: var(--c-muted); }

/* Image preview */
#adspFilePreview {
  display: none;
  align-items: center; gap: .5rem;
  padding: .4rem .875rem;
  background: rgba(0,132,87,.08); border-top: 1px solid var(--c-accent);
  font-size: .72rem; color: var(--c-navy);
  flex-shrink: 0;
}
#adspFilePreview img { height: 36px; border-radius: 6px; object-fit: cover; }
#adspFilePreview button {
  margin-left: auto; background: none; border: none;
  color: var(--c-muted); cursor: pointer; font-size: 1rem; line-height: 1;
}

/* Input bar */
.adsp-bar {
  display: flex; align-items: flex-end; gap: .625rem;
  padding: .875rem 1.125rem;
  border-top: 1px solid var(--c-border);
  background: var(--c-surface);
  flex-shrink: 0;
}
.adsp-bar-act {
  width: 36px; height: 36px; border-radius: 9px;
  border: 1.5px solid var(--c-border);
  background: transparent; color: var(--c-muted);
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; cursor: pointer; transition: .15s; flex-shrink: 0;
}
.adsp-bar-act:hover { border-color: var(--c-accent); color: var(--c-accent); }

#adspInput {
  flex: 1;
  padding: .5rem .75rem;
  border: 1.5px solid var(--c-border); border-radius: 10px;
  font-size: .8125rem; font-family: inherit;
  color: var(--c-text); background: var(--c-bg);
  outline: none; resize: none; max-height: 100px;
  line-height: 1.45; transition: border-color .15s;
}
#adspInput:focus { border-color: var(--c-accent); }
#adspInput::placeholder { color: var(--c-muted); }

.adsp-send {
  width: 40px; height: 40px; border-radius: 10px;
  border: none; background: var(--c-navy); color: #fff;
  font-size: .875rem; display: flex; align-items: center; justify-content: center;
  cursor: pointer; flex-shrink: 0; transition: opacity .15s, background .15s;
}
.adsp-send:hover { background: #162d7a; }
.adsp-send:disabled { opacity: .45; cursor: not-allowed; }

/* Lightbox */
.adsp-lbx {
  display: none; position: fixed; inset: 0;
  background: rgba(5,15,35,.92); z-index: 9999;
  align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
}
.adsp-lbx.open { display: flex; }
.adsp-lbx img { max-width: 90vw; max-height: 90vh; border-radius: 12px; }
.adsp-lbx-close {
  position: absolute; top: 18px; right: 18px;
  width: 38px; height: 38px; border-radius: 50%;
  background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.2);
  cursor: pointer; color: #fff; font-size: .9375rem;
  display: flex; align-items: center; justify-content: center;
}

@keyframes adspFadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

@media(max-width: 900px) { .adsp-side { width: 240px; } }
@media(max-width: 640px) {
  .adsp-shell { flex-direction: column; height: calc(100dvh - 120px); }
  .adsp-side { width: 100%; border-right: 0; border-bottom: 1px solid var(--c-border); max-height: 200px; }
  .adsp-main { flex: 1; }
  .adsp-bub  { max-width: 82%; }
}
</style>
@endpush

@section('content')
@php
  $totalUnread = $clients->sum('unread_for_admin');
  $initials    = strtoupper(substr($client->name, 0, 1));
@endphp

<div class="adsp-shell">

  {{-- ── SIDEBAR ── --}}
  <aside class="adsp-side">

    <div class="adsp-side-hdr">
      <div class="adsp-side-title">
        <span>Conversations</span>
        @if($totalUnread > 0)
        <span class="adsp-unread-pill">{{ $totalUnread }}</span>
        @endif
      </div>
      <div class="adsp-search">
        <i class="fas fa-search"></i>
        <input type="text" id="adspSearch" placeholder="Rechercher…" autocomplete="off">
      </div>
    </div>

    <div class="adsp-filters">
      <button class="adsp-filter-btn active" id="adspFilterAll" onclick="adspFilter('all')">Tous</button>
      <button class="adsp-filter-btn" id="adspFilterUnread" onclick="adspFilter('unread')">
        <i class="fas fa-circle" style="font-size:.45rem;color:var(--c-accent)"></i>
        Non lus @if($totalUnread > 0)({{ $totalUnread }})@endif
      </button>
    </div>

    <div class="adsp-side-list" id="adspList">
      @forelse($clients as $c)
      @php
        $last     = $c->last_message;
        $unread   = $c->unread_for_admin;
        $preview  = $last ? \Str::limit($last->body ?: '📎 Image', 50) : '';
        $time     = $last ? $last->created_at->diffForHumans(null, true) : '';
        $isActive = $c->id === $client->id;
      @endphp
      <a href="{{ route('admin.support.show', $c) }}"
         class="adsp-conv {{ $unread ? 'is-unread' : '' }} {{ $isActive ? 'is-active' : '' }}"
         data-name="{{ strtolower($c->name) }}"
         data-unread="{{ $unread && !$isActive ? '1' : '0' }}">

        <div class="adsp-conv-avatar">
          {{ strtoupper(substr($c->name, 0, 1)) }}
          @if($unread && !$isActive)<div class="adsp-conv-dot"></div>@endif
        </div>

        <div class="adsp-conv-body">
          <div class="adsp-conv-name">{{ $c->name }}</div>
          <div class="adsp-conv-preview">
            @if($last && $last->sender_type === 'admin' && !$last->is_bot)
              <span style="color:var(--c-accent-d,#006A45);font-weight:600">Vous : </span>
            @elseif($last && $last->is_bot)
              <span style="color:#7c3aed;font-weight:600">IA : </span>
            @endif
            {{ $preview }}
          </div>
        </div>

        <div class="adsp-conv-meta">
          <span class="adsp-conv-time">{{ $time }}</span>
          @if($unread && !$isActive)
          <span class="adsp-conv-badge">{{ $unread }}</span>
          @endif
        </div>
      </a>
      @empty
      <div style="padding:3rem 1.5rem;text-align:center">
        <i class="fas fa-comments" style="font-size:2rem;color:var(--c-muted);opacity:.25;display:block;margin-bottom:.75rem"></i>
        <p style="font-size:.8rem;color:var(--c-muted)">Aucune conversation.</p>
      </div>
      @endforelse
    </div>

  </aside>

  {{-- ── MAIN CHAT ── --}}
  <div class="adsp-main">

    {{-- Chat header --}}
    <div class="adsp-chat-hdr">
      <div class="adsp-hdr-av">{{ $initials }}</div>
      <div class="adsp-hdr-info">
        <div class="adsp-hdr-name">{{ $client->name }}</div>
        <div class="adsp-hdr-sub">
          <span class="adsp-hdr-dot"></span>
          <span>{{ $client->email }}</span>
        </div>
      </div>
      <div class="adsp-hdr-acts">
        @if(Route::has('admin.users.show'))
        <a href="{{ route('admin.users.show', $client) }}" class="adsp-hdr-btn" title="Voir le profil">
          <i class="fas fa-user"></i>
        </a>
        @endif
        <a href="{{ route('admin.support.index') }}" class="adsp-hdr-btn" title="Toutes les conversations">
          <i class="fas fa-th-list"></i>
        </a>
      </div>
    </div>

    {{-- Error strip --}}
    <div id="adspError"></div>

    {{-- Messages --}}
    <div class="adsp-msgs" id="adspMsgs"></div>

    {{-- File preview --}}
    <div id="adspFilePreview">
      <img id="adspThumb" src="" alt="">
      <span id="adspFName"></span>
      <button type="button" onclick="adspClearFile()" title="Retirer">
        <i class="fas fa-times"></i>
      </button>
    </div>

    {{-- Input bar --}}
    <div class="adsp-bar">
      <button class="adsp-bar-act" type="button"
        onclick="document.getElementById('adspFileIn').click()" title="Joindre une image">
        <i class="fas fa-image"></i>
      </button>
      <input type="file" id="adspFileIn" accept="image/*" style="display:none">

      <textarea id="adspInput" rows="1"
        placeholder="Répondre à {{ $client->name }}…"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();adspSend()}"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>

      <button class="adsp-send" id="adspSendBtn" type="button" onclick="adspSend()">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>

  </div>
</div>

{{-- Lightbox --}}
<div class="adsp-lbx" id="adspLbx" onclick="adspLbxClose()">
  <button class="adsp-lbx-close" onclick="adspLbxClose()"><i class="fas fa-times"></i></button>
  <img id="adspLbxImg" src="" alt="" onclick="event.stopPropagation()">
</div>

@push('scripts')
<script>
(function () {
  const SEND_URL  = @json(route('admin.support.store', $client));
  const POLL_URL  = @json(route('admin.support.poll', $client));
  const CLIENT_NM = @json($client->name);
  const INIT      = @json($initials);
  const CHAT_DATA = @json($chatData);

  let lastId      = {{ $lastId }};
  let pendingFile = null;
  let pollTimer   = null;
  let lastDay     = null;

  const msgs = document.getElementById('adspMsgs');

  /* ── Render all initial messages ── */
  if (CHAT_DATA && CHAT_DATA.length) {
    CHAT_DATA.forEach(m => adspBubble(m, false));
  } else {
    msgs.innerHTML = `
      <div class="adsp-empty-chat">
        <i class="fas fa-comment-dots"></i>
        <p>Aucun message encore.<br>Démarrez l'échange ci-dessous.</p>
      </div>`;
  }
  adspScrollBottom();

  /* ── Bubble builder ── */
  function adspBubble(m, animate) {
    const isOut = m.sender_type === 'admin' && !m.is_bot;
    const isBot = !!m.is_bot;

    const day = m.date_key || '';
    if (day !== lastDay) {
      lastDay = day;
      const sep = document.createElement('div');
      sep.className = 'adsp-day-sep';
      sep.innerHTML = `<span>${m.date_label || day}</span>`;
      msgs.appendChild(sep);
    }

    const row = document.createElement('div');
    row.className = 'adsp-row' + (isOut ? ' is-out' : '') + (isBot ? ' is-bot' : '');
    row.dataset.id = m.id;
    if (animate) row.style.animation = 'adspFadeUp .2s ease';

    const time = m.time || '';

    if (!isOut) {
      const av = document.createElement('div');
      av.className = 'adsp-bav' + (isBot ? ' bot' : '');
      av.textContent = isBot ? '🤖' : INIT;
      row.appendChild(av);
    }

    const bub = document.createElement('div');
    bub.className = 'adsp-bub';

    if (isBot) {
      const tag = document.createElement('div');
      tag.className = 'adsp-bot-tag';
      tag.textContent = 'Assistant IA';
      bub.appendChild(tag);
    }

    if (m.file_type === 'image' && m.file_url) {
      const img = document.createElement('img');
      img.src = m.file_url;
      img.className = 'adsp-bub-img';
      img.loading = 'lazy';
      img.onclick = () => adspLbxOpen(m.file_url);
      bub.appendChild(img);
    }

    if (m.body) {
      const txt = document.createElement('div');
      txt.textContent = m.body;
      bub.appendChild(txt);
    }

    const meta = document.createElement('div');
    meta.className = 'adsp-bub-meta';
    meta.textContent = time;
    if (isOut) meta.innerHTML += ' <i class="fas fa-check" style="font-size:.5rem"></i>';
    bub.appendChild(meta);

    row.appendChild(bub);

    if (isOut) {
      const sp = document.createElement('div');
      sp.className = 'adsp-spacer';
      row.appendChild(sp);
    }

    // remove empty-state placeholder if present
    const emp = msgs.querySelector('.adsp-empty-chat');
    if (emp) msgs.removeChild(emp);

    msgs.appendChild(row);
    if (m.id > lastId) lastId = m.id;
  }

  function adspScrollBottom() {
    requestAnimationFrame(() => { msgs.scrollTop = msgs.scrollHeight; });
  }

  /* ── Send ── */
  window.adspSend = function () {
    const inp  = document.getElementById('adspInput');
    const body = inp.value.trim();
    if (!body && !pendingFile) return;

    const btn = document.getElementById('adspSendBtn');
    btn.disabled = true;

    const fd = new FormData();
    if (body)        fd.append('body', body);
    if (pendingFile) fd.append('file', pendingFile);

    inp.value = '';
    inp.style.height = '';
    adspClearFile();

    fetch(SEND_URL, {
      method : 'POST',
      headers: {
        'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept'          : 'application/json',
      },
      body: fd,
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
      if (!ok) { adspShowError(d.error || 'Erreur lors de l\'envoi.'); return; }
      if (d.message) { adspBubble(d.message, true); adspScrollBottom(); }
    })
    .catch(() => adspShowError('Erreur réseau. Réessayez.'))
    .finally(() => { btn.disabled = false; });
  };

  /* ── File attach ── */
  document.getElementById('adspFileIn').addEventListener('change', function () {
    const f = this.files[0];
    if (!f) return;
    pendingFile = f;
    document.getElementById('adspFName').textContent = f.name;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('adspThumb').src = e.target.result; };
    reader.readAsDataURL(f);
    document.getElementById('adspFilePreview').style.display = 'flex';
    this.value = '';
  });

  window.adspClearFile = function () {
    pendingFile = null;
    document.getElementById('adspFilePreview').style.display = 'none';
    document.getElementById('adspThumb').src = '';
    document.getElementById('adspFName').textContent = '';
  };

  /* ── Error ── */
  function adspShowError(msg) {
    const el = document.getElementById('adspError');
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 6000);
  }

  /* ── Polling ── */
  function adspPoll() {
    fetch(POLL_URL + '?after=' + lastId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (!data || !data.messages || !data.messages.length) return;
      const atBottom = msgs.scrollHeight - msgs.clientHeight - msgs.scrollTop < 80;
      data.messages.forEach(m => {
        if (!msgs.querySelector('[data-id="' + m.id + '"]')) {
          adspBubble(m, true);
        }
      });
      if (atBottom) adspScrollBottom();
    })
    .catch(() => {});
  }

  pollTimer = setInterval(adspPoll, 4000);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) { clearInterval(pollTimer); pollTimer = null; }
    else { pollTimer = setInterval(adspPoll, 4000); }
  });
  window.addEventListener('beforeunload', () => clearInterval(pollTimer));

  /* ── Lightbox ── */
  window.adspLbxOpen = function (src) {
    document.getElementById('adspLbxImg').src = src;
    document.getElementById('adspLbx').classList.add('open');
  };
  window.adspLbxClose = function () {
    document.getElementById('adspLbx').classList.remove('open');
    document.getElementById('adspLbxImg').src = '';
  };
  document.addEventListener('keydown', e => { if (e.key === 'Escape') adspLbxClose(); });

  /* ── Sidebar search / filter ── */
  let _f = 'all';
  document.getElementById('adspSearch').addEventListener('input', function () {
    adspApply(this.value.toLowerCase());
  });
  window.adspFilter = function (f) {
    _f = f;
    document.getElementById('adspFilterAll').classList.toggle('active', f === 'all');
    document.getElementById('adspFilterUnread').classList.toggle('active', f === 'unread');
    adspApply(document.getElementById('adspSearch').value.toLowerCase());
  };
  function adspApply(q) {
    document.querySelectorAll('.adsp-conv').forEach(el => {
      const nm = el.dataset.name.includes(q);
      const ur = _f === 'unread' ? el.dataset.unread === '1' : true;
      el.style.display = (nm && ur) ? '' : 'none';
    });
  }
})();
</script>
@endpush
@endsection
