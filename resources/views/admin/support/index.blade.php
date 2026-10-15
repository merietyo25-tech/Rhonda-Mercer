@extends('layouts.dashboard')
@section('title', 'Support clients — ' . site_name())
@section('page_title', 'Support')

@push('styles')
<style>
@import url('');
/* ══════════════════════════════════════════
   ADMIN SUPPORT — préfixe adsp-
   Layout : sidebar 300px | main flex-1
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

.adsp-search {
  position: relative;
}
.adsp-search i {
  position: absolute;
  left: .7rem;
  top: 50%;
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

.adsp-side-list {
  flex: 1;
  overflow-y: auto;
}
.adsp-side-list::-webkit-scrollbar { width: 3px; }
.adsp-side-list::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 99px; }

/* Conversation item */
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
  background: rgba(27,58,141,.05);
  border-left: 3px solid var(--c-navy);
  padding-left: calc(1.125rem - 3px);
}
.adsp-conv.is-active:hover { background: rgba(27,58,141,.08); }

.adsp-conv-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--c-navy), #3A3590);
  color: var(--c-accent);
  font-weight: 900;
  font-size: .875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
}
.adsp-conv-dot {
  position: absolute;
  bottom: 1px; right: 1px;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--c-accent);
  border: 2px solid var(--c-surface);
}
.adsp-conv-body { flex: 1; min-width: 0; }
.adsp-conv-name {
  font-size: .8rem;
  font-weight: 600;
  color: var(--c-navy);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.adsp-conv.is-unread .adsp-conv-name { font-weight: 800; }
.adsp-conv-preview {
  font-size: .7rem;
  color: var(--c-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: .1rem;
}
.adsp-conv.is-unread .adsp-conv-preview { color: var(--c-text); font-weight: 500; }
.adsp-conv-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: .3rem;
  flex-shrink: 0;
}
.adsp-conv-time {
  font-size: .62rem;
  color: var(--c-muted);
  white-space: nowrap;
}
.adsp-conv-badge {
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 999px;
  background: var(--c-accent);
  color: var(--c-navy);
  font-size: .6rem;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Main panel ── */
.adsp-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--c-bg);
  overflow: hidden;
}

.adsp-empty-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  opacity: .35;
  padding: 2rem;
}
.adsp-empty-main-ico {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: var(--c-surface);
  border: 1.5px solid var(--c-border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.875rem;
  color: var(--c-muted);
  margin: 0 auto 1.25rem;
}
.adsp-empty-main h3 {
  font-size: .9375rem;
  font-weight: 700;
  color: var(--c-navy);
  margin-bottom: .375rem;
}
.adsp-empty-main p {
  font-size: .8125rem;
  color: var(--c-muted);
  line-height: 1.6;
}

.adsp-no-convs {
  padding: 3rem 1.5rem;
  text-align: center;
}
.adsp-no-convs i {
  font-size: 2rem;
  color: var(--c-muted);
  opacity: .25;
  display: block;
  margin-bottom: .75rem;
}
.adsp-no-convs p {
  font-size: .8rem;
  color: var(--c-muted);
}

@media(max-width: 900px) {
  .adsp-side { width: 240px; }
}
@media(max-width: 640px) {
  .adsp-shell { flex-direction: column; height: calc(100dvh - 120px); }
  .adsp-side { width: 100%; border-right: 0; border-bottom: 1px solid var(--c-border); max-height: 260px; }
  .adsp-main { flex: 1; }
}
</style>
@endpush

@section('content')
@php
  $totalUnread = $clients->sum('unread_for_admin');
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
      <button class="adsp-filter-btn active" id="adspFilterAll" onclick="adspFilter('all')">
        Tous
      </button>
      <button class="adsp-filter-btn" id="adspFilterUnread" onclick="adspFilter('unread')">
        <i class="fas fa-circle" style="font-size:.45rem;color:var(--c-accent)"></i>
        Non lus @if($totalUnread > 0)({{ $totalUnread }})@endif
      </button>
    </div>

    <div class="adsp-side-list" id="adspList">
      @forelse($clients as $c)
      @php
        $last    = $c->last_message;
        $unread  = $c->unread_for_admin;
        $preview = $last ? \Str::limit($last->body ?: '📎 Image', 55) : '';
        $time    = $last ? $last->created_at->diffForHumans(null, true) : '';
      @endphp
      <a href="{{ route('admin.support.show', $c) }}"
         class="adsp-conv {{ $unread ? 'is-unread' : '' }}"
         data-name="{{ strtolower($c->name) }}"
         data-unread="{{ $unread ? '1' : '0' }}">

        <div class="adsp-conv-avatar">
          {{ strtoupper(substr($c->name, 0, 1)) }}
          @if($unread)<div class="adsp-conv-dot"></div>@endif
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
          @if($unread)
          <span class="adsp-conv-badge">{{ $unread }}</span>
          @endif
        </div>
      </a>
      @empty
      <div class="adsp-no-convs">
        <i class="fas fa-comments"></i>
        <p>Aucune conversation pour l'instant.</p>
      </div>
      @endforelse
    </div>

  </aside>

  {{-- ── MAIN (vide) ── --}}
  <div class="adsp-main">
    <div class="adsp-empty-main">
      <div class="adsp-empty-main-ico">
        <i class="fas fa-comment-dots"></i>
      </div>
      <h3>Sélectionnez une conversation</h3>
      <p>Choisissez un client dans la liste<br>pour afficher la messagerie.</p>
    </div>
  </div>

</div>

<script>
let _adspFilter = 'all';

document.getElementById('adspSearch').addEventListener('input', function () {
  adspApply(this.value.toLowerCase());
});

function adspFilter(f) {
  _adspFilter = f;
  document.getElementById('adspFilterAll').classList.toggle('active', f === 'all');
  document.getElementById('adspFilterUnread').classList.toggle('active', f === 'unread');
  adspApply(document.getElementById('adspSearch').value.toLowerCase());
}

function adspApply(q) {
  document.querySelectorAll('.adsp-conv').forEach(el => {
    const nm = el.dataset.name.includes(q);
    const ur = _adspFilter === 'unread' ? el.dataset.unread === '1' : true;
    el.style.display = (nm && ur) ? '' : 'none';
  });
}
</script>
@endsection
