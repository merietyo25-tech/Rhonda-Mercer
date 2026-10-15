@extends('layouts.client-app')
@section('title', __('app.notifications_title') . ' — ' . site_name())
@section('page_title', __('app.notifications_title'))
@section('back_btn', true)
@section('back_url', route('client.app.home'))

@section('topbar_action')
@if($notifications->isNotEmpty())
<form method="POST" action="{{ route('client.app.notifications.read-all') }}" id="readAllForm"
      x-data="{ submitting: false }" @submit="submitting = true">
  @csrf
  <button type="submit" :disabled="submitting"
          style="background:none;border:none;font-size:.75rem;font-weight:700;color:var(--ca-teal-l);cursor:pointer;padding:.5rem .25rem;font-family:inherit;letter-spacing:.01em">
    <i class="fas fa-spinner fa-spin" x-show="submitting" x-cloak></i>
    <span x-text="submitting ? '…' : 'Tout lire'"></span>
  </button>
</form>
@endif
@endsection

@push('styles')
<style>
/* ── Filter bar ── */
.nx-filters{
  display:flex;gap:.5rem;padding:.75rem 1.25rem .25rem;overflow-x:auto;
  -webkit-overflow-scrolling:touch;scrollbar-width:none;
}
.nx-filters::-webkit-scrollbar{display:none}
.nx-filter{
  display:inline-flex;align-items:center;gap:.375rem;
  padding:.35rem .875rem;border-radius:999px;
  font-size:.72rem;font-weight:700;white-space:nowrap;
  border:1.5px solid var(--ca-border);
  color:var(--ca-text-3);background:var(--ca-bg2);cursor:pointer;
  transition:.15s;
}
.nx-filter.active,
.nx-filter:hover{
  background:rgba(0,132,87,.12);
  border-color:rgba(0,132,87,.3);
  color:var(--ca-teal-l);
}
.nx-filter__dot{
  width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;
}

/* ── Badge count ── */
.nx-unread-count{
  display:inline-flex;align-items:center;justify-content:center;
  min-width:18px;height:18px;padding:0 5px;
  border-radius:999px;font-size:.6rem;font-weight:800;
  background:var(--ca-teal-l);color:#fff;margin-left:.25rem;
}

/* ── Date separator ── */
.nx-date-sep{
  font-size:.67rem;font-weight:800;text-transform:uppercase;
  letter-spacing:.1em;color:var(--ca-text-3);
  padding:.875rem 1.25rem .375rem;
  display:flex;align-items:center;gap:.625rem;
}
.nx-date-sep::after{
  content:'';flex:1;height:1px;background:var(--ca-border-2);
}

/* ── Notification item ── */
.nx-list{display:flex;flex-direction:column;gap:.375rem;padding:0 1.25rem}

.nx-item{
  display:flex;align-items:flex-start;gap:.875rem;
  background:var(--ca-bg2);
  border:1px solid var(--ca-border);
  border-radius:16px;
  padding:.875rem 1rem;
  position:relative;
  transition:.15s;
  overflow:hidden;
}
.nx-item.nx-unread{
  background:var(--ca-bg3);
  border-color:rgba(0,132,87,.2);
}
.nx-item.nx-unread::before{
  content:'';
  position:absolute;left:0;top:0;bottom:0;
  width:3px;border-radius:0 2px 2px 0;
  background:var(--ca-teal-l);
}

/* ── Icon ── */
.nx-ico{
  width:46px;height:46px;border-radius:14px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:1rem;position:relative;
}
.nx-ico--transfer{background:linear-gradient(145deg,rgba(0,132,87,.25),rgba(0,132,87,.1));color:var(--ca-teal-l)}
.nx-ico--loan    {background:linear-gradient(145deg,rgba(63,203,146,.25),rgba(63,203,146,.1));color:var(--ca-accent-l)}
.nx-ico--credit  {background:linear-gradient(145deg,rgba(74,222,128,.2),rgba(74,222,128,.07));color:#4ade80}
.nx-ico--debit   {background:linear-gradient(145deg,rgba(248,113,113,.2),rgba(248,113,113,.07));color:#f87171}
.nx-ico--system  {background:linear-gradient(145deg,rgba(96,165,250,.2),rgba(96,165,250,.07));color:#60a5fa}

/* ── Unread dot on icon ── */
.nx-ico__dot{
  position:absolute;top:-2px;right:-2px;
  width:10px;height:10px;border-radius:50%;
  background:var(--ca-teal-l);
  border:2px solid var(--ca-bg3);
  box-shadow:0 0 6px rgba(0,132,87,.5);
}

/* ── Body ── */
.nx-body{flex:1;min-width:0}
.nx-title{font-size:.875rem;font-weight:700;color:var(--ca-text);margin-bottom:.2rem;line-height:1.3}
.nx-text{font-size:.78rem;color:var(--ca-text-2);line-height:1.55;margin-bottom:.35rem}
.nx-meta{display:flex;align-items:center;gap:.625rem}
.nx-time{font-size:.67rem;color:var(--ca-text-3);display:flex;align-items:center;gap:.3rem}
.nx-type-pill{
  font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;
  padding:.1rem .45rem;border-radius:999px;
}
.nx-type--transfer{background:rgba(0,132,87,.12);color:var(--ca-teal-l)}
.nx-type--loan    {background:rgba(63,203,146,.12);color:var(--ca-accent-l)}
.nx-type--credit  {background:rgba(74,222,128,.12);color:#4ade80}
.nx-type--debit   {background:rgba(248,113,113,.12);color:#f87171}
.nx-type--system  {background:rgba(96,165,250,.12);color:#60a5fa}

/* ── Empty ── */
.nx-empty{
  display:flex;flex-direction:column;align-items:center;
  padding:4.5rem 2rem 2rem;text-align:center;
}
.nx-empty__ring{
  width:88px;height:88px;border-radius:50%;
  border:2px dashed var(--ca-border);
  display:flex;align-items:center;justify-content:center;
  font-size:2rem;color:var(--ca-text-3);
  margin-bottom:1.25rem;opacity:.5;
}
.nx-empty__title{font-size:1rem;font-weight:700;color:var(--ca-text-2);margin-bottom:.4rem}
.nx-empty__sub{font-size:.8rem;color:var(--ca-text-3);line-height:1.5;max-width:240px}
[x-cloak] { display:none !important }
</style>
@endpush

@section('content')

@php
  $unreadCount = $notifications->whereNull('read_at')->count();
  $iconMap = [
    'transfer'    => ['cls' => 'nx-ico--transfer', 'fa' => 'fas fa-paper-plane',   'pill' => 'nx-type--transfer', 'lbl' => 'Virement'],
    'credit'      => ['cls' => 'nx-ico--credit',   'fa' => 'fas fa-circle-plus',   'pill' => 'nx-type--credit',   'lbl' => 'Crédit'],
    'debit'       => ['cls' => 'nx-ico--debit',    'fa' => 'fas fa-circle-minus',  'pill' => 'nx-type--debit',    'lbl' => 'Débit'],
    'system'      => ['cls' => 'nx-ico--system',   'fa' => 'fas fa-bell',          'pill' => 'nx-type--system',   'lbl' => 'Système'],
  ];
@endphp

@if($notifications->isEmpty())
<div class="nx-empty">
  <div class="nx-empty__ring"><i class="fas fa-bell-slash"></i></div>
  <div class="nx-empty__title">{{ __('app.notifications_empty') }}</div>
  <div class="nx-empty__sub">{{ __('app.notifications_empty_sub') }}</div>
</div>
@else

{{-- Filter pills ── --}}
<div class="nx-filters" x-data="{active:'all'}">
  <button class="nx-filter" :class="active==='all'?'active':''" @click="active='all';filterNotifs('all')">
    <span>Toutes</span>
    <span class="nx-unread-count">{{ $notifications->count() }}</span>
  </button>
  @if($unreadCount > 0)
  <button class="nx-filter" :class="active==='unread'?'active':''" @click="active='unread';filterNotifs('unread')">
    <span class="nx-filter__dot"></span>
    Non lues
    <span class="nx-unread-count">{{ $unreadCount }}</span>
  </button>
  @endif
  @foreach(['credit' => 'Crédits', 'debit' => 'Débits', 'transfer' => 'Virements'] as $type => $label)
    @if($notifications->where('type', $type)->isNotEmpty())
    <button class="nx-filter" :class="active==='{{ $type }}'?'active':''" @click="active='{{ $type }}';filterNotifs('{{ $type }}')">
      {{ $label }}
    </button>
    @endif
  @endforeach
</div>

{{-- List ── --}}
<div id="nx-list-wrap" style="padding-bottom:1.5rem;margin-top:.375rem">
@php $prevDate = null; @endphp
@foreach($notifications as $n)
@php
  $date   = $n->created_at->format('d/m/Y');
  $unread = is_null($n->read_at);
  $map    = $iconMap[$n->type] ?? $iconMap['system'];
  $diff   = $n->created_at->diffInMinutes(now());
  if ($diff < 1)        $timeStr = __('app.notif_just_now');
  elseif ($diff < 60)   $timeStr = str_replace(':n', (int)$diff,         __('app.notif_minutes_ago'));
  elseif ($diff < 1440) $timeStr = str_replace(':n', (int)($diff/60),    __('app.notif_hours_ago'));
  else                  $timeStr = str_replace(':n', (int)($diff/1440),   __('app.notif_days_ago'));
@endphp

@if($date !== $prevDate)
<div class="nx-date-sep" data-filter-sep="{{ $n->type }}" data-unread="{{ $unread ? 'true' : 'false' }}">
  @if($n->created_at->isToday()) Aujourd'hui
  @elseif($n->created_at->isYesterday()) Hier
  @else {{ $n->created_at->isoFormat('dddd D MMMM') }}
  @endif
</div>
@php $prevDate = $date; @endphp
@endif

<div class="nx-list" style="margin-bottom:.375rem">
<div class="nx-item {{ $unread ? 'nx-unread' : '' }}"
     data-type="{{ $n->type }}"
     data-unread="{{ $unread ? 'true' : 'false' }}">
  <div class="nx-ico {{ $map['cls'] }}">
    <i class="{{ $map['fa'] }}"></i>
    @if($unread)<div class="nx-ico__dot"></div>@endif
  </div>
  <div class="nx-body">
    <div class="nx-title">{{ $n->title }}</div>
    <div class="nx-text">{{ $n->body }}</div>
    <div class="nx-meta">
      <div class="nx-time">
        <i class="fas fa-clock" style="font-size:.6rem"></i>
        {{ $timeStr }}
      </div>
      <span class="nx-type-pill {{ $map['pill'] }}">{{ $map['lbl'] }}</span>
    </div>
  </div>
</div>
</div>

@endforeach
</div>

@endif

<div style="height:.5rem"></div>

@push('scripts')
<script>
function filterNotifs(type) {
  document.querySelectorAll('.nx-item').forEach(el => {
    const matchType   = type === 'all'    || el.dataset.type === type;
    const matchUnread = type !== 'unread' || el.dataset.unread === 'true';
    const show = matchType && matchUnread;
    el.closest('.nx-list').style.display = show ? '' : 'none';
  });
  // hide date seps with no visible items
  document.querySelectorAll('.nx-date-sep').forEach(sep => {
    let next = sep.nextElementSibling;
    let hasVisible = false;
    while (next && !next.classList.contains('nx-date-sep')) {
      if (next.classList.contains('nx-list') && next.style.display !== 'none') {
        hasVisible = true; break;
      }
      next = next.nextElementSibling;
    }
    sep.style.display = hasVisible ? '' : 'none';
  });
}
</script>
@endpush

@endsection
