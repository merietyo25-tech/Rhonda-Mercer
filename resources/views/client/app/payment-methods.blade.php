@extends('layouts.client-app')
@section('title', __('app.payment_methods') . ' — ' . site_name())
@section('page_title', 'IBAN')
@section('back_btn', true)
@section('back_url', route('client.app.home'))

@push('styles')
<style>
.pm-body { padding:1.25rem 1.25rem 2.5rem }

/* Carte bancaire visuelle */
.pm-card {
  border-radius:22px;
  background:linear-gradient(145deg,#3A3590 0%,#231F5F 45%,#15123F 100%);
  padding:1.5rem;position:relative;overflow:hidden;
  box-shadow:0 20px 56px rgba(0,0,0,.55),0 0 0 1px rgba(255,255,255,.07);
  margin-bottom:1.5rem;
}
.pm-card::before {
  content:'';position:absolute;top:-60px;right:-60px;
  width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,200,150,.12) 0%,transparent 65%);
  pointer-events:none;
}
.pm-card::after {
  content:'';position:absolute;bottom:-70px;left:-40px;
  width:180px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(63,203,146,.08) 0%,transparent 65%);
  pointer-events:none;
}
.pm-card__top {
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:1.25rem;
}
.pm-card__brand {
  font-family:'Outfit',sans-serif;font-size:.62rem;
  font-weight:800;letter-spacing:.14em;text-transform:uppercase;
  color:rgba(255,255,255,.45);
}
.pm-card__chip {
  width:32px;height:24px;border-radius:4px;
  background:linear-gradient(135deg,#D9F2E7,#3FCB92,#2B94F7);
  box-shadow:0 2px 6px rgba(0,0,0,.35);position:relative;overflow:hidden;
}
.pm-card__chip::before {
  content:'';position:absolute;top:50%;left:0;right:0;
  height:1px;background:rgba(0,0,0,.2);transform:translateY(-50%);
}
.pm-card__chip::after {
  content:'';position:absolute;left:50%;top:0;bottom:0;
  width:1px;background:rgba(0,0,0,.18);transform:translateX(-50%);
}
.pm-card__label {
  font-size:.6rem;font-weight:600;text-transform:uppercase;
  letter-spacing:.1em;color:rgba(255,255,255,.38);margin-bottom:.25rem;
}
.pm-card__iban {
  font-family:'Space Grotesk', monospace;font-size:.95rem;font-weight:700;
  color:#fff;letter-spacing:.1em;word-break:break-all;line-height:1.5;
  margin-bottom:1.125rem;
}
.pm-card__bottom {
  display:flex;align-items:flex-end;justify-content:space-between;
  position:relative;z-index:1;
}
.pm-card__holder {
  font-family:'Outfit',sans-serif;
  font-size:.75rem;font-weight:700;color:rgba(255,255,255,.7);
  text-transform:uppercase;letter-spacing:.06em;
}
.pm-card__bic {
  font-family:monospace;font-size:.7rem;
  color:rgba(255,255,255,.42);margin-top:.15rem;letter-spacing:.06em;
}
.pm-circles { position:absolute;bottom:1rem;right:4.5rem;display:flex;pointer-events:none }
.pm-circ {width:30px;height:30px;border-radius:50%;opacity:.3}
.pm-circ:first-child{background:var(--ca-accent);margin-right:-12px}
.pm-circ:last-child {background:var(--ca-accent-l)}

/* Actions copier */
.pm-actions {
  display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.5rem;
}
.pm-copy-btn {
  display:flex;align-items:center;justify-content:center;gap:.5rem;
  padding:.75rem 1rem;
  background:var(--ca-bg3);border:1px solid var(--ca-border);
  border-radius:var(--ca-radius-sm);
  font-size:.82rem;font-weight:700;color:var(--ca-text-2);
  cursor:pointer;font-family:inherit;
  transition:background var(--ca-transition),color var(--ca-transition),border-color var(--ca-transition);
}
.pm-copy-btn:hover { background:var(--ca-bg4);color:var(--ca-text) }
.pm-copy-btn--copied { color:var(--ca-positive);border-color:rgba(0,200,150,.3) }
.pm-copy-btn i { font-size:.78rem }

/* Détail section */
.pm-detail-title {
  font-size:.72rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.08em;color:var(--ca-text-3);margin-bottom:.75rem;
}
.pm-detail-list {
  background:var(--ca-bg2);border:1px solid var(--ca-border);
  border-radius:var(--ca-radius-md);overflow:hidden;
}
.pm-detail-row {
  display:flex;align-items:center;justify-content:space-between;
  padding:.875rem 1.125rem;
  border-bottom:1px solid var(--ca-border-2);
}
.pm-detail-row:last-child { border-bottom:none }
.pm-detail-key { font-size:.8rem;color:var(--ca-text-3);font-weight:500 }
.pm-detail-val {
  font-size:.82rem;font-weight:700;color:var(--ca-text);
  font-family:'Space Grotesk', monospace;letter-spacing:.03em;
  text-align:right;max-width:60%;word-break:break-all;
}

/* Partager */
.pm-share-btn {
  width:100%;padding:.875rem;margin-top:1.25rem;
  background:none;border:1.5px solid var(--ca-teal-l);
  border-radius:var(--ca-radius-sm);
  color:var(--ca-teal-l);font-size:.9rem;font-weight:700;
  cursor:pointer;font-family:inherit;
  display:flex;align-items:center;justify-content:center;gap:.625rem;
  transition:background var(--ca-transition);
}
.pm-share-btn:hover { background:rgba(0,132,87,.1) }

/* Alerte si non configuré */
.pm-empty {
  display:flex;flex-direction:column;align-items:center;
  padding:3rem 1rem 2rem;text-align:center;
}
.pm-empty__ico {
  width:72px;height:72px;border-radius:50%;
  background:var(--ca-bg3);border:1px solid var(--ca-border);
  display:flex;align-items:center;justify-content:center;
  font-size:1.6rem;color:var(--ca-text-3);margin-bottom:1rem;
}
.pm-empty__title { font-size:.95rem;font-weight:700;color:var(--ca-text-2);margin-bottom:.5rem }
.pm-empty__sub   { font-size:.8rem;color:var(--ca-text-3);line-height:1.55 }
</style>
@endpush

@section('main_class', 'ca-main--narrow')
@section('content')
<div class="pm-body">

@php
    $bankAccount = $user->bankAccount;
    $card        = $user->card;
    $isBlocked   = ($bankAccount && $bankAccount->status === \App\Models\BankAccount::STATUS_BLOCKED)
                || ($card && $card->status === \App\Models\Card::STATUS_BLOCKED);
@endphp

@if($bankAccount)

@if($isBlocked)
<div class="alert alert-danger" style="margin-bottom:1rem">
    {{ __('app.banking_blocked_notice') }}
</div>
@endif

{{-- Carte bancaire --}}
<div class="pm-card" x-data="{ shown: true }">
  <div class="pm-circles" aria-hidden="true">
    <div class="pm-circ"></div>
    <div class="pm-circ"></div>
  </div>

  <div class="pm-card__top">
    <div class="pm-card__brand">
      <i class="fas fa-landmark" style="margin-right:.3rem;font-size:.58rem"></i>
     {{ site_name() }} &nbsp;·&nbsp; {{ __('app.account_num') }}
    </div>
    <div class="pm-card__chip" aria-hidden="true"></div>
  </div>

  <div class="pm-card__label">IBAN</div>
  <div class="pm-card__iban" x-show="shown">
    {{ chunk_split($bankAccount->iban, 4, ' ') }}
  </div>
  <div class="pm-card__iban" x-show="!shown" aria-hidden="true" style="color:rgba(255,255,255,.28);letter-spacing:.25em">
    &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;
  </div>

  <div class="pm-card__bottom">
    <div>
      <div class="pm-card__holder">{{ Str::upper(Str::words($user->name, 2, '')) }}</div>
      @if($bankAccount->bic)
      <div class="pm-card__bic">BIC · {{ $bankAccount->bic }}</div>
      @endif
    </div>
    <button @click="shown = !shown" class="pm-copy-btn" style="padding:.4rem .75rem;border-radius:8px;font-size:.72rem">
      <i :class="shown ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      <span x-text="shown ? 'Masquer' : 'Afficher'"></span>
    </button>
  </div>
</div>

{{-- Boutons copier --}}
<div class="pm-actions" x-data="pmCopy()">
  <button class="pm-copy-btn" :class="{ 'pm-copy-btn--copied': copiedIban }"
          @click="copy('{{ $bankAccount->iban }}', 'iban')">
    <i :class="copiedIban ? 'fas fa-check' : 'fas fa-copy'"></i>
    <span x-text="copiedIban ? '{{ __('app.copied') }}' : 'Copier IBAN'"></span>
  </button>
  @if($bankAccount->bic)
  <button class="pm-copy-btn" :class="{ 'pm-copy-btn--copied': copiedBic }"
          @click="copy('{{ $bankAccount->bic }}', 'bic')">
    <i :class="copiedBic ? 'fas fa-check' : 'fas fa-copy'"></i>
    <span x-text="copiedBic ? '{{ __('app.copied') }}' : 'Copier BIC'"></span>
  </button>
  @else
  <div></div>
  @endif
</div>

{{-- Détail --}}
<div class="pm-detail-title">{{ __('app.receive_title') ?? 'Coordonnées bancaires' }}</div>
<div class="pm-detail-list">
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ __('app.receive_name') }}</span>
    <span class="pm-detail-val">{{ $user->name }}</span>
  </div>
  <div class="pm-detail-row">
    <span class="pm-detail-key">IBAN</span>
    <span class="pm-detail-val">{{ chunk_split($bankAccount->iban, 4, ' ') }}</span>
  </div>
  @if($bankAccount->bic)
  <div class="pm-detail-row">
    <span class="pm-detail-key">BIC / SWIFT</span>
    <span class="pm-detail-val">{{ $bankAccount->bic }}</span>
  </div>
  @endif
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ __('app.receive_bank') ?? 'Banque' }}</span>
    <span class="pm-detail-val">{{ site_name() }} Bank</span>
  </div>
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ __('app.balance') }}</span>
    <span class="pm-detail-val" style="color:var(--ca-positive)">
      {{ $user->currency ?? \App\Models\Currency::default() }} {{ number_format((float)$user->balance, 2, ',', ' ') }}
    </span>
  </div>
</div>

@if($card)
<div class="pm-detail-title" style="margin-top:1.25rem">{{ __('app.card_title') ?? 'Carte' }}</div>
<div class="pm-detail-list">
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ __('app.receive_name') }}</span>
    <span class="pm-detail-val">{{ $card->holder_name }}</span>
  </div>
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ ucfirst($card->network) }}</span>
    <span class="pm-detail-val">{{ $card->maskedNumber() }}</span>
  </div>
  <div class="pm-detail-row">
    <span class="pm-detail-key">{{ __('app.card_expires') ?? 'Expire' }}</span>
    <span class="pm-detail-val">{{ $card->expires_at->format('m/Y') }}</span>
  </div>
</div>
@endif

{{-- Partager --}}
<button class="pm-share-btn" onclick="shareCoords()">
  <i class="fas fa-share-nodes"></i>
  {{ __('app.share_details') }}
</button>

@else

{{-- Vide --}}
<div class="pm-empty">
  <div class="pm-empty__ico"><i class="fas fa-credit-card"></i></div>
  <div class="pm-empty__title">{{ __('app.not_configured') }}</div>
  <div class="pm-empty__sub">
    Votre conseiller {{ site_name() }} configurera vos coordonnees bancaires<br>lors de la validation de votre compte.
  </div>
</div>

@endif

</div>
@endsection

@push('scripts')
<script>
function pmCopy() {
  return {
    copiedIban: false,
    copiedBic:  false,
    copy(text, which) {
      navigator.clipboard.writeText(text).then(() => {
        if (which === 'iban') {
          this.copiedIban = true;
          setTimeout(() => { this.copiedIban = false; }, 2200);
        } else {
          this.copiedBic = true;
          setTimeout(() => { this.copiedBic = false; }, 2200);
        }
      }).catch(() => {
        const el = document.createElement('textarea');
        el.value = text; el.style.position = 'fixed'; el.style.opacity = '0';
        document.body.appendChild(el); el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        if (which === 'iban') {
          this.copiedIban = true;
          setTimeout(() => { this.copiedIban = false; }, 2200);
        }
      });
    }
  };
}

function shareCoords() {
  const iban = '{{ $bankAccount->iban ?? "" }}';
  const name = '{{ addslashes($user->name) }}';
  const bic  = '{{ $bankAccount->bic ?? "" }}';
  const text = `Coordonnees bancaires {{ site_name() }}\nTitulaire : ${name}\nIBAN : ${iban}${bic ? '\nBIC : ' + bic : ''}`;
  if (navigator.share) {
    navigator.share({ title: 'Mes coordonnees bancaires', text });
  } else {
    navigator.clipboard.writeText(text);
  }
}
</script>
@endpush
