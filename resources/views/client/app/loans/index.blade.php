@extends('layouts.client-app')
@section('title', __('loans.title') . ' — ' . site_name())
@section('page_title', __('loans.nav'))
@section('back_btn', true)
@section('back_url', route('client.app.home'))

@push('styles')
<style>
.ln-page{padding:1.25rem 1.25rem 2rem;max-width:760px}
.ln-hero{position:relative;overflow:hidden;border-radius:22px;padding:1.4rem 1.5rem;color:#fff;background:linear-gradient(135deg,#3A3590 0%,#231F5F 60%,#1A1750 100%);box-shadow:0 14px 36px rgba(49,43,129,.28)}
.ln-hero::after{content:'';position:absolute;right:-70px;top:-70px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(63,203,146,.28),transparent 65%)}
.ln-hero h2{font-size:1.15rem;font-weight:800;margin:0 0 .4rem;position:relative}
.ln-hero p{font-size:.84rem;line-height:1.55;color:rgba(255,255,255,.82);margin:0 0 1.1rem;max-width:46ch;position:relative}
.ln-btn{display:inline-flex;align-items:center;justify-content:center;gap:.55rem;min-height:48px;padding:.7rem 1.4rem;border-radius:999px;border:0;background:linear-gradient(135deg,#3FCB92,#008457);color:#fff;font-weight:800;font-size:.9rem;font-family:inherit;cursor:pointer;box-shadow:0 8px 22px rgba(0,132,87,.3);position:relative}
.ln-btn--ghost{background:var(--ca-bg3);color:var(--ca-text);box-shadow:none;border:1px solid var(--ca-border)}
.ln-h{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--ca-text-3);margin:1.5rem 0 .6rem}
.ln-item{display:flex;align-items:center;gap:.9rem;padding:.9rem 1rem;margin-bottom:.55rem;background:var(--ca-bg2);border:1px solid var(--ca-border);border-radius:16px;text-decoration:none;color:inherit;transition:.15s}
.ln-item:hover{border-color:var(--ca-accent);transform:translateY(-1px)}
.ln-ico{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(0,132,87,.12);color:var(--ca-accent-l,#008457);flex-shrink:0}
.ln-main{flex:1;min-width:0}
.ln-amt{font-family:'Space Grotesk',sans-serif;font-weight:800;font-size:1.05rem;color:var(--ca-text)}
.ln-sub{font-size:.74rem;color:var(--ca-text-3);margin-top:.15rem}
.ln-badge{font-size:.66rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.22rem .6rem;border-radius:999px;white-space:nowrap}
.ln-badge--pending{background:rgba(245,158,11,.16);color:#b45309}
.ln-badge--approved{background:rgba(22,163,74,.14);color:#15803d}
.ln-badge--rejected{background:rgba(220,38,38,.12);color:#b91c1c}
.ln-badge--cancelled{background:rgba(100,116,139,.16);color:#475569}
.ln-empty{text-align:center;padding:2.2rem 1rem;color:var(--ca-text-3)}
.ln-empty i{font-size:2rem;margin-bottom:.7rem;color:var(--ca-text-3)}
.ln-empty strong{display:block;color:var(--ca-text);margin-bottom:.25rem}
</style>
@endpush

@section('content')
<div class="ln-page">
  <div class="ln-hero">
    <h2>{{ __('loans.title') }}</h2>
    <p>{{ __('loans.intro') }}</p>
    @if($hasPending)
      <span class="ln-badge ln-badge--pending" style="position:relative">{{ __('loans.already_pending') }}</span>
    @else
      <a href="{{ route('client.app.loans.create') }}" class="ln-btn"><i class="fas fa-plus"></i> {{ __('loans.new_request') }}</a>
    @endif
  </div>

  <div class="ln-h">{{ __('loans.my_requests') }}</div>
  @forelse($applications as $a)
  <a href="{{ route('client.app.loans.show', $a) }}" class="ln-item">
    <span class="ln-ico"><i class="fas fa-hand-holding-dollar"></i></span>
    <span class="ln-main">
      <div class="ln-amt">{{ number_format($a->amount, 2, ',', ' ') }} {{ $a->currency }}</div>
      <div class="ln-sub">{{ __('loans.p_' . $a->purpose) }} · {{ $a->duration_months }} {{ __('loans.months') }} · {{ $a->created_at->format('d/m/Y') }}</div>
    </span>
    <span class="ln-badge ln-badge--{{ $a->status }}">{{ __('loans.s_' . $a->status) }}</span>
  </a>
  @empty
  <div class="ln-empty">
    <i class="fas fa-hand-holding-dollar"></i>
    <strong>{{ __('loans.empty') }}</strong>
    {{ __('loans.empty_hint') }}
  </div>
  @endforelse
</div>
@endsection
