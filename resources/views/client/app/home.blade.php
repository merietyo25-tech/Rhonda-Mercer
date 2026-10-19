@extends('layouts.client-app')
@section('title', __('app.title'))

{{-- ── Custom topbar ─────────────────────────────────────────────── --}}
@section('topbar')
<header class="ca-topbar ca-topbar--home h-topbar">
  {{-- Left: avatar + greeting --}}
  <div class="h-header__left">
    <div class="h-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
    <div>
      <div class="h-greeting">{{ __('app.welcome_back') }}</div>
      <div class="h-name">{{ Str::words($user->name, 2, '') }}</div>
    </div>
  </div>

  {{-- Right: lang + bell --}}
  <div class="h-header__actions">
    <a href="{{ route('client.app.notifications') }}" class="h-topbtn" style="position:relative;text-decoration:none" aria-label="{{ __('app.notifications_title') }}">
      <i class="fas fa-bell"></i>
      <span class="h-notif-dot" id="notif-dot" style="{{ ($unreadCount ?? 0) > 0 ? '' : 'display:none' }}"></span>
    </a>
  </div>
  @include('partials.client-chip')
</header>
@endsection

@push('styles')
<style>
/* ── Topbar home ── */
.h-topbar{
  padding: .875rem 1.25rem !important;
  padding-top: calc(.875rem + env(safe-area-inset-top,0px)) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  min-height: unset !important;
}
.h-header__left  { display:flex;align-items:center;gap:.75rem }
.h-header__actions{ display:flex;gap:.5rem }
.h-avatar{
  width:46px;height:46px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--ca-navy-light),#312B81);
  border:2.5px solid rgba(63,203,146,.45);
  box-shadow:0 0 0 4px rgba(63,203,146,.1);
  display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:1.1rem;color:#fff;
}
.h-greeting{ font-size:.7rem;color:var(--ca-text-3);margin-bottom:.05rem }
.h-name{ font-size:.98rem;font-weight:700;color:var(--ca-text) }
.h-topbtn{
  width:40px;height:40px;border-radius:50%;
  background:var(--ca-bg3);border:1px solid var(--ca-border);
  display:flex;align-items:center;justify-content:center;
  color:var(--ca-text-2);font-size:.88rem;cursor:pointer;
  transition:var(--ca-transition);
}
.h-topbtn:hover{ background:var(--ca-bg4);color:var(--ca-text) }
.h-notif-dot{
  position:absolute;top:8px;right:8px;
  width:8px;height:8px;border-radius:50%;
  background:var(--ca-negative);
  border:2px solid var(--ca-bg);
}

/* ── Balance card ── */
.h-card{
  margin:.625rem 1.25rem 0;
  border-radius:24px;
  background:linear-gradient(145deg,#3A3590 0%,#231F5F 45%,#1A1750 100%);
  --ca-accent-l:#D9F2E7;
  padding:1.375rem 1.5rem 1.25rem;
  position:relative;overflow:hidden;
  box-shadow:0 20px 56px rgba(49,43,129,.4),0 0 0 1px rgba(255,255,255,.07);
}
.h-card::before{
  content:'';position:absolute;top:-80px;right:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle,rgba(63,203,146,.12) 0%,transparent 65%);
  pointer-events:none;
}
.h-card::after{
  content:'';position:absolute;bottom:-80px;left:-50px;
  width:220px;height:220px;border-radius:50%;
  background:radial-gradient(circle,rgba(63,203,146,.09) 0%,transparent 65%);
  pointer-events:none;
}
/* Card top row */
.h-card__top{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:1.125rem;
}
.h-card__brand{
  font-family:'Outfit',sans-serif;
  font-size:.65rem;font-weight:800;
  letter-spacing:.14em;text-transform:uppercase;
  color:rgba(255,255,255,.5);
}
.h-card__chip{
  width:34px;height:26px;border-radius:5px;
  background:linear-gradient(135deg,#D9F2E7,#3FCB92,#2B94F7);
  box-shadow:0 2px 8px rgba(0,0,0,.35);
  position:relative;overflow:hidden;
}
.h-card__chip::before{
  content:'';position:absolute;top:50%;left:0;right:0;
  height:1px;background:rgba(0,0,0,.2);transform:translateY(-50%);
}
.h-card__chip::after{
  content:'';position:absolute;left:50%;top:0;bottom:0;
  width:1px;background:rgba(0,0,0,.18);transform:translateX(-50%);
}
.h-pending{
  display:inline-flex;align-items:center;gap:.45rem;margin:.55rem 0 .1rem;padding:.3rem .7rem;
  border-radius:999px;background:rgba(245,158,11,.16);border:1px solid rgba(245,158,11,.4);
  color:#FDE3A7;font-size:.72rem;font-weight:600;
}
.h-pending strong{ color:#fff;font-weight:800 }
.h-pending i{ font-size:.68rem }
/* Balance */
.h-balance-row{
  display:flex;align-items:center;gap:.625rem;
  margin-bottom:.3rem;
}
.h-balance-lbl{
  font-size:.63rem;font-weight:600;text-transform:uppercase;
  letter-spacing:.1em;color:rgba(255,255,255,.42);
}
.h-balance-line{ display:flex;align-items:center;justify-content:space-between;gap:.75rem }
.h-balance-line .h-balance, .h-balance-line .h-balance--hidden{ margin-bottom:1.125rem;min-width:0 }
.h-eye{
  flex-shrink:0;width:42px;height:42px;margin-bottom:1.125rem;border-radius:50%;
  background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
  color:rgba(255,255,255,.85);cursor:pointer;font-size:.9rem;
  display:inline-flex;align-items:center;justify-content:center;transition:background .18s,color .18s;
}
.h-eye:hover{ background:rgba(255,255,255,.22);color:#fff }
.h-balance{
  font-family:'Outfit',sans-serif;
  font-size:2.125rem;font-weight:800;
  color:#fff;letter-spacing:-.03em;line-height:1;
  margin-bottom:1.125rem;
}
.h-balance sup{
  font-size:.95rem;font-weight:600;
  vertical-align:super;margin-right:.2rem;
  color:var(--ca-accent-l);
}
.h-balance--hidden{
  font-size:1.5rem;letter-spacing:.35em;
  color:rgba(255,255,255,.28);margin-bottom:1.125rem;
}
/* Card bottom */
.h-card__bottom{
  display:flex;align-items:flex-end;justify-content:space-between;
  position:relative;z-index:1;
}
.h-card__name{
  font-family:'Outfit',sans-serif;
  font-size:.78rem;font-weight:700;
  color:rgba(255,255,255,.75);
  text-transform:uppercase;letter-spacing:.06em;
  margin-bottom:.18rem;
}
.h-card__num{
  font-family:monospace;font-size:.7rem;
  color:rgba(255,255,255,.62);letter-spacing:.08em;
  overflow-wrap:anywhere;line-height:1.5;
}
.h-card__num--sub{ font-size:.64rem;color:rgba(255,255,255,.42);letter-spacing:.06em }
.h-card__badge{
  background:rgba(63,203,146,.18);
  border:1px solid rgba(63,203,146,.38);
  border-radius:999px;
  padding:.28rem .75rem;
  font-size:.65rem;font-weight:700;
  color:var(--ca-accent-l);letter-spacing:.05em;
  display:flex;align-items:center;gap:.3rem;
}
/* Logo du réseau (Visa / Mastercard) selon la carte attribuée */
.h-card__right{ display:flex;flex-direction:column;align-items:flex-end;gap:.4rem;flex-shrink:0 }
.h-card__net{
  font-size:2.3rem;line-height:1;color:rgba(255,255,255,.92);
  pointer-events:none;
}

/* ── Quick actions ── */
.h-actions{ padding:1rem 1.25rem .25rem }
.h-send{
  display:flex;align-items:center;justify-content:center;gap:.65rem;min-height:54px;border-radius:999px;
  background:linear-gradient(135deg,#3FCB92,#008457);color:#fff;font-weight:800;font-size:.95rem;
  box-shadow:0 10px 26px rgba(0,132,87,.3);transition:transform .15s,box-shadow .2s;
}
.h-send:active{ transform:scale(.98) }
.h-loan{
  display:flex;align-items:center;gap:.7rem;margin-top:.7rem;min-height:52px;padding:.6rem 1.1rem;border-radius:16px;
  background:var(--ca-bg2);border:1px solid var(--ca-border);color:var(--ca-text);font-weight:700;font-size:.88rem;
  transition:border-color .2s,transform .15s;
}
.h-loan i:first-child{ width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(0,132,87,.12);color:var(--ca-accent-l,#008457);font-size:.85rem }
.h-loan:hover{ border-color:var(--ca-accent) }
.h-action__ico{ width:64px !important;height:64px !important;font-size:1.3rem !important }
@media (min-width:768px){
  .h-avatar{ display:none }
  .h-actions{ display:none }
}
.h-action__lbl{ font-size:.8rem !important }
.h-action{
  display:flex;flex-direction:column;align-items:center;
  gap:.5rem;text-decoration:none;cursor:pointer;
}
.h-action__ico{
  width:56px;height:56px;border-radius:18px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;
  transition:transform .14s,box-shadow .14s;
}
.h-action:active .h-action__ico{ transform:scale(.91) }
.h-action__ico--teal  { background:rgba(63,203,146,.2);  border:1px solid rgba(63,203,146,.35);  color:var(--ca-accent-l);    box-shadow:0 4px 14px rgba(63,203,146,.18) }
.h-action__ico--green { background:rgba(0,200,150,.15);  border:1px solid rgba(0,200,150,.3);    color:var(--ca-positive);  box-shadow:0 4px 14px rgba(0,200,150,.16) }
.h-action__ico--blue  { background:rgba(74,158,255,.15); border:1px solid rgba(74,158,255,.3);   color:var(--ca-blue);      box-shadow:0 4px 14px rgba(74,158,255,.14) }
.h-action__ico--purple{ background:rgba(139,92,246,.15); border:1px solid rgba(139,92,246,.3);   color:var(--ca-purple);    box-shadow:0 4px 14px rgba(139,92,246,.14) }
.h-action__lbl{
  font-size:.68rem;font-weight:600;
  color:var(--ca-text-2);text-align:center;line-height:1.2;
}

/* ── Stats mini-cards ── */
.h-stats{
  display:grid;grid-template-columns:repeat(3,1fr);
  gap:.625rem;
  padding:.875rem 1.25rem 0;
}
.h-stat{
  background:var(--ca-bg2);
  border:1px solid var(--ca-border);
  border-radius:16px;
  padding:.875rem .75rem .75rem;
  text-align:center;position:relative;overflow:hidden;
}
.h-stat::before{
  content:'';position:absolute;top:0;left:0;right:0;
  height:3px;border-radius:16px 16px 0 0;
}
.h-stat--def::before { background:linear-gradient(90deg,var(--ca-text-3),var(--ca-bg4)) }
.h-stat--teal::before{ background:linear-gradient(90deg,var(--ca-accent-l),#2B94F7) }
.h-stat--amb::before { background:linear-gradient(90deg,var(--ca-amber),#C87800) }
.h-stat__num{
  font-family:'Outfit',sans-serif;
  font-size:1.75rem;font-weight:800;
  line-height:1;margin-bottom:.3rem;
}
.h-stat--def  .h-stat__num{ color:var(--ca-text) }
.h-stat--teal .h-stat__num{ color:var(--ca-accent-l) }
.h-stat--amb  .h-stat__num{ color:var(--ca-amber) }
.h-stat__lbl{
  font-size:.63rem;font-weight:600;
  text-transform:uppercase;letter-spacing:.07em;
  color:var(--ca-text-3);
}

/* ── Section header ── */
.h-section{
  display:flex;align-items:center;justify-content:space-between;
  padding:1.25rem 1.25rem .625rem;
}
.h-section__title{ font-size:.85rem;font-weight:700;color:var(--ca-text) }
.h-section__link{
  font-size:.75rem;font-weight:600;color:var(--ca-accent-l);
  display:inline-flex;align-items:center;gap:.3rem;
  transition:opacity .18s;
}
.h-section__link:hover{ opacity:.75 }

/* ── Transaction cards ── */
.h-txn-list{ padding:0 1.25rem;display:flex;flex-direction:column;gap:.5rem }
.h-txn{
  display:flex;align-items:center;gap:.875rem;
  background:var(--ca-bg2);
  border:1px solid var(--ca-border);
  border-radius:16px;
  padding:.875rem 1rem;
  text-decoration:none;
  transition:background var(--ca-transition),transform .1s;
}
.h-txn:active{ transform:scale(.99) }
.h-txn:hover { background:var(--ca-bg3) }
.h-txn__ico{
  width:44px;height:44px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.95rem;flex-shrink:0;
}
.h-txn__info{ flex:1;min-width:0 }
.h-txn__title{
  font-size:.875rem;font-weight:600;color:var(--ca-text);
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.h-txn__sub{
  font-size:.72rem;color:var(--ca-text-3);
  margin-top:.18rem;display:flex;align-items:center;gap:.35rem;
}
.h-txn__right{ text-align:right;flex-shrink:0 }
.h-txn__amount{
  font-family:'Outfit',sans-serif;
  font-size:.95rem;font-weight:700;
}
.h-txn__amount--pos{ color:var(--ca-positive) }
.h-txn__amount--neg{ color:var(--ca-negative) }
.h-txn__amount--neu{ color:var(--ca-text) }
.h-txn__date{ font-size:.65rem;color:var(--ca-text-3);margin-top:.18rem }

/* ── Empty state ── */
.h-empty{
  display:flex;flex-direction:column;align-items:center;
  padding:2rem 1rem;text-align:center;
}
.h-empty__ico{
  width:58px;height:58px;border-radius:50%;
  background:var(--ca-bg3);border:1px solid var(--ca-border);
  display:flex;align-items:center;justify-content:center;
  font-size:1.25rem;color:var(--ca-text-3);margin-bottom:.875rem;
}
.h-empty__title{ font-size:.875rem;font-weight:600;color:var(--ca-text-2);margin-bottom:.35rem }
.h-empty__sub  { font-size:.75rem;color:var(--ca-text-3) }

/* ── Pending alert banner ── */
.h-alert{
  margin:.25rem 1.25rem 0;
  background:rgba(245,158,11,.08);
  border:1px solid rgba(245,158,11,.22);
  border-left:3px solid var(--ca-amber);
  border-radius:14px;
  padding:.75rem 1rem;
  display:flex;align-items:center;gap:.625rem;
}
.h-alert i{ color:var(--ca-amber);font-size:.9rem;flex-shrink:0 }
.h-alert__text{ font-size:.78rem;color:rgba(255,255,255,.75);line-height:1.5 }
.h-alert__text strong{ color:var(--ca-amber);font-weight:700 }

/* ── Stagger entrance animations ── */
@keyframes hIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.h-card     { animation:hIn .4s ease .05s both }
.h-actions  { animation:hIn .4s ease .12s both }
.h-stats    { animation:hIn .4s ease .18s both }
.h-section  { animation:hIn .4s ease .22s both }
.h-txn-list { animation:hIn .4s ease .26s both }
</style>
@endpush

@section('main_class', 'ca-main--home')
@section('content')
<div class="h-page" style="padding-bottom:1.5rem">

{{-- ── Balance card ─────────────────────────────────────────────── --}}
@php
  $bankAccount = $user->bankAccount;
  $card        = $user->card;
  $ibanClear   = $bankAccount ? chunk_split((string) $bankAccount->iban, 4, ' ') : null;
@endphp
<div class="h-card" x-data="{ shown: true }">


  {{-- Top row: brand + chip --}}
  <div class="h-card__top">
    <div class="h-card__brand">
      <i class="fas fa-landmark" style="font-size:.6rem;margin-right:.3rem"></i>
     {{ __('app.account_num') }}
    </div>
    @if($card)
    <div class="h-card__net" aria-label="{{ ucfirst($card->network) }}"><i class="fa-brands {{ strtolower($card->network) === 'visa' ? 'fa-cc-visa' : 'fa-cc-mastercard' }}"></i></div>
    @endif
  </div>

  {{-- Balance label + toggle --}}
  <div class="h-balance-row">
    <span class="h-balance-lbl">{{ __('app.balance') }}</span>
  </div>

  {{-- Amount + bouton masquer/afficher, à droite du montant --}}
  <div class="h-balance-line">
    <div class="h-balance" x-show="shown" x-transition>
      <sup>{{ $user->currency ?? \App\Models\Currency::default() }}</sup>{{ number_format((float)$user->balance, 2, ',', ' ') }}
    </div>
    <div class="h-balance--hidden" x-show="!shown" aria-hidden="true">
      &bull;&bull;&bull;&bull;&bull;&bull;
    </div>
    <button type="button" class="h-eye" @click="shown = !shown" :aria-label="shown ? 'Masquer' : 'Afficher'" :aria-pressed="(!shown).toString()">
      <i :class="shown ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
    </button>
  </div>

  {{-- Solde en attente : il rejoint le solde dès sa validation par l'administration --}}
  @if(($pendingBalance ?? 0) > 0)
  <div class="h-pending" title="{{ __('app.pending_balance_hint') }}">
    <i class="fas fa-hourglass-half" aria-hidden="true"></i>
    <span>{{ __('app.pending_balance') }}</span>
    <strong x-show="shown">+{{ number_format($pendingBalance, 2, ',', ' ') }} {{ $user->currency ?? \App\Models\Currency::default() }}</strong>
    <strong x-show="!shown" style="display:none">&bull;&bull;&bull;&bull;</strong>
  </div>
  @endif

  {{-- Bas de carte : IBAN uniquement (nom, BIC et devise sont dans les autres écrans) --}}
  <div class="h-card__bottom">
    <div>
      @if($bankAccount)
      <div class="h-card__num" x-show="shown">{{ trim($ibanClear) }}</div>
      <div class="h-card__num" x-show="!shown" style="display:none">{{ $bankAccount->maskedIban() }}</div>
      @else
      <div class="h-card__num">{{ __('app.not_configured') }}</div>
      @endif
    </div>
  </div>
</div>

{{-- ── Quick Actions ─────────────────────────────────────────────── --}}
<div class="h-actions">
  <a href="{{ route('client.app.transfer.send') }}" class="h-send">
    <i class="fas fa-paper-plane"></i> {{ __('app.action_send') }}
  </a>
  <a href="{{ route('client.app.loans') }}" class="h-loan">
    <i class="fas fa-hand-holding-dollar"></i> {{ __('loans.title') }} <i class="fas fa-chevron-right" style="margin-left:auto;font-size:.65rem;opacity:.6"></i>
  </a>
</div>

{{-- ── Recent activity ───────────────────────────────────────────── --}}
<div class="h-section">
  <span class="h-section__title">{{ __('app.recent_transactions') }}</span>
  <a href="{{ route('client.app.movements') }}" class="h-section__link">
    {{ __('app.see_all') }} <i class="fas fa-chevron-right" style="font-size:.6rem"></i>
  </a>
</div>

<div class="h-txn-list">
  @forelse($recentActivity->take(5) as $mv)
  @php
    $isCredit  = $mv->type === 'credit';
    $isPending = in_array($mv->status, ['pending', 'fee_required']);
    $isFeeDue  = $mv->status === 'fee_required';
    $isRejected= $mv->status === 'rejected';

    if ($isPending) {
        $icoStyle = 'background:rgba(245,158,11,.12)';
        $icoColor = 'color:#f59e0b';
        $icoIcon  = 'fa-clock';
        $amtCls   = 'h-txn__amount--neu';
        $prefix   = $isCredit ? '+' : '-';
    } elseif ($isRejected) {
        $icoStyle = 'background:rgba(148,163,184,.12)';
        $icoColor = 'color:#94a3b8';
        $icoIcon  = 'fa-ban';
        $amtCls   = 'h-txn__amount--neu';
        $prefix   = '';
    } elseif ($isCredit) {
        $icoStyle = 'background:rgba(74,222,128,.12)';
        $icoColor = 'color:#4ade80';
        $icoIcon  = 'fa-arrow-down';
        $amtCls   = 'h-txn__amount--pos';
        $prefix   = '+';
    } else {
        $icoStyle = 'background:rgba(255,90,90,.1)';
        $icoColor = 'color:var(--ca-negative)';
        $icoIcon  = 'fa-arrow-up';
        $amtCls   = 'h-txn__amount--neg';
        $prefix   = '-';
    }
  @endphp
  <a href="{{ ! empty($mv->transfer_ref) ? route('client.app.transfer.show', $mv->transfer_ref) : route('client.app.movements') }}" class="h-txn">
    <div class="h-txn__ico" style="{{ $icoStyle }}">
      <i class="fas {{ $icoIcon }}" style="{{ $icoColor }}"></i>
    </div>
    <div class="h-txn__info">
      <div class="h-txn__title">{{ $mv->label }}</div>
      @if($mv->sub)
      <div class="h-txn__sub">{{ Str::limit($mv->sub, 38) }}
        @if($isFeeDue)
          &nbsp;<span style="font-size:.58rem;background:rgba(96,165,250,.18);color:#60a5fa;padding:.1rem .38rem;border-radius:999px;font-weight:700;white-space:nowrap">{{ __('app.mv_status_fee') }}</span>
        @elseif($isPending)
          &nbsp;<span style="font-size:.58rem;background:rgba(245,158,11,.18);color:#f59e0b;padding:.1rem .38rem;border-radius:999px;font-weight:700;white-space:nowrap">{{ __('app.mv_status_pending') }}</span>
        @elseif($isRejected)
          &nbsp;<span style="font-size:.58rem;background:rgba(148,163,184,.18);color:#94a3b8;padding:.1rem .38rem;border-radius:999px;font-weight:700;white-space:nowrap">{{ __('app.mv_status_rejected') }}</span>
        @endif
      </div>
      @endif
    </div>
    <div class="h-txn__right">
      <div class="h-txn__amount {{ $amtCls }}" style="{{ $isRejected ? 'text-decoration:line-through;opacity:.55' : '' }}">
        {{ $prefix }}{{ number_format($mv->amount, 2, ',', ' ') }}
      </div>
      <div class="h-txn__date">{{ $mv->created_at?->format('d/m · H:i') }}</div>
    </div>
  </a>
  @empty
  <div class="h-empty">
    <div class="h-empty__ico"><i class="fas fa-receipt"></i></div>
    <div class="h-empty__title">{{ __('app.no_activity') }}</div>
    <div class="h-empty__sub">{{ __('app.no_activity_hint') }}</div>
  </div>
  @endforelse
</div>
</div>
@endsection
