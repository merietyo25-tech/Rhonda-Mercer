@extends('layouts.client-app')
@section('title', __('app.receive_title') . ' — ' . site_name())
@section('page_title', __('app.receive_title'))
@section('back_btn', true)
@section('back_url', route('client.app.transfers'))

@section('main_class', 'ca-main--narrow')
@section('content')

@push('styles')
<style>
/* ── Hero card ── */
.rcv-hero{
  margin:.875rem 1.25rem 0;
  background:linear-gradient(145deg,#3A3590,#231F5F);
  border-radius:20px;padding:1.625rem 1.5rem;
  position:relative;overflow:hidden;
  box-shadow:0 12px 32px rgba(0,0,0,.4);
  text-align:center;
}
.rcv-hero::before{
  content:'';position:absolute;bottom:-50px;right:-50px;
  width:180px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(63,203,146,.18) 0%,transparent 70%);
  pointer-events:none;
}
.rcv-hero__avatar{
  width:68px;height:68px;border-radius:50%;
  background:linear-gradient(145deg,rgba(0,132,87,.6),rgba(0,132,87,.3));
  border:2px solid rgba(255,255,255,.15);
  display:flex;align-items:center;justify-content:center;
  font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:800;
  color:#fff;margin:0 auto .75rem;
}
.rcv-hero__name{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;color:#fff;margin-bottom:.2rem}
.rcv-hero__badge{
  display:inline-flex;align-items:center;gap:.35rem;
  font-size:.68rem;font-weight:600;
  color:rgba(63,203,146,.9);
  background:rgba(63,203,146,.12);border:1px solid rgba(63,203,146,.25);
  border-radius:999px;padding:.2rem .65rem;margin-bottom:.25rem;
}

/* ── IBAN block ── */
.rcv-iban-block{
  margin:1rem 1.25rem .5rem;
  background:var(--ca-bg2);border:1px solid var(--ca-border);
  border-radius:18px;padding:1.25rem;
}
.rcv-iban-label{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;font-weight:700;color:var(--ca-text-3);margin-bottom:.625rem}
.rcv-iban-value{
  font-family:'Space Grotesk',monospace;
  font-size:1.0625rem;font-weight:700;color:var(--ca-text);
  letter-spacing:.04em;line-height:1.4;
  word-break:break-all;margin-bottom:1rem;
}
.rcv-copy-btn{
  display:flex;align-items:center;justify-content:center;gap:.5rem;
  width:100%;padding:.625rem;border-radius:12px;
  background:rgba(0,132,87,.1);border:1px solid rgba(0,132,87,.22);
  color:var(--ca-teal-l);font-size:.8rem;font-weight:600;cursor:pointer;
  transition:.18s;
}
.rcv-copy-btn:active{background:rgba(0,132,87,.2)}
.rcv-copy-btn--ok{background:rgba(74,222,128,.1)!important;border-color:rgba(74,222,128,.25)!important;color:var(--ca-positive)!important}

/* ── Details card ── */
.rcv-details{
  margin:0 1.25rem;
  background:var(--ca-bg2);border:1px solid var(--ca-border);border-radius:18px;
  overflow:hidden;
}
.rcv-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:.8rem 1.125rem;border-bottom:1px solid var(--ca-border-2);
}
.rcv-row:last-child{border-bottom:none}
.rcv-row__left{display:flex;align-items:center;gap:.625rem}
.rcv-row__ico{
  width:30px;height:30px;border-radius:9px;
  background:var(--ca-bg3);
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;color:var(--ca-text-3);flex-shrink:0;
}
.rcv-row__label{font-size:.78rem;color:var(--ca-text-3)}
.rcv-row__val{font-size:.8rem;font-weight:600;color:var(--ca-text);text-align:right;max-width:55%}
</style>
@endpush

@php
  $iban = $user->bankAccount?->iban ?: 'Non renseigné';
  $bic  = $user->bankAccount?->bic ?: '—';
  $currency = $user->currency ?? \App\Models\Currency::default();
  $shareText = "IBAN : {$iban}\nBIC : {$bic}\nTitulaire : {$user->name}\nBanque : " . site_name() . " Financial";
@endphp

{{-- Hero ── --}}
<div class="rcv-hero">
  <div class="rcv-hero__avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
  <div class="rcv-hero__name">{{ $user->name }}</div>
  <div class="rcv-hero__badge"><i class="fas fa-star" style="font-size:.55rem"></i> {{ __('app.premium_member') }}</div>
</div>

{{-- IBAN ── --}}
<div class="rcv-iban-block">
  <div class="rcv-iban-label">{{ __('app.receive_iban') }}</div>
  <div class="rcv-iban-value">{{ $user->bankAccount ? trim(chunk_split($iban, 4, " ")) : $iban }}</div>
  <button class="rcv-copy-btn" id="copyBtn" onclick="copyIban(this, '{{ addslashes($iban) }}')">
    <i class="fas fa-copy" id="copyIcon"></i>
    <span id="copyTxt">{{ __('app.copy_iban') }}</span>
  </button>
</div>

{{-- Details ── --}}
<div class="rcv-details">
  @foreach([
    ['fa-building-columns', __('app.receive_bic'),  $bic],
    ['fa-user',             __('app.receive_name'), $user->name],
    ['fa-landmark',         __('app.receive_bank'), site_name() . ' Financial'],
    ['fa-coins',            'Devise',               $currency],
    ['fa-envelope',         'Email',                $user->email],
  ] as [$icon, $label, $val])
  <div class="rcv-row">
    <div class="rcv-row__left">
      <div class="rcv-row__ico"><i class="fas {{ $icon }}"></i></div>
      <span class="rcv-row__label">{{ $label }}</span>
    </div>
    <span class="rcv-row__val">{{ $val }}</span>
  </div>
  @endforeach
</div>

{{-- Notice ── --}}
<div style="margin:.875rem 1.25rem;padding:.75rem 1rem;background:rgba(0,132,87,.06);border:1px solid rgba(0,132,87,.18);border-radius:14px;display:flex;align-items:flex-start;gap:.5rem">
  <i class="fas fa-circle-info" style="color:var(--ca-teal-l);font-size:.8rem;margin-top:.1rem;flex-shrink:0"></i>
  <span style="font-size:.75rem;color:var(--ca-text-3);line-height:1.5">
    Partagez ces coordonnées bancaires pour recevoir des fonds directement sur votre compte {{ site_name() }}.
  </span>
</div>

{{-- Actions ── --}}
<div class="ca-btn-wrap">
  <button class="ca-btn ca-btn--accent"
          onclick="if(navigator.share){navigator.share({title:'Mes coordonnées ' . site_name(),text:`{{ addslashes($shareText) }}`}).catch(()=>{})}else{copyIban(null,'{{ addslashes($shareText) }}',true)}">
    <i class="fas fa-share-nodes"></i> {{ __('app.share_details') }}
  </button>
</div>

<div class="ca-divider">{{ __('app.or') }}</div>

<div class="ca-btn-wrap" style="padding-top:0">
  <a href="{{ route('client.app.transfers') }}" class="ca-btn ca-btn--ghost">
    <i class="fas fa-arrow-left"></i> Retour aux virements
  </a>
</div>

<div style="height:1rem"></div>

@push('scripts')
<script>
function copyIban(btn, text, silent) {
  navigator.clipboard.writeText(text).then(() => {
    if (btn) {
      btn.classList.add('rcv-copy-btn--ok');
      document.getElementById('copyIcon').className = 'fas fa-check';
      document.getElementById('copyTxt').textContent = 'Copié !';
      setTimeout(() => {
        btn.classList.remove('rcv-copy-btn--ok');
        document.getElementById('copyIcon').className = 'fas fa-copy';
        document.getElementById('copyTxt').textContent = '{{ __("app.copy_iban") }}';
      }, 2200);
    }
  }).catch(() => {});
}
</script>
@endpush

@endsection
