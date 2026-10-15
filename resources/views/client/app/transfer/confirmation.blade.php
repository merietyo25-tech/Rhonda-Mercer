@extends('layouts.client-app')
@section('title', __('app.transfer_pending_title') . ' — ' . site_name())
@section('page_title', __('app.confirm_title'))

@section('topbar_action')
<a href="{{ route('client.app.home') }}" class="ca-topbar__action" style="color:var(--ca-text-3)">
  <i class="fas fa-times"></i>
</a>
@endsection

@section('main_class', 'ca-main--narrow')
@section('content')

@push('styles')
<style>
.trf-confirm{
  display:flex;flex-direction:column;align-items:center;
  padding:2.5rem 1.5rem 1.5rem;
  text-align:center;
}
/* ── Pending circle ── */
.trf-confirm__circle{
  width:88px;height:88px;border-radius:50%;
  background:linear-gradient(145deg,rgba(245,158,11,.2),rgba(245,158,11,.08));
  border:2px solid rgba(245,158,11,.35);
  display:flex;align-items:center;justify-content:center;
  font-size:2rem;color:#f59e0b;
  margin-bottom:1.375rem;
  box-shadow:0 0 32px rgba(245,158,11,.2);
  animation:pulseAmber 2s ease-in-out infinite;
}
@keyframes pulseAmber{
  0%,100%{box-shadow:0 0 24px rgba(245,158,11,.15)}
  50%    {box-shadow:0 0 40px rgba(245,158,11,.32)}
}
.trf-confirm__title{
  font-family:'Space Grotesk',sans-serif;
  font-size:1.25rem;font-weight:800;
  color:var(--ca-text);margin-bottom:.5rem;
}
.trf-confirm__body{
  font-size:.85rem;color:var(--ca-text-3);
  line-height:1.6;max-width:280px;margin-bottom:1.5rem;
}
/* ── Amount badge ── */
.trf-confirm__amt-lbl{font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:var(--ca-text-3);margin-bottom:.35rem}
.trf-confirm__amt{
  font-family:'Space Grotesk',sans-serif;
  font-size:2.25rem;font-weight:900;
  color:var(--ca-text);
  margin-bottom:1.5rem;
}
/* ── Details card ── */
.trf-confirm__card{
  width:100%;background:var(--ca-bg2);
  border:1px solid var(--ca-border);border-radius:18px;
  overflow:hidden;margin-bottom:1.5rem;
}
.trf-confirm__row{
  display:flex;align-items:center;justify-content:space-between;
  padding:.75rem 1.125rem;border-bottom:1px solid var(--ca-border-2);
}
.trf-confirm__row:last-child{border-bottom:none}
.trf-confirm__row-lbl{font-size:.78rem;color:var(--ca-text-3)}
.trf-confirm__row-val{font-size:.8rem;font-weight:600;color:var(--ca-text);text-align:right;max-width:60%}

/* ── Status pill ── */
.trf-confirm__status{
  display:inline-flex;align-items:center;gap:.4rem;
  padding:.35rem .9rem;border-radius:20px;font-size:.75rem;font-weight:700;
  background:rgba(245,158,11,.12);color:#f59e0b;
  border:1px solid rgba(245,158,11,.25);
  margin-bottom:1.75rem;
}

/* ── Info banner ── */
.trf-confirm__info{
  width:100%;background:rgba(0,132,87,.07);
  border:1px solid rgba(0,132,87,.2);
  border-radius:14px;padding:.875rem 1rem;
  display:flex;align-items:flex-start;gap:.625rem;
  text-align:left;margin-bottom:1.5rem;
}
.trf-confirm__info i{color:var(--ca-accent-l);flex-shrink:0;margin-top:.1rem}
.trf-confirm__info-text{font-size:.78rem;color:var(--ca-text-3);line-height:1.5}
</style>
@endpush

<div class="trf-confirm">

  {{-- Pending icon ── --}}
  <div class="trf-confirm__circle">
    <i class="fas fa-hourglass-half"></i>
  </div>

  <div class="trf-confirm__title">{{ __('app.transfer_pending_title') }}</div>
  <div class="trf-confirm__body">
    {{ __('app.transfer_pending_body') }}
  </div>

  @if($transfer)

  {{-- Status pill ── --}}
  <div class="trf-confirm__status">
    <i class="fas fa-clock" style="font-size:.7rem"></i> {{ __('app.transfer_pending_status') }}
  </div>

  {{-- Amount ── --}}
  <div class="trf-confirm__amt-lbl">{{ __('app.transfer_pending_amount_label') }}</div>
  <div class="trf-confirm__amt">
    {{ $transfer->currency }} {{ number_format($transfer->amount, 2, ',', ' ') }}
  </div>

  {{-- Details ── --}}
  <div class="trf-confirm__card">
    <div class="trf-confirm__row">
      <span class="trf-confirm__row-lbl">{{ __('app.transfer_pending_beneficiary') }}</span>
      <span class="trf-confirm__row-val">{{ $transfer->beneficiary_name }}</span>
    </div>
    <div class="trf-confirm__row">
      <span class="trf-confirm__row-lbl">{{ __('app.transfer_pending_iban') }}</span>
      <span class="trf-confirm__row-val" style="font-family:monospace;font-size:.72rem">
        {{ Str::limit($transfer->beneficiary_iban, 22) }}
      </span>
    </div>
    <div class="trf-confirm__row">
      <span class="trf-confirm__row-lbl">{{ __('app.transfer_pending_submitted_on') }}</span>
      <span class="trf-confirm__row-val">{{ $transfer->created_at->format('d/m/Y — H:i') }}</span>
    </div>
    <div class="trf-confirm__row">
      <span class="trf-confirm__row-lbl">{{ __('app.transfer_pending_reference') }}</span>
      <span class="trf-confirm__row-val" style="font-family:monospace;color:var(--ca-accent-l)">
        {{ $transfer->reference }}
      </span>
    </div>
    @if($transfer->note)
    <div class="trf-confirm__row">
      <span class="trf-confirm__row-lbl">{{ __('app.transfer_pending_note') }}</span>
      <span class="trf-confirm__row-val">{{ $transfer->note }}</span>
    </div>
    @endif
  </div>

  {{-- Info banner ── --}}
  <div class="trf-confirm__info">
    <i class="fas fa-circle-info"></i>
    <div class="trf-confirm__info-text">
      {{ __('app.transfer_pending_info') }}
    </div>
  </div>

  @endif

</div>

<div class="ca-btn-wrap" style="padding:0 1.25rem 1rem">
  <a href="{{ route('client.app.movements') }}" class="ca-btn ca-btn--accent">
    <i class="fas fa-list-ul"></i> {{ __('app.transfer_pending_view_movements') }}
  </a>
</div>

<div style="text-align:center;padding:.25rem 1.25rem 1.5rem">
  <a href="{{ route('client.app.home') }}"
     style="font-size:.82rem;color:var(--ca-teal-l);font-weight:600">
    <i class="fas fa-home" style="font-size:.75rem"></i> {{ __('app.back_home') }}
  </a>
</div>

@endsection
