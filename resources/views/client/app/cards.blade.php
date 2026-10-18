@extends('layouts.client-app')
@section('title', __('cards.title') . ' — ' . site_name())
@section('page_title', __('cards.title'))
@section('back_btn', true)
@section('back_url', route('client.app.home'))

@push('styles')
<style>
.cd-page { padding: 1.25rem 1.25rem 2rem; }
.cd-sub  { font-size: .82rem; color: var(--ca-text-3); margin-bottom: 1.1rem; }
.cd-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
@media (min-width: 768px) { .cd-grid { grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); } }

/* Carte bancaire */
.cd-card {
  position: relative; overflow: hidden;
  aspect-ratio: 1.586 / 1; min-height: 190px; max-width: 440px; width: 100%;
  border-radius: 22px; padding: 1.35rem 1.5rem;
  background: linear-gradient(135deg, #3A3590 0%, #231F5F 50%, #1A1750 100%);
  box-shadow: 0 12px 40px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.08);
  display: flex; flex-direction: column; justify-content: space-between;
  color: #fff;
}
.cd-card::before {
  content: ''; position: absolute; top: -70px; right: -70px; width: 240px; height: 240px; border-radius: 50%;
  background: radial-gradient(circle, rgba(63,203,146,.2) 0%, transparent 65%); pointer-events: none;
}
.cd-card--blocked, .cd-card--suspended { filter: grayscale(.85); opacity: .8; }
.cd-card__top { display: flex; align-items: center; justify-content: space-between; position: relative; }
.cd-card__brand { font-size: .68rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.7); }
.cd-card__chip {
  width: 42px; height: 31px; border-radius: 7px;
  background: linear-gradient(135deg, #8EDDBC, #008457 55%, #7BA6D9);
}
.cd-card__num {
  position: relative; font-family: 'Space Grotesk', ui-monospace, monospace;
  font-size: clamp(1.05rem, 4.6vw, 1.45rem); letter-spacing: .12em; font-weight: 600; margin: .35rem 0;
  white-space: nowrap;
}
.cd-card__bottom { display: flex; align-items: flex-end; justify-content: space-between; gap: .75rem; position: relative; }
.cd-card__lbl { font-size: .55rem; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.55); margin-bottom: .15rem; }
.cd-card__val { font-size: .85rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; overflow-wrap: anywhere; }
.cd-card__net { font-size: 2.1rem; line-height: 1; color: rgba(255,255,255,.92); }

.cd-meta { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .75rem; max-width: 440px; }
.cd-badge { font-size: .72rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; }
.cd-badge--ok  { background: rgba(0,200,150,.14); color: var(--ca-positive); }
.cd-badge--off { background: rgba(255,90,90,.14); color: var(--ca-negative); }
.cd-eye { background: none; border: 1px solid var(--ca-border); color: var(--ca-text-2); border-radius: 999px; padding: .3rem .8rem; font-size: .72rem; cursor: pointer; }
.cd-badge--pause { background: rgba(245,158,11,.14); color: var(--ca-amber); }
.cd-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; max-width: 440px; }
.cd-btn { display: inline-flex; align-items: center; gap: .45rem; padding: .55rem 1rem; border-radius: 999px; cursor: pointer; font-family: inherit; font-size: .78rem; font-weight: 700;
  border: 1px solid var(--ca-border); background: var(--ca-bg3); color: var(--ca-text); }
.cd-btn:hover { border-color: var(--ca-accent); }
.cd-btn--warn { color: var(--ca-amber); }
.cd-limit { margin-top: .9rem; max-width: 440px; padding: .95rem 1rem; border-radius: 14px; background: var(--ca-bg3); border: 1px solid var(--ca-border); }
.cd-limit__top { display: flex; justify-content: space-between; align-items: baseline; gap: .75rem; margin-bottom: .4rem; }
.cd-limit__t { font-size: .8rem; font-weight: 800; }
.cd-limit__v { font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 1.15rem; }
.cd-limit input[type=range] { width: 100%; accent-color: var(--ca-accent); margin: .35rem 0 .2rem; }
.cd-limit__h { font-size: .72rem; color: var(--ca-text-3); margin-bottom: .6rem; }
.cd-note { font-size: .76rem; color: var(--ca-negative); margin-top: .5rem; max-width: 440px; }

.cd-empty { text-align: center; padding: 3rem 1rem; }
.cd-empty__ico { width: 76px; height: 76px; margin: 0 auto 1rem; border-radius: 50%; background: var(--ca-bg3); border: 1px solid var(--ca-border);
  display: flex; align-items: center; justify-content: center; font-size: 1.9rem; color: var(--ca-text-3); }
.cd-empty__t { font-size: 1rem; font-weight: 700; margin-bottom: .4rem; }
.cd-empty__s { font-size: .82rem; color: var(--ca-text-3); line-height: 1.6; max-width: 360px; margin: 0 auto 1.4rem; }
.cd-ok { margin-bottom: 1rem; padding: .8rem 1rem; border-radius: 12px; background: rgba(0,200,150,.1); border: 1px solid rgba(0,200,150,.3); color: var(--ca-text-2); font-size: .82rem; }
.cd-pending { display: inline-flex; align-items: center; gap: .5rem; padding: .7rem 1.2rem; border-radius: 999px; font-size: .82rem; font-weight: 700; background: rgba(245,158,11,.12); color: var(--ca-amber); }
button.cd-empty__btn { border: 0; cursor: pointer; font-family: inherit; }
.cd-empty__btn { display: inline-flex; align-items: center; gap: .5rem; padding: .75rem 1.4rem; border-radius: 999px; font-size: .85rem; font-weight: 700;
  color: #fff; background: linear-gradient(135deg, #3FCB92, #008457); }

[x-cloak]{display:none !important}
.cd-pay{max-width:440px;margin:0 auto;text-align:left;background:var(--ca-bg3);border:1px solid var(--ca-border);border-radius:18px;padding:1.1rem 1.2rem}
.cd-pay__t{font-size:.85rem;font-weight:800;margin-bottom:.6rem}
.cd-pay__row{display:flex;justify-content:space-between;gap:1rem;padding:.6rem 0;border-top:1px solid var(--ca-border-2);font-size:.8rem}
.cd-pay__row span{color:var(--ca-text-3);flex-shrink:0}
.cd-pay__row strong{text-align:right;word-break:break-all}
.cd-mono{font-family:monospace;font-size:.78rem}
.cd-pay__hint{font-size:.74rem;color:var(--ca-text-3);padding:.6rem 0 .2rem;line-height:1.5}
.cd-pay__btns{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem}
.cd-modal{position:fixed;inset:0;z-index:20000;display:flex;align-items:flex-end;justify-content:center;padding:1rem}
.cd-modal__bg{position:absolute;inset:0;background:rgba(2,10,20,.66);backdrop-filter:blur(3px)}
.cd-modal__box{position:relative;width:100%;max-width:440px;background:var(--ca-bg2);color:var(--ca-text);border:1px solid var(--ca-border);border-radius:22px;padding:1.4rem;box-shadow:0 24px 60px rgba(0,0,0,.5);max-height:90vh;overflow-y:auto}
@media (min-width:768px){.cd-modal{align-items:center}}
.cd-modal__x{position:absolute;top:.8rem;right:.8rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--ca-bg4);color:var(--ca-text-2);cursor:pointer}
.cd-modal__title{font-size:1.05rem;font-weight:800;margin-bottom:1rem;padding-right:2rem;text-align:left}
.cd-modal__foot{display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.2rem}
.cd-lbl{display:block;font-size:.76rem;font-weight:700;color:var(--ca-text-2);margin-bottom:.4rem}
.cd-field{margin-bottom:.9rem}
.cd-field input{width:100%;min-height:46px;padding:.65rem .9rem;border-radius:12px;border:1px solid var(--ca-border);background:var(--ca-bg3);color:var(--ca-text);font-size:16px;font-family:inherit}
.cd-field input:focus{outline:2px solid var(--ca-accent)}
.cd-hint{font-size:.72rem;color:var(--ca-text-3);margin-top:.3rem;line-height:1.45}
.cd-two{display:grid;grid-template-columns:1fr 1.4fr;gap:.7rem}
.cd-types{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1rem}
.cd-type{position:relative;display:flex;flex-direction:column;gap:.25rem;padding:.9rem;border-radius:14px;border:1.5px solid var(--ca-border);background:var(--ca-bg3);cursor:pointer;font-size:.74rem;color:var(--ca-text-3);line-height:1.4}
.cd-type input{position:absolute;opacity:0;pointer-events:none}
.cd-type i{font-size:1.2rem;color:var(--ca-accent-l)}
.cd-type strong{font-size:.85rem;color:var(--ca-text)}
.cd-type.is-on{border-color:var(--ca-accent-l);background:var(--ca-bg4)}
.cd-badge--type{background:var(--ca-bg4);color:var(--ca-text-2)}
</style>
@endpush

@section('content')
<div class="cd-page">
  @if($cards->isNotEmpty())
  <p class="cd-sub">{{ __('cards.subtitle') }}</p>

  <div class="cd-grid">
    @foreach($cards as $card)
    @php
      $blocked   = $card->status === \App\Models\Card::STATUS_BLOCKED;
      $suspended = $card->status === \App\Models\Card::STATUS_SUSPENDED;
      $limit     = $card->effectiveLimit();
      $cap       = max(\App\Models\Card::LIMIT_MIN, $card->cap());
      $net     = strtolower($card->network);
    @endphp
    <div x-data="{ shown: false }">
      <div class="cd-card {{ $blocked ? 'cd-card--blocked' : ($suspended ? 'cd-card--suspended' : '') }}">
        <div class="cd-card__top">
          <div class="cd-card__brand"><i class="fas fa-landmark"></i> {{ site_name() }}</div>
          <div class="cd-card__chip" aria-hidden="true"></div>
        </div>

        <div class="cd-card__num" x-show="shown">{{ $card->maskedNumber() }}</div>
        <div class="cd-card__num" x-show="!shown">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{ $card->last_four }}</div>

        <div class="cd-card__bottom">
          <div style="min-width:0">
            <div class="cd-card__lbl">{{ __('cards.holder') }}</div>
            <div class="cd-card__val">{{ $card->holder_name }}</div>
          </div>
          <div>
            <div class="cd-card__lbl">{{ __('cards.expires') }}</div>
            <div class="cd-card__val">{{ $card->expires_at->format('m/y') }}</div>
          </div>
          <div class="cd-card__net" aria-label="{{ ucfirst($net) }}">
            <i class="fa-brands {{ $net === 'visa' ? 'fa-cc-visa' : 'fa-cc-mastercard' }}"></i>
          </div>
        </div>
      </div>

      <div class="cd-meta">
        <span class="cd-badge cd-badge--type">{{ __('cards.type_' . ($card->card_type ?: 'virtual')) }}</span>
        <span class="cd-badge {{ $blocked ? 'cd-badge--off' : ($suspended ? 'cd-badge--pause' : 'cd-badge--ok') }}">
          {{ $blocked ? __('cards.status_blocked') : ($suspended ? __('cards.status_suspended') : __('cards.status_active')) }}
        </span>
      </div>
      @if($blocked)
      <div class="cd-note"><i class="fas fa-circle-exclamation"></i> {{ __('cards.blocked_notice') }}</div>
      @elseif($suspended)
      <div class="cd-note" style="color:var(--ca-amber)"><i class="fas fa-pause-circle"></i> {{ __('cards.suspended_notice') }}</div>
      @endif

      @unless($blocked)
      <div class="cd-actions">
        <form data-confirm="{{ $suspended ? __('cards.confirm_resume') : __('cards.confirm_suspend') }}" data-confirm-title="{{ $suspended ? __('cards.resume_button') : __('cards.suspend_button') }}"{!! $suspended ? '' : ' data-confirm-danger="1"' !!} method="POST" action="{{ route('client.app.cards.suspend', $card) }}">
          @csrf
          <button type="submit" class="cd-btn {{ $suspended ? '' : 'cd-btn--warn' }}">
            <i class="fas {{ $suspended ? 'fa-play' : 'fa-pause' }}"></i> {{ $suspended ? __('cards.resume_button') : __('cards.suspend_button') }}
          </button>
        </form>
      </div>

      <form :data-confirm="@js(__('cards.confirm_limit', ['amount' => '__AMT__'])).replace('__AMT__', new Intl.NumberFormat('fr-FR').format(v) + ' {{ $user->currency ?? 'EUR' }}')" data-confirm-title="{{ __('cards.limit_title') }}" method="POST" action="{{ route('client.app.cards.limit', $card) }}" class="cd-limit" x-data="{ v: {{ $limit }} }">
        @csrf
        <div class="cd-limit__top">
          <span class="cd-limit__t"><i class="fas fa-gauge-high"></i> {{ __('cards.limit_title') }}</span>
          <span class="cd-limit__v"><span x-text="new Intl.NumberFormat('fr-FR').format(v)"></span> {{ $user->currency ?? 'EUR' }}</span>
        </div>
        <input type="range" name="spending_limit" min="{{ \App\Models\Card::LIMIT_MIN }}" max="{{ $cap }}" step="{{ \App\Models\Card::LIMIT_STEP }}" x-model.number="v">
        <div class="cd-limit__h">{{ __('cards.limit_hint', ['min' => number_format(\App\Models\Card::LIMIT_MIN, 0, ',', ' '), 'max' => number_format($cap, 0, ',', ' '), 'currency' => $user->currency ?? 'EUR']) }}</div>
        @error('spending_limit')<div class="cd-note" style="margin:0 0 .5rem">{{ $message }}</div>@enderror
        <button type="submit" class="cd-btn" :disabled="v === {{ $limit }}"><i class="fas fa-floppy-disk"></i> {{ __('cards.limit_save') }}</button>
      </form>
      @endunless
    </div>
    @endforeach
  </div>

  @else
  @php
    $pr = $pendingRequest;
    $awaiting = $pr && $pr->status === \App\Models\CardRequest::STATUS_AWAITING_PAYMENT && $pr->invoice;
    $inv = $awaiting ? $pr->invoice : null;
    $reopen = $errors->any() && old('card_type') !== null;
  @endphp
  <div class="cd-empty" x-data="{ m: {{ $reopen ? 'true' : 'false' }}, type: '{{ old('card_type', 'virtual') }}', busy: false }" @keydown.escape.window="m = false">
    <div class="cd-empty__ico"><i class="fas fa-credit-card"></i></div>
    <div class="cd-empty__t">{{ __('cards.empty_title') }}</div>

    @if($awaiting)
      <p class="cd-empty__s">{{ __('cards.pay_text', ['amount' => number_format((float) $inv->total, 2, ',', ' ') . ' ' . $inv->currency]) }}</p>
      <div class="cd-pay">
        <div class="cd-pay__t"><i class="fas fa-file-invoice"></i> {{ __('cards.pay_title') }}</div>
        <div class="cd-pay__row"><span>{{ __('cards.form_type') }}</span><strong>{{ __('cards.type_' . $pr->card_type) }} — {{ $pr->holder_name }}</strong></div>
        <div class="cd-pay__row"><span>{{ __('invoice.amount') }}</span><strong>{{ number_format((float) $inv->total, 2, ',', ' ') }} {{ $inv->currency }}</strong></div>
        <div class="cd-pay__row"><span>{{ __('transfer.type') }}</span><strong>{{ $inv->paymentTypeLabel() }}</strong></div>
        <div class="cd-pay__row"><span>{{ __('invoice.pay_holder') }}</span><strong>{{ $inv->paymentHolder() }}</strong></div>
        <div class="cd-pay__row"><span>{{ __('invoice.pay_iban') }}</span><strong class="cd-mono">{{ \App\Models\Invoice::formatIban($inv->paymentIban()) }}</strong></div>
        @if($inv->paymentBic() !== '')<div class="cd-pay__row"><span>{{ __('invoice.pay_bic') }}</span><strong class="cd-mono">{{ $inv->paymentBic() }}</strong></div>@endif
        <div class="cd-pay__row"><span>{{ __('invoice.pay_reference') }}</span><strong class="cd-mono">{{ $inv->reference }}</strong></div>
        <div class="cd-pay__hint"><i class="fas fa-circle-info"></i> {{ __('invoice.pay_hint') }}</div>
        <div class="cd-pay__btns">
          <button type="button" class="cd-btn" x-data="{ ok: false }" @click="navigator.clipboard && navigator.clipboard.writeText(@js($inv->paymentIban())); ok = true; setTimeout(() => ok = false, 2000)">
            <i class="fas" :class="ok ? 'fa-check' : 'fa-copy'"></i> <span x-text="ok ? @js(__('app.copied')) : @js(__('app.copy_iban'))"></span>
          </button>
          <a href="{{ route('client.app.invoices.show', $inv) }}" class="cd-btn"><i class="fas fa-file-invoice"></i> {{ __('cards.pay_invoice') }}</a>
        </div>
      </div>
      @if($pr->isPhysical())<p class="cd-empty__s" style="margin-top:1rem"><i class="fas fa-truck"></i> {{ __('cards.delivery_to', ['address' => $pr->deliveryLine()]) }}</p>@endif
    @elseif($pr)
      <p class="cd-empty__s">{{ __('cards.empty_text') }}</p>
      <span class="cd-pending"><i class="fas fa-hourglass-half"></i> {{ __('cards.request_pending') }}</span>
      <p class="cd-empty__s" style="margin-top:.9rem">{{ __('cards.type_' . $pr->card_type) }} — {{ $pr->holder_name }}@if($pr->isPhysical())<br><i class="fas fa-truck"></i> {{ __('cards.delivery_to', ['address' => $pr->deliveryLine()]) }}@endif</p>
    @else
      <p class="cd-empty__s">{{ __('cards.empty_text') }}</p>
      <button type="button" class="cd-empty__btn" @click="m = true"><i class="fas fa-credit-card"></i> {{ __('cards.request_button') }}</button>
    @endif

    {{-- Modale : demande de carte --}}
    <div class="cd-modal" x-show="m" x-cloak x-transition.opacity>
      <div class="cd-modal__bg" @click="m = false"></div>
      <div class="cd-modal__box" @click.stop>
        <button type="button" class="cd-modal__x" @click="m = false" aria-label="×"><i class="fas fa-xmark"></i></button>
        <div class="cd-modal__title">{{ __('cards.form_title') }}</div>

        @if($errors->any() && old('card_type') !== null)
        <div class="cd-note" style="margin:0 0 .8rem;max-width:none"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        <form data-confirm="{{ __('cards.confirm_request') }}" data-confirm-title="{{ __('cards.request_button') }}" method="POST" action="{{ route('client.app.cards.request') }}" @submit="busy = true" style="text-align:left">
          @csrf
          <div class="cd-lbl">{{ __('cards.form_type') }}</div>
          <div class="cd-types">
            <label class="cd-type" :class="{ 'is-on': type === 'virtual' }">
              <input type="radio" name="card_type" value="virtual" x-model="type">
              <i class="fas fa-mobile-screen"></i>
              <strong>{{ __('cards.type_virtual') }}</strong>
              <span>{{ __('cards.type_virtual_desc') }}</span>
            </label>
            <label class="cd-type" :class="{ 'is-on': type === 'physical' }">
              <input type="radio" name="card_type" value="physical" x-model="type">
              <i class="fas fa-credit-card"></i>
              <strong>{{ __('cards.type_physical') }}</strong>
              <span>{{ __('cards.type_physical_desc') }}</span>
            </label>
          </div>

          <div class="cd-field">
            <label class="cd-lbl" for="cd-name">{{ __('cards.form_name') }}</label>
            <input id="cd-name" type="text" name="holder_name" maxlength="26" required value="{{ old('holder_name', $user->name) }}" autocomplete="cc-name">
            <div class="cd-hint">{{ __('cards.form_name_hint') }}</div>
          </div>

          <div x-show="type === 'physical'" x-cloak>
            <div class="cd-field">
              <label class="cd-lbl" for="cd-addr">{{ __('cards.form_address') }}</label>
              <input id="cd-addr" type="text" name="delivery_address" maxlength="255" :required="type === 'physical'" value="{{ old('delivery_address', $user->address) }}" autocomplete="street-address">
            </div>
            <div class="cd-two">
              <div class="cd-field">
                <label class="cd-lbl" for="cd-zip">{{ __('cards.form_zip') }}</label>
                <input id="cd-zip" type="text" name="delivery_zip" maxlength="20" :required="type === 'physical'" value="{{ old('delivery_zip') }}" autocomplete="postal-code">
              </div>
              <div class="cd-field">
                <label class="cd-lbl" for="cd-city">{{ __('cards.form_city') }}</label>
                <input id="cd-city" type="text" name="delivery_city" maxlength="100" :required="type === 'physical'" value="{{ old('delivery_city') }}" autocomplete="address-level2">
              </div>
            </div>
            <div class="cd-field">
              <label class="cd-lbl" for="cd-country">{{ __('cards.form_country') }}</label>
              <input id="cd-country" type="text" name="delivery_country" maxlength="100" :required="type === 'physical'" value="{{ old('delivery_country', $user->country) }}" autocomplete="country-name">
              <div class="cd-hint">{{ __('cards.form_delivery_hint') }}</div>
            </div>
          </div>

          <div class="cd-modal__foot">
            <button type="button" class="cd-btn" @click="m = false">{{ __('cards.form_cancel') }}</button>
            <button type="submit" class="cd-empty__btn" :disabled="busy"><i class="fas" :class="busy ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i> {{ __('cards.form_submit') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
