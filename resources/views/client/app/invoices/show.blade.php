@extends('layouts.client-app')
@section('title', $invoice->reference . ' — ' . site_name())
@section('page_title', __('app.invoice_detail'))
@section('back_btn', true)
@section('back_url', route('client.app.invoices'))

@push('styles')
<style>
/* ── Status banner ── */
.invd-banner {
  margin: .875rem 1.25rem 0;
  border-radius: 16px;
  padding: 1rem 1.25rem;
  display: flex; align-items: center; gap: .875rem;
}
.invd-banner--sent      { background: rgba(96,165,250,.1);  border: 1px solid rgba(96,165,250,.25) }
.invd-banner--paid      { background: rgba(74,222,128,.1);  border: 1px solid rgba(74,222,128,.25) }
.invd-banner--cancelled { background: rgba(148,163,184,.1); border: 1px solid rgba(148,163,184,.2) }

.invd-banner__ico {
  width: 44px; height: 44px; border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem; flex-shrink: 0;
}
.invd-banner--sent      .invd-banner__ico { background: rgba(96,165,250,.18); color: #60a5fa }
.invd-banner--paid      .invd-banner__ico { background: rgba(74,222,128,.18); color: #4ade80 }
.invd-banner--cancelled .invd-banner__ico { background: rgba(148,163,184,.18); color: #94a3b8 }

.invd-banner__title {
  font-size: .875rem; font-weight: 700; line-height: 1.2;
}
.invd-banner--sent      .invd-banner__title { color: #93c5fd }
.invd-banner--paid      .invd-banner__title { color: #86efac }
.invd-banner--cancelled .invd-banner__title { color: #cbd5e1 }
.invd-banner__sub { font-size: .72rem; color: var(--ca-text-3); margin-top: .15rem }

/* ── Total card ── */
.invd-total {
  margin: .875rem 1.25rem 0;
  background: linear-gradient(145deg,#3A3590 0%,#231F5F 50%,#15123F 100%);
  border-radius: 20px;
  padding: 1.25rem 1.375rem;
  text-align: center;
  position: relative; overflow: hidden;
  box-shadow: 0 16px 48px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.06);
}
.invd-total::before {
  content: ''; position: absolute; top: -60px; right: -60px;
  width: 200px; height: 200px; border-radius: 50%;
  background: radial-gradient(circle,rgba(13,207,220,.08) 0%,transparent 65%);
  pointer-events: none;
}
.invd-total__lbl {
  font-size: .65rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: .1em;
  color: rgba(255,255,255,.38); margin-bottom: .5rem;
}
.invd-total__amount {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 2.25rem; font-weight: 900;
  color: #fff; letter-spacing: -.03em; line-height: 1;
}
.invd-total__amount sup {
  font-size: 1rem; font-weight: 600;
  vertical-align: super; margin-right: .25rem;
  color: var(--ca-accent-l);
}
.invd-total__ref {
  font-size: .72rem; color: rgba(255,255,255,.35);
  margin-top: .5rem; letter-spacing: .04em;
}

/* ── Info section ── */
.invd-section {
  margin: 1rem 1.25rem 0;
}
.invd-section__title {
  font-size: .68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .08em;
  color: var(--ca-text-3); margin-bottom: .5rem;
}
.invd-card {
  background: var(--ca-bg2);
  border: 1px solid var(--ca-border);
  border-radius: 16px;
  overflow: hidden;
}
.invd-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: .75rem 1rem; gap: .75rem;
  border-bottom: 1px solid var(--ca-border);
}
.invd-row:last-child { border-bottom: none }
.invd-row__lbl {
  font-size: .78rem; color: var(--ca-text-3); font-weight: 500;
  display: flex; align-items: center; gap: .45rem;
}
.invd-row__lbl i { width: 14px; text-align: center; font-size: .7rem }
.invd-row__val {
  font-size: .82rem; font-weight: 600; color: var(--ca-text);
  text-align: right;
}
.invd-row__val--green { color: #4ade80 }
.invd-row__val--blue  { color: #60a5fa }

/* ── Line items ── */
.invd-item {
  padding: .875rem 1rem;
  border-bottom: 1px solid var(--ca-border);
}
.invd-item:last-child { border-bottom: none }
.invd-item__name {
  font-size: .845rem; font-weight: 600; color: var(--ca-text);
  margin-bottom: .2rem;
}
.invd-item__detail {
  font-size: .72rem; color: var(--ca-text-3);
  display: flex; gap: .75rem; flex-wrap: wrap;
}
.invd-item__price {
  font-family: 'Space Grotesk', sans-serif;
  font-size: .9rem; font-weight: 800; color: var(--ca-text);
  float: right; margin-top: -.1rem;
}

/* ── Totals summary ── */
.invd-totals { padding: .25rem 0 }
.invd-totals__row {
  display: flex; justify-content: space-between;
  padding: .5rem 1rem;
  font-size: .82rem; color: var(--ca-text-2);
}
.invd-totals__row--total {
  font-weight: 800; font-size: .9rem;
  color: var(--ca-text); border-top: 1px solid var(--ca-border);
  padding-top: .75rem; margin-top: .25rem;
}
.invd-totals__row--total .invd-totals__val {
  color: var(--ca-accent-l);
  font-family: 'Space Grotesk', sans-serif; font-size: 1rem;
}

/* ── Note ── */
.invd-note {
  background: rgba(63,203,146,.07);
  border: 1px solid rgba(63,203,146,.2);
  border-radius: 12px;
  padding: .875rem 1rem;
  font-size: .8rem; color: rgba(63,203,146,.85);
  line-height: 1.55;
}
.invd-note i { margin-right: .45rem; opacity: .7 }

/* ── Bottom space ── */
.invd-spacer { height: 1.5rem }
</style>
@endpush

@section('content')
@php
  $currency = $invoice->currency ?? ($user->currency ?? config('solberg.default_currency', 'EUR'));
  $st       = $invoice->status;

  $statusTitles = [
      'sent'      => __('app.invoice_status_sent'),
      'paid'      => __('app.invoice_status_paid'),
      'cancelled' => __('app.invoice_status_cancelled'),
  ];
  $statusIcons  = [
      'sent'      => 'fa-clock',
      'paid'      => 'fa-circle-check',
      'cancelled' => 'fa-ban',
  ];
  $statusSubs = [
      'sent'      => $invoice->due_date
                       ? __('app.invoice_due') . ' : ' . $invoice->due_date->format('d/m/Y')
                       : '',
      'paid'      => $invoice->paid_at
                       ? __('app.invoice_status_paid') . ' le ' . $invoice->paid_at->format('d/m/Y')
                       : '',
      'cancelled' => '',
  ];
@endphp

{{-- ── Status banner ── --}}
<div class="invd-banner invd-banner--{{ $st }}">
  <div class="invd-banner__ico">
    <i class="fas {{ $statusIcons[$st] ?? 'fa-file-invoice' }}"></i>
  </div>
  <div>
    <div class="invd-banner__title">{{ $statusTitles[$st] ?? $st }}</div>
    @if($statusSubs[$st] ?? '')
    <div class="invd-banner__sub">{{ $statusSubs[$st] }}</div>
    @endif
  </div>
</div>

{{-- ── Total card ── --}}
<div class="invd-total">
  <div class="invd-total__lbl">{{ __('app.invoice_total') }}</div>
  <div class="invd-total__amount">
    <sup>{{ $currency }}</sup>{{ number_format($invoice->total, 2, ',', ' ') }}
  </div>
  <div class="invd-total__ref">{{ $invoice->reference }}</div>
</div>

{{-- ── Téléchargement ── --}}
<div style="margin:.875rem 1.25rem 0">
  <a href="{{ route('client.app.invoices.pdf', $invoice) }}" target="_blank" rel="noopener"
     style="display:flex;align-items:center;justify-content:center;gap:.55rem;width:100%;padding:.8rem 1rem;border-radius:14px;border:1px solid var(--ca-border);background:var(--ca-bg2);color:var(--ca-text);font-size:.85rem;font-weight:700;text-decoration:none">
    <i class="fas fa-file-pdf" style="color:var(--ca-accent-l)"></i> {{ __('app.invoice_download') }}
  </a>
</div>

{{-- ── Invoice info ── --}}
<div class="invd-section">
  <div class="invd-section__title">{{ __('app.invoice_ref') }}</div>
  <div class="invd-card">
    <div class="invd-row">
      <span class="invd-row__lbl"><i class="fas fa-hashtag"></i>{{ __('app.invoice_ref') }}</span>
      <span class="invd-row__val" style="font-family:'Space Grotesk',sans-serif;font-size:.78rem">{{ $invoice->reference }}</span>
    </div>
    <div class="invd-row">
      <span class="invd-row__lbl"><i class="fas fa-calendar-plus"></i>{{ __('app.invoice_date') }}</span>
      <span class="invd-row__val">{{ $invoice->issue_date?->format('d/m/Y') ?? '—' }}</span>
    </div>
    @if($invoice->due_date && $st !== 'paid')
    <div class="invd-row">
      <span class="invd-row__lbl">
        <i class="fas fa-calendar-check"
           style="{{ $invoice->due_date->isPast() && $st === 'sent' ? 'color:#f87171' : '' }}"></i>
        {{ __('app.invoice_due') }}
      </span>
      <span class="invd-row__val {{ $invoice->due_date->isPast() && $st === 'sent' ? 'invd-row__val--' : '' }}"
            style="{{ $invoice->due_date->isPast() && $st === 'sent' ? 'color:#f87171' : '' }}">
        {{ $invoice->due_date->format('d/m/Y') }}
        @if($invoice->due_date->isPast() && $st === 'sent')
          <span style="font-size:.65rem;font-weight:700;background:rgba(239,68,68,.15);color:#f87171;padding:.1rem .4rem;border-radius:6px;margin-left:.3rem">
            En retard
          </span>
        @endif
      </span>
    </div>
    @endif
    @if($invoice->paid_at)
    <div class="invd-row">
      <span class="invd-row__lbl"><i class="fas fa-circle-check" style="color:#4ade80"></i>{{ __('app.invoice_status_paid') }}</span>
      <span class="invd-row__val invd-row__val--green">{{ $invoice->paid_at->format('d/m/Y') }}</span>
    </div>
    @endif
  </div>
</div>

{{-- ── Line items ── --}}
@if(!empty($invoice->items) && count($invoice->items) > 0)
<div class="invd-section" style="margin-top:.875rem">
  <div class="invd-section__title">{{ __('app.invoice_items') }}</div>
  <div class="invd-card">
    @foreach($invoice->items as $item)
    <div class="invd-item">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem">
        <div class="invd-item__name">{{ $invoice->itemDescription($item) }}</div>
        <div class="invd-item__price">
          {{ number_format(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1), 2, ',', ' ') }}&nbsp;{{ $currency }}
        </div>
      </div>
      <div class="invd-item__detail">
        @if(isset($item['quantity']) && $item['quantity'] > 1)
        <span><i class="fas fa-xmark" style="font-size:.55rem"></i> {{ $item['quantity'] }}</span>
        @endif
        @if(isset($item['unit_price']))
        <span>{{ number_format($item['unit_price'], 2, ',', ' ') }} {{ $currency }} / unité</span>
        @endif
      </div>
    </div>
    @endforeach

    {{-- Totaux --}}
    <div class="invd-totals">
      <div class="invd-totals__row">
        <span>{{ __('app.invoice_subtotal') }}</span>
        <span class="invd-totals__val">{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
      @if($invoice->tax_rate > 0)
      <div class="invd-totals__row">
        <span>{{ __('app.invoice_tax') }} ({{ $invoice->tax_rate }}%)</span>
        <span class="invd-totals__val">{{ number_format($invoice->tax_amount, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
      @endif
      <div class="invd-totals__row invd-totals__row--total">
        <span>{{ __('app.invoice_total') }}</span>
        <span class="invd-totals__val">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
    </div>
  </div>
</div>
@elseif($invoice->description)
<div class="invd-section" style="margin-top:.875rem">
  <div class="invd-section__title">{{ __('app.invoice_items') }}</div>
  <div class="invd-card">
    <div class="invd-item">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem">
        <div class="invd-item__name">{{ $invoice->displayDescription() }}</div>
        <div class="invd-item__price">{{ number_format($invoice->total, 2, ',', ' ') }}&nbsp;{{ $currency }}</div>
      </div>
    </div>
    <div class="invd-totals">
      @if($invoice->tax_rate > 0)
      <div class="invd-totals__row">
        <span>{{ __('app.invoice_subtotal') }}</span>
        <span>{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
      <div class="invd-totals__row">
        <span>{{ __('app.invoice_tax') }} ({{ $invoice->tax_rate }}%)</span>
        <span>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
      @endif
      <div class="invd-totals__row invd-totals__row--total">
        <span>{{ __('app.invoice_total') }}</span>
        <span class="invd-totals__val">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $currency }}</span>
      </div>
    </div>
  </div>
</div>
@endif

{{-- ── Virement concerné ── --}}
@if($invoice->linkedTransfer)
@php $lt = $invoice->linkedTransfer; @endphp
<div class="invd-section" style="margin-top:.875rem">
  <div class="invd-section__title">{{ __('transfer.detail_title') }}</div>
  <div class="invd-note" style="display:block">
    @foreach([[__('transfer.reference'), $lt->reference], [__('transfer.type'), $lt->typeLabel()], [__('transfer.beneficiary'), $lt->beneficiary_name], ...($lt->beneficiary_iban ? [[__('invoice.pay_iban'), \App\Models\Invoice::formatIban((string) $lt->beneficiary_iban)]] : []), [__('invoice.amount'), number_format((float) $lt->amount, 2, ',', ' ') . ' ' . $lt->currency]] as [$k, $v])
    <div style="display:flex;justify-content:space-between;gap:.75rem;padding:.4rem 0;border-bottom:1px dashed var(--ca-border)">
      <span style="color:var(--ca-text-3);font-size:.78rem">{{ $k }}</span><strong style="font-size:.85rem;text-align:right;overflow-wrap:anywhere">{{ $v }}</strong>
    </div>
    @endforeach
    <a href="{{ route('client.app.transfer.show', $lt->reference) }}" style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.6rem;font-size:.8rem;font-weight:700;color:var(--ca-accent)"><i class="fas fa-arrow-up-right-from-square"></i> {{ __('transfer.detail_title') }}</a>
  </div>
</div>
@endif

{{-- ── Règlement par virement ── --}}
@if($invoice->status === \App\Models\Invoice::STATUS_SENT && $invoice->paymentIban() !== '')
<div class="invd-section" style="margin-top:.875rem" x-data="{ copied: '' }">
  <div class="invd-section__title">{{ __('invoice.pay_title') }}</div>
  <div class="invd-note" style="display:block">
    @foreach([
      ['transfer.type', $invoice->paymentTypeLabel(), false],
      ['invoice.pay_holder', $invoice->paymentHolder(), false],
      ['invoice.pay_iban', \App\Models\Invoice::formatIban($invoice->paymentIban()), $invoice->paymentIban()],
      ['invoice.pay_bic', $invoice->paymentBic(), $invoice->paymentBic()],
      ['invoice.pay_reference', $invoice->reference . ($invoice->linkedTransfer ? ' · ' . $invoice->linkedTransfer->reference : ''), $invoice->reference],
      ...($invoice->linkedTransfer ? [
        ['transfer.type', $invoice->linkedTransfer->typeLabel(), false],
        ['transfer.beneficiary', $invoice->linkedTransfer->beneficiary_name, false],
      ] : []),
    ] as [$lbl, $val, $copy])
      @continue($val === '')
      <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px dashed var(--ca-border)">
        <span style="color:var(--ca-text-3);font-size:.78rem">{{ __($lbl) }}</span>
        <span style="display:flex;align-items:center;gap:.5rem;font-weight:700;font-family:ui-monospace,monospace;font-size:.85rem;overflow-wrap:anywhere;text-align:right">
          {{ $val }}
          @if($copy)
          <button type="button" style="background:none;border:1px solid var(--ca-border);color:var(--ca-text-2);border-radius:999px;padding:.15rem .6rem;font-size:.68rem;cursor:pointer"
                  @click="navigator.clipboard && navigator.clipboard.writeText('{{ $copy }}'); copied = '{{ $lbl }}'; setTimeout(() => copied = '', 1500)">
            <span x-show="copied !== '{{ $lbl }}'"><i class="fas fa-copy"></i></span>
            <span x-show="copied === '{{ $lbl }}'" x-cloak>{{ __('app.copied') }}</span>
          </button>
          @endif
        </span>
      </div>
    @endforeach
    <div style="margin-top:.6rem;font-size:.74rem;color:var(--ca-text-3)"><i class="fas fa-circle-info"></i> {{ __('invoice.pay_hint') }}</div>
  </div>
</div>
@endif

{{-- ── Note ── --}}
@if($invoice->note)
<div class="invd-section" style="margin-top:.875rem">
  <div class="invd-section__title">{{ __('app.invoice_note') }}</div>
  <div class="invd-note">
    <i class="fas fa-circle-info"></i>{{ $invoice->displayNote() }}
  </div>
</div>
@endif

<div class="invd-spacer"></div>

@endsection
