@extends('layouts.client-app')
@section('title', __('loans.title') . ' — ' . site_name())
@section('page_title', $application->reference)
@section('back_btn', true)
@section('back_url', route('client.app.loans'))

@push('styles')
<style>
.ls-page{padding:1.25rem 1.25rem 2rem;max-width:620px}
.ls-top{border-radius:22px;padding:1.4rem 1.5rem;color:#fff;background:linear-gradient(135deg,#3A3590,#231F5F 60%,#1A1750);box-shadow:0 14px 36px rgba(49,43,129,.28);margin-bottom:1rem}
.ls-top small{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.6);margin-bottom:.3rem;font-weight:700}
.ls-amt{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;line-height:1.1}
.ls-badge{display:inline-block;margin-top:.8rem;font-size:.68rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.25rem .7rem;border-radius:999px}
.ls-badge--pending{background:rgba(245,158,11,.22);color:#FDE3A7}
.ls-badge--approved{background:rgba(63,203,146,.22);color:#B8F3D8}
.ls-badge--rejected{background:rgba(248,113,113,.22);color:#FECACA}
.ls-badge--cancelled{background:rgba(255,255,255,.16);color:#E2E8F0}
.ls-list{background:var(--ca-bg2);border:1px solid var(--ca-border);border-radius:18px;padding:.3rem 1.1rem;margin-bottom:1rem}
.ls-row{display:flex;justify-content:space-between;gap:1rem;padding:.75rem 0;border-bottom:1px solid var(--ca-border);font-size:.84rem}
.ls-row:last-child{border-bottom:0}
.ls-row span{color:var(--ca-text-3)}
.ls-row strong{color:var(--ca-text);text-align:right}
.ls-note{background:var(--ca-bg3);border-radius:14px;padding:.9rem 1rem;font-size:.84rem;line-height:1.55;color:var(--ca-text);margin-bottom:1rem}
.ls-note small{display:block;color:var(--ca-text-3);font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:.3rem}
.ls-cancel{width:100%;min-height:48px;border-radius:999px;border:1px solid rgba(220,38,38,.35);background:transparent;color:#dc2626;font-weight:800;font-family:inherit;cursor:pointer}
</style>
@endpush

@section('content')
<div class="ls-page">
  <div class="ls-top">
    <small>{{ __('loans.amount') }}</small>
    <div class="ls-amt">{{ number_format($application->amount, 2, ',', ' ') }} {{ $application->currency }}</div>
    <span class="ls-badge ls-badge--{{ $application->status }}">{{ __('loans.s_' . $application->status) }}</span>
  </div>

  <div class="ls-list">
    <div class="ls-row"><span>{{ __('loans.reference') }}</span><strong>{{ $application->reference }}</strong></div>
    <div class="ls-row"><span>{{ __('loans.purpose') }}</span><strong>{{ __('loans.p_' . $application->purpose) }}</strong></div>
    <div class="ls-row"><span>{{ __('loans.duration') }}</span><strong>{{ $application->duration_months }} {{ __('loans.months') }}</strong></div>
    <div class="ls-row"><span>{{ __('loans.monthly') }}</span><strong>{{ number_format($application->monthly_payment, 2, ',', ' ') }} {{ $application->currency }}</strong></div>
    <div class="ls-row"><span>{{ __('loans.rate') }}</span><strong>{{ rtrim(rtrim(number_format($application->interest_rate, 2, ',', ''), '0'), ',') }} %</strong></div>
    <div class="ls-row"><span>{{ __('loans.total_cost') }}</span><strong>{{ number_format($application->total_cost, 2, ',', ' ') }} {{ $application->currency }}</strong></div>
    <div class="ls-row"><span>{{ __('loans.date') }}</span><strong>{{ $application->created_at->format('d/m/Y H:i') }}</strong></div>
  </div>

  @if($application->message)
  <div class="ls-note"><small>{{ __('loans.message') }}</small>{{ $application->message }}</div>
  @endif
  @if($application->admin_note)
  <div class="ls-note"><small>{{ __('loans.advisor_note') }}</small>{{ $application->admin_note }}</div>
  @endif

  @if($application->isPending())
  <form method="POST" action="{{ route('client.app.loans.cancel', $application) }}"
        data-confirm="{{ __('loans.cancel_confirm') }}" data-confirm-title="{{ __('loans.cancel') }}" data-confirm-ok="{{ __('loans.cancel') }}" data-confirm-danger="1">
    @csrf
    <button type="submit" class="ls-cancel"><i class="fas fa-times"></i> {{ __('loans.cancel') }}</button>
  </form>
  @endif
</div>
@endsection
