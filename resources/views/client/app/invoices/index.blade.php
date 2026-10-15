@extends('layouts.client-app')
@section('title', __('app.invoices_title') . ' — ' . site_name())
@section('page_title', __('app.invoices_title'))
@section('back_btn', true)
@section('back_url', route('client.app.profile'))

@section('topbar_action')
<span style="font-size:.8rem;font-weight:800;color:var(--ca-accent-l);min-width:28px;text-align:center">
  {{ $invoices->count() }}
</span>
@endsection

@push('styles')
<style>
/* ── Summary strip ── */
.inv-strip {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: .5rem;
  margin: .75rem 1.25rem 0;
}
.inv-chip {
  background: var(--ca-bg2);
  border: 1px solid var(--ca-border);
  border-radius: 14px;
  padding: .625rem .5rem;
  text-align: center;
}
.inv-chip__val {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.125rem; font-weight: 900;
  color: var(--ca-text); line-height: 1;
}
.inv-chip__val--green { color: var(--ca-positive) }
.inv-chip__val--amber { color: var(--ca-amber) }
.inv-chip__lbl {
  font-size: .6rem; color: var(--ca-text-3);
  margin-top: .25rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: .04em;
}

/* ── Filter pills ── */
.inv-filters {
  display: flex; gap: .5rem;
  padding: .875rem 1.25rem .375rem;
  overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;
}
.inv-filters::-webkit-scrollbar { display: none }
.inv-pill {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .35rem .875rem; border-radius: 999px;
  font-size: .72rem; font-weight: 700; white-space: nowrap;
  border: 1.5px solid var(--ca-border);
  color: var(--ca-text-3); background: var(--ca-bg2); cursor: pointer;
  transition: .15s; text-decoration: none;
}
.inv-pill.active {
  background: rgba(63,203,146,.12);
  border-color: rgba(63,203,146,.3);
  color: var(--ca-accent-l);
}
.inv-pill--paid.active   { background: rgba(74,222,128,.1); border-color: rgba(74,222,128,.3); color: #4ade80 }
.inv-pill--sent.active   { background: rgba(96,165,250,.1); border-color: rgba(96,165,250,.3); color: #60a5fa }
.inv-pill--canc.active   { background: rgba(148,163,184,.1); border-color: rgba(148,163,184,.3); color: #94a3b8 }

/* ── Invoice list ── */
.inv-list {
  display: flex; flex-direction: column;
  gap: .625rem; padding: .5rem 1.25rem 1rem;
}
.inv-card {
  display: flex; align-items: center; gap: .875rem;
  background: var(--ca-bg2);
  border: 1px solid var(--ca-border);
  border-radius: 18px;
  padding: 1rem 1rem 1rem 1.125rem;
  text-decoration: none;
  transition: .18s;
  position: relative; overflow: hidden;
}
.inv-card:hover  { background: var(--ca-bg3) }
.inv-card:active { transform: scale(.99) }
.inv-card__stripe {
  position: absolute; left: 0; top: 0; bottom: 0;
  width: 4px; border-radius: 18px 0 0 18px;
}
.inv-card__stripe--sent      { background: linear-gradient(180deg,#60a5fa,#3b82f6) }
.inv-card__stripe--paid      { background: linear-gradient(180deg,#4ade80,#22c55e) }
.inv-card__stripe--cancelled { background: linear-gradient(180deg,#94a3b8,#64748b) }

.inv-card__ico {
  width: 46px; height: 46px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0;
}
.inv-card__ico--sent      { background: rgba(96,165,250,.14); color: #60a5fa }
.inv-card__ico--paid      { background: rgba(74,222,128,.14); color: #4ade80 }
.inv-card__ico--cancelled { background: rgba(148,163,184,.14); color: #94a3b8 }

.inv-card__body { flex: 1; min-width: 0 }
.inv-card__ref  {
  font-size: .8rem; font-weight: 700; color: var(--ca-text);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.inv-card__desc {
  font-size: .72rem; color: var(--ca-text-3);
  margin-top: .1rem; white-space: nowrap;
  overflow: hidden; text-overflow: ellipsis;
}
.inv-card__date { font-size: .68rem; color: var(--ca-text-3); margin-top: .2rem }

.inv-card__right { text-align: right; flex-shrink: 0 }
.inv-card__amount {
  font-family: 'Space Grotesk', sans-serif;
  font-size: .975rem; font-weight: 800;
  color: var(--ca-text); line-height: 1;
}
.inv-card__amount--paid { color: #4ade80 }
.inv-card__right .ca-badge { margin-top: .35rem; text-transform: uppercase }

/* ── Empty state ── */
.inv-empty {
  text-align: center;
  padding: 3.5rem 1.25rem;
}
.inv-empty__ico {
  font-size: 3rem; opacity: .2;
  color: var(--ca-text-3);
  margin-bottom: 1rem;
}
.inv-empty__title {
  font-size: .9375rem; font-weight: 700;
  color: var(--ca-text-2);
}
.inv-empty__sub {
  font-size: .8rem; color: var(--ca-text-3);
  margin-top: .375rem; line-height: 1.6;
}
</style>
@endpush

@section('content')

@php
  $currency  = $user->currency ?? config('solberg.default_currency', 'EUR');
  $cntSent   = $invoices->where('status', 'sent')->count();
  $cntPaid   = $invoices->where('status', 'paid')->count();
  $totalPaid = $invoices->where('status', 'paid')->sum('total');

  $filter    = request('filter', 'all');
  $displayed = match($filter) {
      'sent'      => $invoices->where('status', 'sent'),
      'paid'      => $invoices->where('status', 'paid'),
      'cancelled' => $invoices->where('status', 'cancelled'),
      default     => $invoices,
  };

  $statusLabels = [
      'sent'      => __('app.invoice_status_sent'),
      'paid'      => __('app.invoice_status_paid'),
      'cancelled' => __('app.invoice_status_cancelled'),
  ];
@endphp

{{-- ── Summary chips ── --}}
<div class="inv-strip">
  <div class="inv-chip">
    <div class="inv-chip__val">{{ $invoices->count() }}</div>
    <div class="inv-chip__lbl">Total</div>
  </div>
  <div class="inv-chip">
    <div class="inv-chip__val inv-chip__val--amber">{{ $cntSent }}</div>
    <div class="inv-chip__lbl">{{ __('app.invoice_status_sent') }}</div>
  </div>
  <div class="inv-chip">
    <div class="inv-chip__val inv-chip__val--green">{{ $cntPaid }}</div>
    <div class="inv-chip__lbl">{{ __('app.invoice_status_paid') }}</div>
  </div>
</div>

{{-- ── Filter pills ── --}}
<div class="inv-filters">
  <a href="{{ route('client.app.invoices') }}"
     class="inv-pill {{ $filter === 'all' ? 'active' : '' }}">
    <i class="fas fa-list-ul" style="font-size:.6rem"></i>
    Tout ({{ $invoices->count() }})
  </a>
  <a href="{{ route('client.app.invoices', ['filter' => 'sent']) }}"
     class="inv-pill inv-pill--sent {{ $filter === 'sent' ? 'active' : '' }}">
    <i class="fas fa-clock" style="font-size:.6rem"></i>
    {{ __('app.invoice_status_sent') }} ({{ $cntSent }})
  </a>
  <a href="{{ route('client.app.invoices', ['filter' => 'paid']) }}"
     class="inv-pill inv-pill--paid {{ $filter === 'paid' ? 'active' : '' }}">
    <i class="fas fa-circle-check" style="font-size:.6rem"></i>
    {{ __('app.invoice_status_paid') }} ({{ $cntPaid }})
  </a>
  @if($invoices->where('status','cancelled')->count() > 0)
  <a href="{{ route('client.app.invoices', ['filter' => 'cancelled']) }}"
     class="inv-pill inv-pill--canc {{ $filter === 'cancelled' ? 'active' : '' }}">
    <i class="fas fa-ban" style="font-size:.6rem"></i>
    {{ __('app.invoice_status_cancelled') }} ({{ $invoices->where('status','cancelled')->count() }})
  </a>
  @endif
</div>

{{-- ── List ── --}}
@if($displayed->isEmpty())
<div class="inv-empty">
  <div class="inv-empty__ico"><i class="fas fa-file-invoice"></i></div>
  <div class="inv-empty__title">{{ __('app.invoice_empty') }}</div>
  <div class="inv-empty__sub">{{ __('app.invoice_empty_sub') }}</div>
</div>
@else
<div class="inv-list">
  @foreach($displayed as $invoice)
  @php
    $st    = $invoice->status;
    $label = $statusLabels[$st] ?? $st;
    $icon  = config("solberg.status_badges.invoice.$st.icon", 'fa-file-invoice');
  @endphp
  <a href="{{ route('client.app.invoices.show', $invoice) }}" class="inv-card">
    <div class="inv-card__stripe inv-card__stripe--{{ $st }}"></div>
    <div class="inv-card__ico inv-card__ico--{{ $st }}">
      <i class="fas {{ $icon }}"></i>
    </div>
    <div class="inv-card__body">
      <div class="inv-card__ref">{{ $invoice->reference }}</div>
      @if($invoice->description)
      <div class="inv-card__desc">{{ Str::limit($invoice->description, 42) }}</div>
      @endif
      <div class="inv-card__date">
        <i class="fas fa-calendar-days" style="font-size:.6rem;margin-right:.2rem"></i>
        {{ $invoice->issue_date?->format('d/m/Y') }}
        @if($invoice->due_date && $st === 'sent')
          &nbsp;·&nbsp;
          <i class="fas fa-hourglass-half" style="font-size:.58rem;margin-right:.2rem;color:{{ $invoice->due_date->isPast() ? '#f87171' : 'var(--ca-amber)' }}"></i>
          {{ $invoice->due_date->format('d/m/Y') }}
        @endif
      </div>
    </div>
    <div class="inv-card__right">
      <div class="inv-card__amount {{ $st === 'paid' ? 'inv-card__amount--paid' : '' }}">
        {{ number_format($invoice->total, 2, ',', ' ') }}&nbsp;{{ $invoice->currency ?? $currency }}
      </div>
      <x-status-badge domain="invoice" :status="$st" :label="$label" />
    </div>
  </a>
  @endforeach
</div>
@endif

@endsection
