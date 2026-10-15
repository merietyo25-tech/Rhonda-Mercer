@extends('layouts.client-app')
@section('title', __('app.nav_transfer') . ' — ' . site_name())
@section('page_title', __('app.nav_transfer'))
@section('main_class', 'ca-main--narrow')

@section('content')

@push('styles')
<style>
[x-cloak]{display:none !important}
.tx{padding:1rem 1.25rem 0;display:flex;flex-direction:column;gap:1rem}

/* Solde */
.tx-hero{background:linear-gradient(145deg,#3A3590,#231F5F);border-radius:20px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,.35)}
.tx-hero::before{content:'';position:absolute;top:-70px;right:-70px;width:210px;height:210px;border-radius:50%;background:radial-gradient(circle,rgba(0,132,87,.22),transparent 70%)}
.tx-hero__lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5)}
.tx-hero__bal{font-family:'Outfit',sans-serif;font-size:2.1rem;font-weight:900;color:#fff;line-height:1.1;margin-top:.35rem}
.tx-hero__cur{font-size:.9rem;font-weight:600;color:rgba(255,255,255,.55);margin-left:.3rem}
.tx-hero__sub{font-size:.72rem;color:rgba(255,255,255,.4);margin-top:.35rem}

/* Actions */
.tx-actions{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.tx-act{display:flex;align-items:center;gap:.75rem;padding:.9rem 1rem;border-radius:16px;background:var(--ca-bg3);border:1px solid var(--ca-border);text-decoration:none;color:var(--ca-text);font-family:inherit;cursor:pointer;text-align:left;transition:.18s}
.tx-act:active{transform:scale(.98)}
.tx-act:hover{background:var(--ca-bg4)}
.tx-act[disabled]{opacity:.45;cursor:not-allowed}
.tx-act__ico{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;background:linear-gradient(145deg,#D9F2E7,#3FCB92);box-shadow:0 4px 12px rgba(63,203,146,.3)}
.tx-act__name{font-size:.875rem;font-weight:700}
.tx-act__desc{font-size:.68rem;color:var(--ca-text-3);margin-top:.1rem;line-height:1.35}

.tx-alert{padding:.8rem 1rem;border-radius:14px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);font-size:.76rem;color:#f87171;line-height:1.5;display:flex;gap:.6rem}

/* En-tête de liste + filtres */
.tx-head{display:flex;align-items:center;justify-content:space-between}
.tx-head__title{font-size:.9rem;font-weight:700;color:var(--ca-text)}
.tx-head__link{font-size:.75rem;font-weight:600;color:var(--ca-accent-l);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem}
.tx-chips{display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.15rem;scrollbar-width:none}
.tx-chips::-webkit-scrollbar{display:none}
.tx-chip{flex-shrink:0;padding:.4rem .85rem;border-radius:999px;border:1px solid var(--ca-border);background:transparent;color:var(--ca-text-2);font-size:.73rem;font-weight:600;font-family:inherit;cursor:pointer;transition:.15s}
.tx-chip.is-on{background:var(--ca-accent);border-color:var(--ca-accent-l);color:#fff}

/* Liste */
.tx-list{background:var(--ca-bg3);border:1px solid var(--ca-border);border-radius:18px;overflow:hidden}
.tx-row{display:flex;align-items:center;gap:.875rem;padding:.9rem 1.1rem;width:100%;background:none;border:0;border-bottom:1px solid var(--ca-border-2);color:inherit;font-family:inherit;text-align:left;cursor:pointer;transition:.15s}
.tx-row:last-child{border-bottom:0}
.tx-row:hover{background:var(--ca-bg4)}
.tx-ico{width:42px;height:42px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem}
.tx-ico--pending{background:rgba(245,158,11,.12);color:#f59e0b}
.tx-ico--fee{background:rgba(96,165,250,.12);color:#60a5fa}
.tx-ico--done{background:rgba(74,222,128,.12);color:var(--ca-positive)}
.tx-ico--rej{background:rgba(148,163,184,.12);color:#94a3b8}
.tx-row__body{flex:1;min-width:0}
.tx-row__name{font-size:.875rem;font-weight:600;color:var(--ca-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tx-row__sub{font-size:.7rem;color:var(--ca-text-3);margin-top:.15rem}
.tx-row__right{text-align:right;flex-shrink:0}
.tx-row__amt{font-family:'Outfit',sans-serif;font-size:.95rem;font-weight:800;color:var(--ca-negative)}
.tx-row__amt.is-rej{color:var(--ca-text-3);text-decoration:line-through}
.tx-row__st{font-size:.65rem;margin-top:.2rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em}
.tx-st--pending{color:#f59e0b}.tx-st--fee{color:#60a5fa}.tx-st--done{color:var(--ca-positive)}.tx-st--rej{color:#94a3b8}
.tx-none{padding:2rem 1rem;text-align:center;color:var(--ca-text-3);font-size:.82rem}
.tx-none i{display:block;font-size:2rem;opacity:.25;margin-bottom:.6rem}

/* Modales */
.tx-modal{position:fixed;inset:0;z-index:20000;display:flex;align-items:flex-end;justify-content:center;padding:1rem}
.tx-modal__bg{position:absolute;inset:0;background:rgba(2,10,20,.66);backdrop-filter:blur(3px)}
.tx-modal__box{position:relative;width:100%;max-width:420px;background:var(--ca-bg2);color:var(--ca-text);border:1px solid var(--ca-border);border-radius:22px;padding:1.4rem 1.4rem 1.25rem;box-shadow:0 24px 60px rgba(0,0,0,.5);max-height:88vh;overflow-y:auto}
@media (min-width:768px){.tx-modal{align-items:center}}
.tx-modal__x{position:absolute;top:.8rem;right:.8rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--ca-bg4);color:var(--ca-text-2);cursor:pointer}
.tx-modal__title{font-size:1rem;font-weight:800;margin-bottom:1rem;padding-right:2rem}
.tx-big{text-align:center;padding:.4rem 0 1rem}
.tx-big__amt{font-family:'Outfit',sans-serif;font-size:1.9rem;font-weight:900}
.tx-big__who{font-size:.85rem;color:var(--ca-text-2);margin-top:.2rem}
.tx-kv{display:flex;justify-content:space-between;gap:1rem;padding:.7rem 0;border-top:1px solid var(--ca-border-2);font-size:.8rem}
.tx-kv__k{color:var(--ca-text-3);flex-shrink:0}
.tx-kv__v{color:var(--ca-text);font-weight:600;text-align:right;word-break:break-all}
.tx-kv__v--mono{font-family:monospace;font-size:.78rem}
.tx-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;margin-top:1rem;padding:.85rem;border-radius:14px;border:0;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;text-decoration:none;background:linear-gradient(145deg,#D9F2E7,#3FCB92);color:#1a2b3c}
.tx-btn--ghost{background:var(--ca-bg4);color:var(--ca-text);margin-top:.6rem}
</style>
@endpush

@php
  $currency = $user->currency ?? \App\Models\Currency::default();
  $blocked  = $user->hasBlockedAccount();
  $negative = (float) $user->balance < 0 || $blocked;
  $iban     = $user->bankAccount?->iban ?: '';
  $bic      = $user->bankAccount?->bic ?: '—';
  $shareText = "IBAN : {$iban}\nBIC : {$bic}\nTitulaire : {$user->name}";

  $kinds = [
    \App\Models\Transfer::STATUS_PENDING      => ['pending', 'hourglass-half'],
    \App\Models\Transfer::STATUS_FEE_REQUIRED => ['fee', 'file-invoice'],
    \App\Models\Transfer::STATUS_COMPLETED    => ['done', 'check'],
    \App\Models\Transfer::STATUS_REJECTED     => ['rej', 'xmark'],
  ];
  $rows = $transfers->map(function ($t) use ($kinds, $currency) {
    [$k] = $kinds[$t->status] ?? ['pending', 'hourglass-half'];
    return [
      'ref'    => $t->reference,
      'name'   => $t->beneficiary_name ?? '—',
      'iban'   => trim(chunk_split((string) $t->beneficiary_iban, 4, ' ')),
      'amount' => number_format((float) $t->amount, 2, ',', ' ') . ' ' . ($t->currency ?: $currency),
      'date'   => $t->created_at->format('d/m/Y · H:i'),
      'kind'   => $k,
      'status' => __('transfer.status_' . $t->status),
      'url'    => route('client.app.transfer.show', $t->reference),
    ];
  })->values();
@endphp

<div class="tx" x-data="{
  modal: null, sel: null, filter: 'all', copied: false,
  rows: @js($rows),
  open(i){ this.sel = this.rows[i]; this.modal = 'detail'; },
  copy(t){ navigator.clipboard.writeText(t).then(()=>{ this.copied = true; setTimeout(()=>this.copied=false, 2000); }).catch(()=>{}); },
  get shown(){ return this.rows.map((r,i)=>({r,i})).filter(x => this.filter === 'all' || x.r.kind === this.filter); }
}" @keydown.escape.window="modal = null">

  {{-- Solde --}}
  <div class="tx-hero">
    <div class="tx-hero__lbl">{{ __('app.balance') }}</div>
    <div class="tx-hero__bal">{{ number_format((float)$user->balance, 2, ',', ' ') }}<span class="tx-hero__cur">{{ $currency }}</span></div>
    <div class="tx-hero__sub">{{ __('app.available') }}</div>
  </div>

  @if($blocked)
  <div class="tx-alert"><i class="fas fa-ban" style="margin-top:.15rem"></i><div><strong>{{ __('app.account_blocked_title') }}</strong> — {{ __('app.transfer_account_blocked') }}</div></div>
  @elseif($negative)
  <div class="tx-alert">
    <i class="fas fa-circle-exclamation" style="margin-top:.15rem"></i>
    <div>Solde négatif ({{ number_format((float)$user->balance,2,',',' ') }} {{ $currency }}). Les virements sont désactivés jusqu'à la régularisation de votre compte.</div>
  </div>
  @endif
  @if($errors->has('blocked'))
  <div class="tx-alert"><i class="fas fa-ban" style="margin-top:.15rem"></i><div>{{ $errors->first('blocked') }}</div></div>
  @endif

  {{-- Actions --}}
  <div class="tx-actions">
    @if($negative)
    <button type="button" class="tx-act" disabled>
      <span class="tx-act__ico"><i class="fas fa-lock"></i></span>
      <span><div class="tx-act__name">{{ __('app.action_send') }}</div></span>
    </button>
    @else
    <a href="{{ route('client.app.transfer.send') }}" class="tx-act">
      <span class="tx-act__ico"><i class="fas fa-paper-plane"></i></span>
      <span><div class="tx-act__name">{{ __('app.action_send') }}</div><div class="tx-act__desc">{{ __('app.send_subtitle') }}</div></span>
    </a>
    @endif
    <button type="button" class="tx-act" @click="modal = 'receive'">
      <span class="tx-act__ico"><i class="fas fa-arrow-down"></i></span>
      <span><div class="tx-act__name">{{ __('app.action_receive') }}</div><div class="tx-act__desc">{{ __('app.receive_subtitle_short') }}</div></span>
    </button>
  </div>

  {{-- Liste --}}
  @if($transfers->isNotEmpty())
  <div class="tx-head">
    <span class="tx-head__title">Virements récents</span>
    <a href="{{ route('client.app.movements') }}" class="tx-head__link">{{ __('app.see_all') }} <i class="fas fa-chevron-right" style="font-size:.6rem"></i></a>
  </div>

  <div class="tx-chips">
    <button type="button" class="tx-chip" :class="{'is-on': filter==='all'}" @click="filter='all'">{{ __('app.see_all') }}</button>
    <button type="button" class="tx-chip" :class="{'is-on': filter==='pending'}" @click="filter='pending'">{{ __('transfer.status_pending') }}</button>
    <button type="button" class="tx-chip" :class="{'is-on': filter==='fee'}" @click="filter='fee'">{{ __('transfer.status_fee_required') }}</button>
    <button type="button" class="tx-chip" :class="{'is-on': filter==='done'}" @click="filter='done'">{{ __('transfer.status_completed') }}</button>
    <button type="button" class="tx-chip" :class="{'is-on': filter==='rej'}" @click="filter='rej'">{{ __('transfer.status_rejected') }}</button>
  </div>

  <div class="tx-list">
    @foreach($rows as $i => $r)
    @php [$k, $icon] = [$r['kind'], collect($kinds)->first(fn ($v) => $v[0] === $r['kind'])[1]]; @endphp
    <button type="button" class="tx-row" x-show="filter === 'all' || filter === '{{ $k }}'" @click="open({{ $i }})">
      <span class="tx-ico tx-ico--{{ $k }}"><i class="fas fa-{{ $icon }}"></i></span>
      <span class="tx-row__body">
        <div class="tx-row__name">{{ $r['name'] }}</div>
        <div class="tx-row__sub">{{ $r['date'] }}</div>
      </span>
      <span class="tx-row__right">
        <div class="tx-row__amt {{ $k === 'rej' ? 'is-rej' : '' }}">-{{ $r['amount'] }}</div>
        <div class="tx-row__st tx-st--{{ $k }}">{{ $r['status'] }}</div>
      </span>
    </button>
    @endforeach
    <div class="tx-none" x-show="shown.length === 0" x-cloak><i class="fas fa-filter"></i>{{ __('app.no_activity') }}</div>
  </div>
  @else
  <div class="tx-list"><div class="tx-none"><i class="fas fa-right-left"></i>{{ __('app.no_activity') }}</div></div>
  @endif

  {{-- Modale : détail d'un virement --}}
  <div class="tx-modal" x-show="modal === 'detail'" x-cloak x-transition.opacity>
    <div class="tx-modal__bg" @click="modal = null"></div>
    <div class="tx-modal__box" x-show="sel" @click.stop>
      <button type="button" class="tx-modal__x" @click="modal = null" aria-label="×"><i class="fas fa-xmark"></i></button>
      <div class="tx-modal__title">{{ __('transfer.details') }}</div>
      <template x-if="sel">
        <div>
          <div class="tx-big">
            <div class="tx-big__amt" x-text="'-' + sel.amount"></div>
            <div class="tx-big__who" x-text="sel.name"></div>
            <div class="tx-row__st" :class="'tx-st--' + sel.kind" x-text="sel.status" style="margin-top:.5rem"></div>
          </div>
          <div class="tx-kv"><span class="tx-kv__k">Réf.</span><span class="tx-kv__v tx-kv__v--mono" x-text="sel.ref"></span></div>
          <div class="tx-kv"><span class="tx-kv__k">IBAN</span><span class="tx-kv__v tx-kv__v--mono" x-text="sel.iban"></span></div>
          <div class="tx-kv"><span class="tx-kv__k">Date</span><span class="tx-kv__v" x-text="sel.date"></span></div>
          <a :href="sel.url" class="tx-btn"><i class="fas fa-arrow-right"></i> {{ __('transfer.details') }}</a>
        </div>
      </template>
    </div>
  </div>

  {{-- Modale : recevoir (coordonnées bancaires) --}}
  <div class="tx-modal" x-show="modal === 'receive'" x-cloak x-transition.opacity>
    <div class="tx-modal__bg" @click="modal = null"></div>
    <div class="tx-modal__box" @click.stop>
      <button type="button" class="tx-modal__x" @click="modal = null" aria-label="×"><i class="fas fa-xmark"></i></button>
      <div class="tx-modal__title">{{ __('app.receive_title') }}</div>
      <div class="tx-big">
        <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--ca-text-3)">{{ __('app.receive_iban') }}</div>
        <div style="font-family:monospace;font-size:1rem;font-weight:700;margin-top:.4rem;word-break:break-all">{{ $iban !== '' ? trim(chunk_split($iban, 4, ' ')) : '—' }}</div>
      </div>
      <div class="tx-kv"><span class="tx-kv__k">{{ __('app.receive_bic') }}</span><span class="tx-kv__v tx-kv__v--mono">{{ $bic }}</span></div>
      <div class="tx-kv"><span class="tx-kv__k">{{ __('app.receive_name') }}</span><span class="tx-kv__v">{{ $user->name }}</span></div>
      <div class="tx-kv"><span class="tx-kv__k">{{ __('app.receive_bank') }}</span><span class="tx-kv__v">{{ site_name() }}</span></div>
      @if($iban !== '')
      <button type="button" class="tx-btn" @click="copy(@js($iban))">
        <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
        <span x-text="copied ? @js(__('app.copied')) : @js(__('app.copy_iban'))"></span>
      </button>
      <button type="button" class="tx-btn tx-btn--ghost" @click="navigator.share ? navigator.share({title: @js(site_name()), text: @js($shareText)}).catch(()=>{}) : copy(@js($shareText))">
        <i class="fas fa-share-nodes"></i> {{ __('app.share_details') }}
      </button>
      @endif
    </div>
  </div>
</div>

<div style="height:1.5rem"></div>
@endsection
