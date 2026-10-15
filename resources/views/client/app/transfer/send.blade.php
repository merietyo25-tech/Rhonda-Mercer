@extends('layouts.client-app')
@section('title', __('app.send_title') . ' — ' . site_name())
@section('page_title', __('app.send_title'))
@section('back_btn', true)
@section('back_url', route('client.app.transfers'))

@php
    $balance  = (float) $user->balance;
    $currency = $user->currency ?? \App\Models\Currency::default();
@endphp

@push('styles')
<style>
.sd-page { padding: 1rem 1.25rem 2rem; max-width: 980px; margin: 0 auto; }
.sd-notice { display: flex; gap: .65rem; align-items: flex-start; margin-bottom: 1rem; padding: .8rem 1rem;
  background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.25); border-left: 3px solid var(--ca-amber); border-radius: 12px;
  font-size: .78rem; line-height: 1.5; color: var(--ca-text-2); }
.sd-notice i { color: var(--ca-amber); margin-top: .15rem; }
.sd-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 900px) { .sd-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; gap: 1.25rem; } }
.sd-card { background: var(--ca-bg2); border: 1px solid var(--ca-border); border-radius: var(--ca-radius); padding: 1.25rem 1.25rem 1.35rem; }
.sd-card__title { font-size: 1rem; font-weight: 800; color: var(--ca-text); margin-bottom: .2rem; }
.sd-card__sub { font-size: .78rem; color: var(--ca-text-3); margin-bottom: 1rem; }
.sd-field { margin-bottom: 1rem; }
.sd-label { display: block; font-size: .74rem; font-weight: 700; color: var(--ca-text-2); margin-bottom: .4rem; }
.sd-label em { font-style: normal; font-weight: 400; color: var(--ca-text-3); }
.sd-input-wrap { position: relative; }
.sd-input-wrap > i { position: absolute; left: .95rem; top: 50%; transform: translateY(-50%); color: var(--ca-text-3); font-size: .8rem; pointer-events: none; }
.sd-input { width: 100%; min-height: 48px; padding: .75rem 1rem .75rem 2.5rem; border-radius: 12px; border: 1.5px solid var(--ca-border);
  background: var(--ca-bg3); color: var(--ca-text); font-size: 16px; font-family: inherit; outline: none; transition: border-color .15s, box-shadow .15s; }
.sd-input:focus { border-color: var(--ca-accent); box-shadow: 0 0 0 3px rgba(63,203,146,.16); }
.sd-input.is-bad { border-color: var(--ca-negative); }
.sd-input--iban { font-family: 'Space Grotesk', ui-monospace, monospace; letter-spacing: .05em; text-transform: uppercase; }
textarea.sd-input { padding-left: 1rem; min-height: 78px; resize: vertical; }
.sd-hint { font-size: .72rem; color: var(--ca-text-3); margin-top: .35rem; }
.sd-hint--err { color: var(--ca-negative); }

.sd-balance { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .7rem .95rem; margin-bottom: 1rem;
  background: var(--ca-bg3); border: 1px solid var(--ca-border); border-radius: 12px; font-size: .76rem; color: var(--ca-text-3); }
.sd-balance strong { color: var(--ca-text); font-family: 'Space Grotesk', sans-serif; font-size: .95rem; }
.sd-amount { text-align: center; padding: .5rem 0 .25rem; }
.sd-amount__val { font-family: 'Space Grotesk', sans-serif; font-weight: 800; color: var(--ca-text); display: flex; align-items: baseline; justify-content: center; gap: .4rem;
  font-size: clamp(2.4rem, 9vw, 3.2rem); line-height: 1.1; }
.sd-amount__val sup { font-size: 1rem; font-weight: 700; color: var(--ca-text-3); }
.sd-amount__val.is-empty > span { opacity: .35; }
.sd-amount__sub { min-height: 1.4rem; margin-top: .3rem; font-size: .78rem; color: var(--ca-text-3); }
.sd-amount__sub .bad { color: var(--ca-negative); font-weight: 600; }
.sd-chip { display: inline-flex; align-items: center; gap: .4rem; margin: .5rem 0 .25rem; padding: .35rem .8rem; border-radius: 999px; cursor: pointer;
  border: 1px solid var(--ca-border); background: var(--ca-bg3); color: var(--ca-text-2); font-size: .74rem; font-weight: 600; font-family: inherit; }
.sd-chip:hover { border-color: var(--ca-accent); }

.sd-amount__input { width: 100%; max-width: 280px; border: 0; border-bottom: 2px solid var(--ca-border); background: transparent; color: var(--ca-text); text-align: center;
  font: inherit; font-size: inherit; line-height: 1.1; outline: none; padding: 0 0 .2rem; border-radius: 0; }
.sd-amount__input:focus { border-bottom-color: var(--ca-accent); }
.sd-amount__input::placeholder { color: var(--ca-text-3); opacity: .45; }
.sd-keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: .55rem; margin: .9rem auto 1rem; max-width: 340px; }
.sd-key { min-height: 54px; border-radius: 14px; border: 1px solid var(--ca-border); background: var(--ca-bg3); color: var(--ca-text);
  font-size: 1.25rem; font-weight: 700; font-family: 'Space Grotesk', sans-serif; cursor: pointer; touch-action: manipulation; transition: background .12s, transform .08s; }
.sd-key:hover { background: var(--ca-bg4); }
.sd-key:active { transform: scale(.96); }
.sd-key--del { color: var(--ca-text-2); font-size: 1rem; }

.sd-summary { display: flex; justify-content: space-between; gap: 1rem; padding: .75rem .95rem; margin-bottom: 1rem; border-radius: 12px;
  background: rgba(63,203,146,.08); border: 1px dashed rgba(63,203,146,.35); font-size: .78rem; color: var(--ca-text-2); }
.sd-summary strong { color: var(--ca-text); }
.sd-summary__to { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: right; }

.sd-submit { width: 100%; min-height: 54px; border: 0; border-radius: 999px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .6rem;
  font-size: 1rem; font-weight: 800; font-family: inherit; color: #fff; background: linear-gradient(135deg, #3FCB92, #008457);
  box-shadow: 0 8px 26px rgba(0,132,87,.35); transition: filter .15s, transform .1s; }
.sd-submit:hover:not(:disabled) { filter: brightness(1.08); }
.sd-submit:active:not(:disabled) { transform: scale(.985); }
.sd-submit:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
.sd-errors { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: 12px; background: rgba(255,90,90,.1); border: 1px solid rgba(255,90,90,.28);
  color: var(--ca-negative); font-size: .8rem; }

/* ── Écran de chargement ── */
.sd-loading { position: fixed; inset: 0; z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 1.1rem; padding: 2rem; text-align: center; color: #fff;
  background: radial-gradient(circle at 50% 35%, rgba(27,73,118,.96) 0%, rgba(15,13,54,.98) 70%); backdrop-filter: blur(6px); }
.sd-loading__bar { position: absolute; top: 0; left: 0; right: 0; height: 3px; background: rgba(255,255,255,.08); overflow: hidden; }
.sd-loading__bar::after { content: ''; position: absolute; top: 0; bottom: 0; width: 40%; background: linear-gradient(90deg, transparent, #3FCB92, transparent);
  animation: sd-slide 1.2s ease-in-out infinite; }
@keyframes sd-slide { from { left: -40%; } to { left: 100%; } }
.sd-loading__ring { position: relative; width: 84px; height: 84px; }
.sd-loading__ring::before { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 3px solid rgba(255,255,255,.14); border-top-color: #3FCB92; animation: sd-spin .85s linear infinite; }
.sd-loading__ring i { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #3FCB92; animation: sd-fly 1.6s ease-in-out infinite; }
@keyframes sd-spin { to { transform: rotate(360deg); } }
@keyframes sd-fly { 0%, 100% { transform: translate(-2px, 2px); } 50% { transform: translate(3px, -3px); } }
.sd-loading__title { font-size: 1.15rem; font-weight: 800; }
.sd-loading__amount { font-family: 'Space Grotesk', sans-serif; font-size: 1.6rem; font-weight: 800; color: #3FCB92; }
.sd-loading__hint { font-size: .8rem; color: rgba(255,255,255,.65); max-width: 300px; line-height: 1.5; }
@media (prefers-reduced-motion: reduce) { .sd-loading__ring::before, .sd-loading__ring i, .sd-loading__bar::after { animation-duration: 3s; } }
</style>
@endpush

@section('content')
<div class="sd-page" x-data="sendTransfer({ balance: {{ $balance }}, currency: @js($currency), initial: @js(old('amount', '')) , timeoutMsg: @js(__('transfer.timeout')) })">

  @if($errors->any())
  <div class="sd-errors" role="alert"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
  @endif
  <div class="sd-errors" role="alert" x-show="clientError" x-cloak style="display:none" x-text="clientError"></div>

  <form :data-confirm="canSend ? @js(__('transfer.confirm_send', ['amount' => '__A__', 'name' => '__N__'])).replace('__A__', display + ' {{ $currency }}').replace('__N__', name) : null" data-confirm-title="{{ __('transfer.send_button') }}" method="POST" action="{{ route('client.app.transfer.send.process') }}" id="sendForm" @submit="onSubmit($event)" novalidate>
    @csrf
    <input type="hidden" name="amount" :value="numeric">

    <div class="sd-grid">
      {{-- ─── Bénéficiaire ─── --}}
      <section class="sd-card">
        <h2 class="sd-card__title">{{ __('transfer.beneficiary_title') }}</h2>
        <p class="sd-card__sub">{{ __('transfer.beneficiary_sub') }}</p>

        <div class="sd-field">
          <label class="sd-label" for="beneficiary_name">{{ __('app.send_name') }}</label>
          <div class="sd-input-wrap"><i class="fas fa-user"></i>
            <input type="text" id="beneficiary_name" name="beneficiary_name" class="sd-input" x-model="name"
                   placeholder="Jean Dupont" value="{{ old('beneficiary_name') }}" maxlength="100" autocomplete="off" required>
          </div>
        </div>

        <div class="sd-field">
          <label class="sd-label" for="beneficiary_iban">{{ __('app.send_iban') }}</label>
          <div class="sd-input-wrap"><i class="fas fa-building-columns"></i>
            <input type="text" id="beneficiary_iban" name="beneficiary_iban" class="sd-input sd-input--iban" :class="ibanBad && 'is-bad'"
                   x-model="iban" @input="formatIban()" @blur="ibanTouched = true"
                   placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX" value="{{ old('beneficiary_iban') }}" maxlength="50" autocomplete="off" required>
          </div>
          <div class="sd-hint sd-hint--err" x-show="ibanBad" style="display:none">{{ __('transfer.iban_invalid') }}</div>
          <div class="sd-hint" x-show="!ibanBad">{{ __('transfer.iban_hint') }}</div>
        </div>

        <div class="sd-field" style="margin-bottom:0">
          <label class="sd-label" for="note">{{ __('app.send_note') }} <em>({{ __('transfer.note_optional') }})</em></label>
          <textarea id="note" name="note" class="sd-input" rows="2" maxlength="255" placeholder="{{ __('app.send_note') }}">{{ old('note') }}</textarea>
        </div>
      </section>

      {{-- ─── Montant ─── --}}
      <section class="sd-card">
        <h2 class="sd-card__title">{{ __('transfer.amount_title') }}</h2>

        <div class="sd-balance">
          <span>{{ __('app.available') }}</span>
          <strong>{{ number_format($balance, 2, ',', ' ') }} {{ $currency }}</strong>
        </div>

        <div class="sd-amount">
          <label class="sd-amount__val" for="amount_input"><sup>{{ $currency }}</sup>
            <input type="text" id="amount_input" class="sd-amount__input" inputmode="decimal" autocomplete="off" placeholder="0" :value="raw"
                   @input="setRaw($event.target.value); $event.target.value = raw" aria-label="{{ __('transfer.amount_title') }}">
          </label>
          <div class="sd-amount__sub">
            <span x-show="numeric > 0 && numeric <= balance" style="display:none">{{ __('transfer.remaining') }} : <strong x-text="fmt(balance - numeric)"></strong></span>
            <span class="bad" x-show="numeric > balance" style="display:none"><i class="fas fa-exclamation-triangle"></i> {{ __('transfer.insufficient') }}</span>
          </div>
          <button type="button" class="sd-chip" @click="useMax()" x-show="balance > 0"><i class="fas fa-wand-magic-sparkles"></i> {{ __('transfer.use_max') }}</button>
        </div>

        <div class="sd-summary" x-show="canSend" style="display:none">
          <span>{{ __('transfer.to') }}</span>
          <span class="sd-summary__to"><strong x-text="name || '—'"></strong></span>
        </div>

        <button type="submit" class="sd-submit" :disabled="!canSend || sending">
          <i class="fas fa-paper-plane"></i>
          <span>{{ __('transfer.send_button') }}</span>
          <span x-show="numeric > 0 && numeric <= balance" style="display:none">— <span x-text="display"></span> {{ $currency }}</span>
        </button>
      </section>
    </div>
  </form>

  {{-- ─── Écran de chargement : affiché dès l'envoi, jusqu'à la redirection ─── --}}
  <div class="sd-loading" x-show="sending" x-cloak style="display:none" role="alertdialog" aria-live="assertive" aria-busy="true">
    <div class="sd-loading__bar"></div>
    <div class="sd-loading__ring"><i class="fas fa-paper-plane"></i></div>
    <div class="sd-loading__title">{{ __('transfer.sending_title') }}</div>
    <div class="sd-loading__amount"><span x-text="display"></span> {{ $currency }}</div>
    <div class="sd-loading__hint">{{ __('transfer.sending_hint') }}</div>
  </div>
</div>
@endsection

@push('scripts')
<script>
window.sendTransfer = function (cfg) {
  return {
    balance: cfg.balance,
    raw: cfg.initial ? String(cfg.initial) : '',
    name: '', iban: '',
    ibanTouched: false, sending: false, clientError: '', timer: null,

    init() {
      // Valeurs rejouées après une erreur de validation serveur
      this.name = document.getElementById('beneficiary_name').value;
      this.iban = document.getElementById('beneficiary_iban').value;
      // Retour arrière du navigateur : on ne reste jamais bloqué sur l'écran de chargement
      window.addEventListener('pageshow', (e) => { if (e.persisted) this.stop(); });
    },

    get numeric() { return parseFloat(this.raw) || 0; },
    get display() { return this.raw ? this.raw.replace(/\B(?=(\d{3})+(?!\d))/g, (m, i, s) => (s.indexOf('.') > -1 && i > s.indexOf('.')) ? '' : ' ') : '0'; },
    get ibanClean() { return this.iban.replace(/\s+/g, '').toUpperCase(); },
    get ibanBad() { return this.ibanTouched && this.ibanClean.length > 0 && !/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/.test(this.ibanClean); },
    get canSend() { return this.numeric > 0 && this.numeric <= this.balance && this.name.trim().length > 1 && this.ibanClean.length >= 15; },

    fmt(n) { return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n || 0); },
    formatIban() { this.iban = this.ibanClean.replace(/(.{4})/g, '$1 ').trim(); },
    useMax() { this.raw = String(Math.floor(this.balance * 100) / 100); },

    // Nettoie la saisie : chiffres et un seul séparateur, 2 décimales max
    setRaw(v) {
      v = String(v).replace(',', '.').replace(/[^0-9.]/g, '');
      const i = v.indexOf('.');
      if (i > -1) v = v.slice(0, i + 1) + v.slice(i + 1).replace(/./g, '').slice(0, 2);
      v = v.replace(/^0+(?=d)/, '');
      if (v.replace('.', '').length > 10) v = v.slice(0, 10);
      this.raw = v;
    },

    onSubmit(e) {
      this.clientError = '';
      this.ibanTouched = true;
      if (this.sending) { e.preventDefault(); return; }       // anti double envoi
      if (!this.canSend || this.ibanBad) { e.preventDefault(); return; }
      this.sending = true;                                    // affiche l'écran de chargement immédiatement
      // Normalise l'IBAN envoyé (sans espaces, en majuscules)
      document.getElementById('beneficiary_iban').value = this.ibanClean;
      // Filet de sécurité : si le serveur ne répond pas, on libère l'écran avec un message
      this.timer = setTimeout(() => { this.stop(); this.clientError = cfg.timeoutMsg; }, 30000);
    },
    stop() { this.sending = false; clearTimeout(this.timer); },
  };
};
</script>
@endpush
