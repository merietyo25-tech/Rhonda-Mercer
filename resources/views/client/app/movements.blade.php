@extends('layouts.client-app')
@section('title', __('app.movements_title', [], $user->locale ?? 'fr') . ' — ' . site_name())
@section('page_title', __('app.movements_title'))
@section('back_btn', true)
@section('back_url', route('client.app.profile'))

@section('content')

<style>
.mv-summary{margin:.75rem 1.25rem 1rem;background:linear-gradient(135deg,#3A3590,#231F5F);border-radius:var(--ca-radius-md);padding:1rem 1.25rem;display:flex;gap:1.25rem;flex-wrap:wrap}
.mv-summary__item{flex:1;min-width:0}
.mv-summary__lbl{font-size:.65rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.2rem}
.mv-summary__val{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:800;color:#fff}
.mv-summary__val--green{color:#4ade80}
.mv-summary__val--red{color:#f87171}
.mv-list{padding:0 1.25rem}
.mv-date-sep{font-size:.65rem;color:var(--ca-text-3);text-transform:uppercase;letter-spacing:.08em;padding:.875rem 0 .35rem;font-weight:700}
.mv-item{display:flex;align-items:center;gap:.875rem;padding:.875rem;background:var(--ca-bg2);border-radius:var(--ca-radius-sm);margin-bottom:.5rem;border:1px solid var(--ca-border);transition:.15s}
.mv-item:hover{border-color:var(--ca-border-2)}
.mv-item--link{cursor:pointer;text-decoration:none;color:inherit}
.mv-item--link:hover{border-color:var(--ca-accent);background:var(--ca-bg3)}
.mv-chev{color:var(--ca-text-3);font-size:.7rem;margin-left:.25rem;flex-shrink:0}
.mv-ico{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem}
.mv-ico--credit{background:rgba(74,222,128,.15);color:#4ade80}
.mv-ico--debit{background:rgba(248,113,113,.15);color:#f87171}
.mv-ico--pending{background:rgba(251,191,36,.15);color:#fbbf24}
.mv-ico--rejected{background:rgba(148,163,184,.15);color:#94a3b8}
.mv-body{flex:1;min-width:0}
.mv-title{font-size:.875rem;font-weight:600;color:var(--ca-text-1)}
.mv-sub{font-size:.72rem;color:var(--ca-text-3);margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mv-right{text-align:right;flex-shrink:0}
.mv-amount--credit{font-size:.9375rem;font-weight:800;color:#4ade80;font-family:'Space Grotesk',sans-serif}
.mv-amount--debit{font-size:.9375rem;font-weight:800;color:#f87171;font-family:'Space Grotesk',sans-serif}
.mv-amount--pending{font-size:.9375rem;font-weight:800;color:#fbbf24;font-family:'Space Grotesk',sans-serif}
.mv-amount--rejected{font-size:.9375rem;font-weight:800;color:#94a3b8;font-family:'Space Grotesk',sans-serif;text-decoration:line-through}
.mv-bal{font-size:.7rem;color:var(--ca-text-3);margin-top:.15rem}
.mv-title .ca-badge{margin-left:.35rem;vertical-align:middle;text-transform:uppercase}
.mv-empty{text-align:center;padding:3rem 1.25rem}
.mv-empty__ico{font-size:2.5rem;color:var(--ca-text-3);margin-bottom:.875rem;opacity:.4}
.mv-empty__title{font-size:.9375rem;font-weight:700;color:var(--ca-text-2)}
.mv-empty__body{font-size:.8rem;color:var(--ca-text-3);margin-top:.35rem}
</style>

@php
  $cur      = $user->currency ?? \App\Models\Currency::default();
  $totalIn  = $merged->where('type','credit')->where('status','completed')->sum('amount');
  $totalOut = $merged->where('type','debit')->where('status','completed')->sum('amount');
@endphp

{{-- Summary --}}
<div class="mv-summary">
  <div class="mv-summary__item">
    <div class="mv-summary__lbl">{{ __('app.mv_balance_current') }}</div>
    <div class="mv-summary__val">{{ number_format((float)$user->balance, 2, ',', ' ') }} {{ $cur }}</div>
  </div>
  <div class="mv-summary__item">
    <div class="mv-summary__lbl">{{ __('app.mv_total_in') }}</div>
    <div class="mv-summary__val mv-summary__val--green">+{{ number_format($totalIn, 2, ',', ' ') }}</div>
  </div>
  <div class="mv-summary__item">
    <div class="mv-summary__lbl">{{ __('app.mv_total_out') }}</div>
    <div class="mv-summary__val mv-summary__val--red">-{{ number_format($totalOut, 2, ',', ' ') }}</div>
  </div>
</div>

@if($merged->isEmpty())
  <div class="mv-empty">
    <div class="mv-empty__ico"><i class="fas fa-wallet"></i></div>
    <div class="mv-empty__title">{{ __('app.mv_empty') }}</div>
    <div class="mv-empty__body">{{ __('app.mv_empty_body') }}</div>
  </div>
@else

<div class="mv-list">

@php $lastDate = null; @endphp

@foreach($merged as $mvt)
@php
  $dateLabel = $mvt->created_at->isToday()
      ? __('app.mv_today')
      : ($mvt->created_at->isYesterday() ? __('app.mv_yesterday') : $mvt->created_at->format('d/m/Y'));

  $isPending  = $mvt->status === 'pending';
  $isRejected = $mvt->status === 'rejected';
  $isFee      = $mvt->status === 'fee_required';

  $iconClass = $isRejected ? 'rejected' : ($isPending || $isFee ? 'pending' : $mvt->type);
  $amtClass  = $isRejected ? 'rejected' : ($isPending || $isFee ? 'pending' : $mvt->type);
  $prefix    = $mvt->type === 'credit' ? '+' : '-';
@endphp

@if($dateLabel !== $lastDate)
  <div class="mv-date-sep">{{ $dateLabel }}</div>
  @php $lastDate = $dateLabel; @endphp
@endif

@php $mvUrl = ($mvt->source === 'transfer' && ! empty($mvt->transfer_ref)) ? route('client.app.transfer.show', $mvt->transfer_ref) : null; @endphp
@if($mvUrl)<a href="{{ $mvUrl }}" class="mv-item mv-item--link">@else<div class="mv-item">@endif
  <div class="mv-ico mv-ico--{{ $iconClass }}">
    @if($mvt->source === 'transfer')
      <i class="fas fa-{{ $mvt->type === 'debit' ? 'arrow-up' : 'arrow-down' }}"></i>
    @else
      <i class="fas fa-{{ $mvt->type === 'credit' ? 'plus' : 'minus' }}"></i>
    @endif
  </div>
  <div class="mv-body">
    <div class="mv-title">
      {{ $mvt->label }}
      @if($isPending)
        <x-status-badge domain="movement" status="pending" :label="__('app.mv_status_pending')" />
      @elseif($isFee)
        <x-status-badge domain="movement" status="fee_required" :label="__('app.mv_status_fee')" />
      @elseif($isRejected)
        <x-status-badge domain="movement" status="rejected" :label="__('app.mv_status_rejected')" />
      @endif
    </div>
    @if($mvt->sub)
      <div class="mv-sub">{{ $mvt->sub }}</div>
    @endif
    <div class="mv-sub">{{ $mvt->created_at->format('H:i') }}</div>
  </div>
  <div class="mv-right">
    <div class="mv-amount--{{ $amtClass }}">
      {{ $prefix }}{{ number_format($mvt->amount, 2, ',', ' ') }} {{ $mvt->currency }}
    </div>
    @if($mvt->has_balance)
      <div class="mv-bal">{{ __('app.mv_balance_after') }} {{ number_format($mvt->balance_after, 2, ',', ' ') }}</div>
    @endif
  </div>
  @if($mvUrl)<i class="fas fa-chevron-right mv-chev" aria-hidden="true"></i>@endif
@if($mvUrl)</a>@else</div>@endif

@endforeach

</div>
@endif

@endsection
