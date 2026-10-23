@extends('layouts.client-app')
@section('title', __('transfer.detail_title') . ' — ' . site_name())
@section('page_title', __('transfer.detail_title'))
@section('back_btn', true)
@section('back_url', route('client.app.movements'))
@section('main_class', 'ca-main--narrow')

@php
    use App\Models\Transfer;
    $isSend    = $transfer->type === 'send';
    $status    = $transfer->status;
    $isPending = $status === Transfer::STATUS_PENDING;
    $isDone    = $status === Transfer::STATUS_COMPLETED;
    $isRej     = $status === Transfer::STATUS_REJECTED;
    $isFee     = $status === Transfer::STATUS_FEE_REQUIRED;
    // Frais encore à régler : la facture liée n'est pas encore payée (sinon on ne montre plus les coordonnées de paiement)
    $feeDue    = $isFee && $transfer->invoice && $transfer->invoice->status === 'sent';

    $tone  = $isDone ? 'ok' : ($isRej ? 'bad' : ($isFee ? 'info' : 'wait'));
    $icon  = $isDone ? 'fa-circle-check' : ($isRej ? 'fa-circle-xmark' : ($isFee ? 'fa-file-invoice' : 'fa-hourglass-half'));
    $title = __('transfer.status_' . $status);
    $hint  = __('transfer.hint_' . ($isFee && ! $feeDue ? 'pending' : $status));
    $sign  = $isRej ? '' : ($isSend ? '−' : '+');
    $iban  = (string) $transfer->beneficiary_iban;
    $pct      = $transfer->progressValue();
    $awaiting = $transfer->isAwaitingCode();
    $locked   = $transfer->isCodeLocked();
    $showBar  = $isPending || $isFee || $isDone;
@endphp

@push('styles')
<style>
.td-processing{margin-top:.8rem;padding:.7rem .9rem;border-radius:12px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.25);color:#93c5fd;font-size:.8rem;font-weight:600;line-height:1.45}
.td-processing i{margin-right:.4rem}
.td-page { padding: 1rem 1.25rem 2rem; }
.td-hero { text-align: center; padding: 1.6rem 1.25rem 1.4rem; border-radius: var(--ca-radius); border: 1px solid var(--ca-border); background: var(--ca-bg2); margin-bottom: 1rem; }
.td-hero__ico { width: 64px; height: 64px; margin: 0 auto .9rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; }
.td-tone--ok   .td-hero__ico { background: rgba(0,200,150,.14); color: var(--ca-positive); }
.td-tone--bad  .td-hero__ico { background: rgba(255,90,90,.14); color: var(--ca-negative); }
.td-tone--wait .td-hero__ico { background: rgba(245,158,11,.14); color: var(--ca-amber); }
.td-tone--info .td-hero__ico { background: rgba(74,158,255,.14); color: var(--ca-blue); }
.td-hero__status { font-size: .8rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
.td-tone--ok .td-hero__status { color: var(--ca-positive); } .td-tone--bad .td-hero__status { color: var(--ca-negative); }
.td-tone--wait .td-hero__status { color: var(--ca-amber); } .td-tone--info .td-hero__status { color: var(--ca-blue); }
.td-hero__amount { font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: clamp(2rem, 8vw, 2.7rem); color: var(--ca-text); margin: .35rem 0 .2rem; line-height: 1.1; }
.td-hero__amount.is-struck { text-decoration: line-through; opacity: .5; }
.td-hero__hint { font-size: .8rem; color: var(--ca-text-3); line-height: 1.55; max-width: 380px; margin: .4rem auto 0; }

.td-card { background: var(--ca-bg2); border: 1px solid var(--ca-border); border-radius: var(--ca-radius); padding: .35rem 1.1rem; margin-bottom: 1rem; }
.td-card__title { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--ca-text-3); padding: .9rem 0 .2rem; }
.td-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: .8rem 0; border-bottom: 1px solid var(--ca-border); font-size: .85rem; }
.td-row:last-child { border-bottom: 0; }
.td-row__k { color: var(--ca-text-3); flex-shrink: 0; }
.td-row__v { color: var(--ca-text); font-weight: 600; text-align: right; min-width: 0; overflow-wrap: anywhere; }
.td-row__v--mono { font-family: 'Space Grotesk', ui-monospace, monospace; letter-spacing: .03em; }
.td-copy { background: none; border: 0; color: var(--ca-accent); cursor: pointer; font-size: .8rem; margin-left: .4rem; }

.td-steps { list-style: none; padding: .5rem 0 .6rem; margin: 0; }
.td-step { position: relative; display: flex; gap: .85rem; padding-bottom: 1.1rem; }
.td-step:last-child { padding-bottom: 0; }
.td-step::before { content: ''; position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; background: var(--ca-border); }
.td-step:last-child::before { display: none; }
.td-dot { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .6rem;
  background: var(--ca-bg4); color: var(--ca-text-3); border: 2px solid var(--ca-border); }
.td-step.is-done .td-dot { background: var(--ca-positive); border-color: var(--ca-positive); color: #fff; }
.td-step.is-bad .td-dot { background: var(--ca-negative); border-color: var(--ca-negative); color: #fff; }
.td-step.is-current .td-dot { background: var(--ca-amber); border-color: var(--ca-amber); color: #fff; }
.td-step__t { font-size: .85rem; font-weight: 700; color: var(--ca-text); }
.td-step__s { font-size: .74rem; color: var(--ca-text-3); margin-top: .1rem; }

.td-note { margin-bottom: 1rem; padding: .85rem 1rem; border-radius: 12px; font-size: .82rem; line-height: 1.55; }
.td-note--bad { background: rgba(255,90,90,.08); border: 1px solid rgba(255,90,90,.25); color: var(--ca-text-2); }
.td-note--info { background: rgba(74,158,255,.08); border: 1px solid rgba(74,158,255,.25); color: var(--ca-text-2); }
.td-note strong { display: block; margin-bottom: .2rem; color: var(--ca-text); }
/* Progression */
.td-bar { height: 12px; border-radius: 999px; background: var(--ca-bg4); overflow: hidden; margin: .35rem 0 .55rem; }
.td-bar__fill { position: relative; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #008457, #3FCB92); transition: width .8s cubic-bezier(.22,1,.36,1); overflow: hidden; }
.td-bar__fill.is-loading::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent); transform: translateX(-100%); animation: td-shine 1.6s ease-in-out infinite; }
.td-bar__fill.is-blocked { background: linear-gradient(90deg, #D97706, #F59E0B); }
.td-bar__fill.is-done { background: linear-gradient(90deg, #00A87E, #00C896); }
@keyframes td-shine { to { transform: translateX(100%); } }
.td-bar__meta { display: flex; justify-content: space-between; gap: 1rem; font-size: .8rem; color: var(--ca-text-3); padding-bottom: .8rem; }
.td-bar__meta strong { color: var(--ca-text); font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; }
.td-lock { margin: 0 0 1rem; padding: 1rem; border-radius: 14px; background: rgba(245,158,11,.09); border: 1px solid rgba(245,158,11,.3); }
.td-lock__head { display: flex; gap: .7rem; align-items: flex-start; margin-bottom: .8rem; }
.td-lock__head i { color: var(--ca-amber); font-size: 1.15rem; margin-top: .1rem; }
.td-lock__title { font-size: .9rem; font-weight: 800; color: var(--ca-text); }
.td-lock__text { font-size: .78rem; color: var(--ca-text-2); line-height: 1.5; margin-top: .15rem; }
.td-code { display: flex; gap: .6rem; flex-wrap: wrap; }
.td-code__input { flex: 1; min-width: 160px; min-height: 52px; text-align: center; letter-spacing: .55em; padding-left: .55em; font-size: 1.5rem; font-weight: 800;
  font-family: 'Space Grotesk', ui-monospace, monospace; border-radius: 12px; border: 1.5px solid var(--ca-border); background: var(--ca-bg3); color: var(--ca-text); outline: none; }
.td-code__input:focus { border-color: var(--ca-accent); box-shadow: 0 0 0 3px rgba(63,203,146,.16); }
.td-code__btn { min-height: 52px; padding: 0 1.4rem; border: 0; border-radius: 999px; cursor: pointer; font-weight: 800; font-family: inherit; color: #fff;
  background: linear-gradient(135deg, #3FCB92, #008457); display: inline-flex; align-items: center; gap: .5rem; }
.td-code__btn:disabled { opacity: .6; cursor: wait; }
.td-err { margin-top: .6rem; font-size: .78rem; color: var(--ca-negative); }
.td-ok { margin-bottom: 1rem; padding: .8rem 1rem; border-radius: 12px; background: rgba(0,200,150,.1); border: 1px solid rgba(0,200,150,.3); color: var(--ca-text-2); font-size: .82rem; }
/* Note : bloc pleine largeur, lisible quelle que soit sa longueur */
.td-notebox { margin: .35rem 0 .9rem; padding: .8rem .95rem; border-radius: 12px; background: var(--ca-bg3); border: 1px solid var(--ca-border); }
.td-notebox__k { font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--ca-text-3); margin-bottom: .3rem; }
.td-notebox__v { font-size: .88rem; line-height: 1.55; color: var(--ca-text); white-space: pre-line; overflow-wrap: anywhere; }
.td-actions { display: flex; flex-direction: column; gap: .6rem; }
@media (min-width: 600px) { .td-actions { flex-direction: row; } .td-actions > * { flex: 1; } }
.td-btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; border-radius: 999px; font-size: .88rem; font-weight: 700; text-decoration: none; cursor: pointer; font-family: inherit; }
.td-btn--primary { background: linear-gradient(135deg, #3FCB92, #008457); color: #fff; border: 0; }
.td-btn--ghost { background: transparent; color: var(--ca-text-2); border: 1px solid var(--ca-border); }
</style>
@endpush

@section('content')
<div class="td-page td-tone--{{ $tone }}">

  {{-- Statut + montant --}}
  <div class="td-hero td-tone--{{ $tone }}">
    <div class="td-hero__ico"><i class="fas {{ $icon }}"></i></div>
    <div class="td-hero__status">{{ $title }}</div>
    <div class="td-hero__amount {{ $isRej ? 'is-struck' : '' }}">{{ $sign }}{{ number_format((float) $transfer->amount, 2, ',', ' ') }} {{ $transfer->currency }}</div>
    <div class="td-hero__hint" style="font-weight:700;color:var(--ca-text);margin-top:.5rem">{{ $transfer->typeLabel() }}@if($transfer->beneficiary_name) — {{ $transfer->beneficiary_name }}@endif</div>
    <div class="td-hero__hint">{{ $hint }}</div>
  </div>

  {{-- Motif du rejet / frais à régler --}}
  @if($isRej && $transfer->admin_note)
  <div class="td-note td-note--bad"><strong>{{ __('transfer.reject_reason') }}</strong>{{ $transfer->admin_note }}</div>
  @endif
  @if($transfer->invoice && $transfer->invoice->status === 'paid' && ! $isDone && ! $isRej)
  <div class="td-ok" style="margin-bottom:.9rem"><i class="fas fa-circle-check"></i> {{ __('transfer.fees_paid') }}</div>
  @endif
  @if($feeDue)
  <div class="td-note td-note--info">
    <strong>{{ __('transfer.fee_title') }}</strong>
    {{ __('transfer.fee_text', ['amount' => number_format((float) $transfer->invoice->total, 2, ',', ' ') . ' ' . $transfer->invoice->currency]) }}
    <div style="margin-top:.35rem;font-weight:700">{{ $transfer->typeLabel() }} — {{ $transfer->beneficiary_name }} · {{ $transfer->reference }}</div>
  </div>
  @endif


  {{-- Progression du virement ("loading state") + déblocage par code --}}
  @if($showBar)
  <div class="td-card" id="td-progress" data-state-url="{{ route('client.app.transfer.state', $transfer->reference) }}" data-status="{{ $status }}" data-awaiting="{{ $awaiting ? 1 : 0 }}" data-locked="{{ $locked ? 1 : 0 }}" data-token="{{ $transfer->code_required ? $transfer->code_generated_at?->timestamp : '' }}" data-pct="{{ $pct }}">
    <div class="td-card__title">{{ __('transfer.progress_title') }}</div>
    <div class="td-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}">
      <div class="td-bar__fill {{ $isDone ? 'is-done' : ($awaiting ? 'is-blocked' : 'is-loading') }}" id="td-fill" style="width: {{ $pct }}%"></div>
    </div>
    <div class="td-bar__meta">
      <strong id="td-pct">{{ $pct }} %</strong>
      <span id="td-label">{{ $isDone ? __('transfer.progress_done') : ($awaiting ? __('transfer.progress_blocked') : __('transfer.progress_running')) }}</span>
    </div>

    {{-- Barre à 100 % mais virement pas encore validé par l'administration --}}
    <div class="td-processing" id="td-processing" @if(! ($pct >= 100 && ! $isDone && ! $isRej)) style="display:none" @endif>
      <i class="fas fa-gears"></i> {{ __('transfer.processing_notice') }}
    </div>

    @if($awaiting)
    <div class="td-lock">
      <div class="td-lock__head">
        <i class="fas fa-lock"></i>
        <div>
          <div class="td-lock__title">{{ __('transfer.code_title') }}</div>
          <div class="td-lock__text">{{ __('transfer.code_text', ['pct' => $transfer->progress]) }}</div>
        </div>
      </div>
      @if($locked)
      <div class="td-err" style="margin-top:0">{{ __('transfer.code_locked', ['minutes' => max(1, now()->diffInMinutes($transfer->code_locked_until))]) }}</div>
      @else
      @php $codeLen = strlen((string) $transfer->unlock_code) ?: \App\Models\Transfer::CODE_LENGTH; @endphp
      <form method="POST" action="{{ route('client.app.transfer.unlock', $transfer->reference) }}" class="td-code" x-data="{ busy: false, v: '', len: {{ $codeLen }} }" @submit="busy = true">
        @csrf
        <input type="text" name="code" class="td-code__input" inputmode="numeric" pattern="[0-9]{{ '{' . $codeLen . '}' }}" maxlength="{{ $codeLen }}" autocomplete="one-time-code"
               placeholder="{{ str_repeat('•', $codeLen) }}" x-model="v" @input="v = v.replace(/\D/g, '').slice(0, len)" required>
        <button type="submit" class="td-code__btn" :disabled="busy || v.length !== len">
          <i class="fas" :class="busy ? 'fa-spinner fa-spin' : 'fa-unlock'"></i> {{ __('transfer.code_button') }}
        </button>
      </form>
      @error('code')<div class="td-err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
      <div class="td-lock__text" style="margin-top:.7rem"><i class="fas fa-headset"></i> {{ __('transfer.code_help') }}</div>
      @endif
    </div>
    @elseif(! $isDone && $transfer->code_verified_at)
    <div class="td-ok" style="margin-bottom:.9rem"><i class="fas fa-circle-check"></i> {{ __('transfer.code_ok') }}</div>
    @endif
  </div>
  @endif

  {{-- Détails --}}
  <div class="td-card">
    <div class="td-card__title">{{ __('transfer.details') }}</div>
    <div class="td-row"><span class="td-row__k">{{ __('transfer.reference') }}</span>
      <span class="td-row__v td-row__v--mono">{{ $transfer->reference }}
        <button type="button" class="td-copy" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $transfer->reference }}'); this.innerHTML='<i class=&quot;fas fa-check&quot;></i>'" aria-label="{{ __('app.copied') }}"><i class="fas fa-copy"></i></button>
      </span></div>
    <div class="td-row"><span class="td-row__k">{{ __('transfer.type') }}</span><span class="td-row__v">{{ $isSend ? __('transfer.type_send') : __('transfer.type_receive') }}</span></div>
    <div class="td-row"><span class="td-row__k">{{ $isSend ? __('transfer.beneficiary') : __('transfer.sender') }}</span><span class="td-row__v">{{ $transfer->beneficiary_name ?: '—' }}</span></div>
    @if($iban !== '')
    <div class="td-row"><span class="td-row__k">IBAN</span><span class="td-row__v td-row__v--mono">{{ trim(chunk_split($iban, 4, ' ')) }}</span></div>
    @endif
    <div class="td-row"><span class="td-row__k">{{ __('transfer.amount_title') }}</span><span class="td-row__v">{{ number_format((float) $transfer->amount, 2, ',', ' ') }} {{ $transfer->currency }}</span></div>
    <div class="td-row"><span class="td-row__k">{{ __('transfer.created_at') }}</span><span class="td-row__v">{{ $transfer->created_at->translatedFormat('d/m/Y · H:i') }}</span></div>
    @if($transfer->processed_at)
    <div class="td-row"><span class="td-row__k">{{ __('transfer.processed_at') }}</span><span class="td-row__v">{{ $transfer->processed_at->translatedFormat('d/m/Y · H:i') }}</span></div>
    @endif
  </div>

  @if($transfer->note)
  <div class="td-notebox">
    <div class="td-notebox__k"><i class="fas fa-note-sticky"></i> {{ __('transfer.note') }}</div>
    <div class="td-notebox__v">{{ $transfer->note }}</div>
  </div>
  @endif

  {{-- Suivi --}}
  <div class="td-card">
    <div class="td-card__title">{{ __('transfer.timeline') }}</div>
    <ol class="td-steps">
      <li class="td-step is-done">
        <span class="td-dot"><i class="fas fa-check"></i></span>
        <div><div class="td-step__t">{{ __('transfer.step_created') }}</div><div class="td-step__s">{{ $transfer->created_at->translatedFormat('d/m/Y · H:i') }}</div></div>
      </li>
      <li class="td-step {{ $isPending ? 'is-current' : 'is-done' }}">
        <span class="td-dot"><i class="fas {{ $isPending ? 'fa-hourglass-half' : 'fa-check' }}"></i></span>
        <div><div class="td-step__t">{{ __('transfer.step_review') }}</div>
             <div class="td-step__s">{{ $isPending ? __('transfer.step_review_wait') : ($isFee ? __('transfer.step_review_fee') : __('transfer.step_review_done')) }}</div></div>
      </li>
      <li class="td-step {{ $isDone ? 'is-done' : ($isRej ? 'is-bad' : '') }}">
        <span class="td-dot"><i class="fas {{ $isRej ? 'fa-xmark' : ($isDone ? 'fa-check' : 'fa-circle') }}"></i></span>
        <div><div class="td-step__t">{{ $isRej ? __('transfer.step_rejected') : __('transfer.step_completed') }}</div>
             <div class="td-step__s">{{ $transfer->processed_at ? $transfer->processed_at->translatedFormat('d/m/Y · H:i') : '—' }}</div></div>
      </li>
    </ol>
  </div>

  @if($feeDue && $transfer->invoice->paymentIban() !== '')
  @php $inv = $transfer->invoice; @endphp
  <div class="td-card">
    <div class="td-card__title">{{ __('invoice.pay_title') }}</div>
    <div class="td-row"><span class="td-row__k">{{ __('transfer.type') }}</span><span class="td-row__v">{{ $inv->paymentTypeLabel() }}</span></div>
    <div class="td-row"><span class="td-row__k">{{ __('invoice.pay_holder') }}</span><span class="td-row__v">{{ $inv->paymentHolder() }}</span></div>
    <div class="td-row"><span class="td-row__k">{{ __('invoice.pay_iban') }}</span><span class="td-row__v td-row__v--mono">{{ \App\Models\Invoice::formatIban($inv->paymentIban()) }}</span></div>
    @if($inv->paymentBic() !== '')<div class="td-row"><span class="td-row__k">{{ __('invoice.pay_bic') }}</span><span class="td-row__v td-row__v--mono">{{ $inv->paymentBic() }}</span></div>@endif
    <div class="td-row"><span class="td-row__k">{{ __('invoice.pay_reference') }}</span><span class="td-row__v">{{ $inv->reference }} · {{ $transfer->reference }}</span></div>
    <div class="td-row"><span class="td-row__k">{{ __('invoice.amount') }}</span><span class="td-row__v">{{ number_format((float) $inv->total, 2, ',', ' ') }} {{ $inv->currency }}</span></div>
    <div style="padding:.6rem 0 .7rem;font-size:.76rem;color:var(--ca-text-3)"><i class="fas fa-circle-info"></i> {{ __('invoice.pay_hint') }}</div>
  </div>
  @endif

  <div class="td-actions">
    @if($isFee && $transfer->invoice)
    <a href="{{ route('client.app.invoices.show', $transfer->invoice) }}" class="td-btn td-btn--primary"><i class="fas fa-file-invoice"></i> {{ __('transfer.see_invoice') }}</a>
    @endif
    <a href="{{ route('client.app.movements') }}" class="td-btn td-btn--ghost"><i class="fas fa-arrow-left"></i> {{ __('transfer.back_movements') }}</a>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var box = document.getElementById('td-progress');
  if (!box || box.dataset.status === 'completed') return;
  var url = box.dataset.stateUrl, startAwait = box.dataset.awaiting === '1', startStatus = box.dataset.status;
  var startLocked = box.dataset.locked === '1', startToken = box.dataset.token || '';
  var fill = document.getElementById('td-fill'), pct = document.getElementById('td-pct');
  function tick() {
    fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) return;
        // Statut, code (généré / régénéré) ou verrouillage modifiés : on recharge pour afficher le champ de saisie du code
        if (d.status !== startStatus || d.code_required !== startAwait || d.locked !== startLocked || (d.code_token || '') !== startToken) { window.location.reload(); return; }
        fill.style.width = d.progress + '%'; pct.textContent = d.progress + ' %';
        var pr = document.getElementById('td-processing'); if (pr) pr.style.display = d.progress >= 100 ? '' : 'none';
      }).catch(function () {});
  }
  setInterval(tick, 4000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) tick(); });
})();
</script>
@endpush
