@extends('layouts.dashboard')
@section('title', $invoice->reference . ' — Facture ' . site_name())

@section('content')
<style>
/* ── Screen header ── */
.inv-screen-hdr{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem}
.inv-screen-left{display:flex;align-items:center;gap:.875rem;flex-wrap:wrap}
.inv-screen-ref{font-family:monospace;font-size:1.1rem;font-weight:900;color:var(--c-navy)}
.inv-screen-actions{display:flex;gap:.5rem;flex-wrap:wrap}

/* ── Buttons ── */
.btn-print,.btn-edit-inv{display:inline-flex;align-items:center;gap:.4rem;padding:.475rem .875rem;border-radius:var(--radius-sm);font-size:.8rem;font-weight:700;border:1.5px solid var(--c-border);background:var(--c-bg);color:var(--c-muted);cursor:pointer;text-decoration:none;transition:.15s}
.btn-print:hover,.btn-edit-inv:hover{border-color:var(--c-navy);color:var(--c-navy)}
.btn-send-inv{display:inline-flex;align-items:center;gap:.4rem;padding:.475rem .875rem;border-radius:var(--radius-sm);font-size:.8rem;font-weight:700;background:var(--c-navy);color:#fff;border:none;cursor:pointer;text-decoration:none;transition:.15s}
.btn-send-inv:hover{background:#231F5F;color:#fff}
.btn-paid-inv{display:inline-flex;align-items:center;gap:.4rem;padding:.475rem .875rem;border-radius:var(--radius-sm);font-size:.8rem;font-weight:700;background:#16a34a;color:#fff;border:none;cursor:pointer;transition:.15s}
.btn-paid-inv:hover{background:#15803d}
.btn-cancel-inv,.btn-delete-inv{display:inline-flex;align-items:center;gap:.4rem;padding:.475rem .875rem;border-radius:var(--radius-sm);font-size:.8rem;font-weight:700;background:transparent;color:#dc2626;border:1.5px solid rgba(220,38,38,.35);cursor:pointer;transition:.15s}
.btn-cancel-inv:hover,.btn-delete-inv:hover{background:rgba(220,38,38,.06)}

/* ── Sheet ── */
.inv-sheet{background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:2.75rem;max-width:810px;margin:0 auto;color:#1a1a2e;box-shadow:0 4px 24px rgba(0,0,0,.06)}

/* Top area */
.inv-top{display:flex;justify-content:space-between;align-items:flex-start;gap:2rem;margin-bottom:2.25rem;flex-wrap:wrap}
.inv-brand{}
.inv-brand-name{font-size:1.0625rem;font-weight:900;color:#3A3590;letter-spacing:-.01em}
.inv-brand-sub{font-size:.72rem;color:#888;line-height:1.7;margin-top:.2rem}
.inv-meta{text-align:right}
.inv-meta-ref{font-family:monospace;font-size:1rem;font-weight:900;color:#3A3590;margin-bottom:.35rem}
.inv-meta-dates{font-size:.72rem;color:#777;line-height:1.9}

/* Parties */
.inv-parties{display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem;padding:1.375rem 1.5rem;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb}
.inv-party-lbl{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:#aaa;margin-bottom:.375rem}
.inv-party-name{font-size:.9375rem;font-weight:800;color:#1a1a2e;margin-bottom:.2rem}
.inv-party-info{font-size:.75rem;color:#666;line-height:1.7}

/* Lines table */
.inv-lines{width:100%;border-collapse:collapse;margin-bottom:1.75rem;font-size:.8125rem}
.inv-lines thead th{padding:.625rem .9375rem;background:#3A3590;color:#fff;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.inv-lines thead th:last-child,.inv-lines thead th:nth-last-child(2){text-align:right}
.inv-lines tbody td{padding:.8125rem .9375rem;border-bottom:1px solid #e5e7eb;color:#333}
.inv-lines tbody tr:last-child td{border-bottom:none}
.inv-lines tbody td:last-child,.inv-lines tbody td:nth-last-child(2){text-align:right}
.inv-lines tbody td:last-child{font-weight:700;color:#1a1a2e}

/* Totals */
.inv-totals-wrap{display:flex;justify-content:flex-end;margin-bottom:1.75rem}
.inv-totals{width:270px;border:1.5px solid #e5e7eb;border-radius:8px;overflow:hidden}
.inv-totals-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem .9375rem;font-size:.8125rem;color:#555;border-bottom:1px solid #e5e7eb}
.inv-totals-row:last-child{border-bottom:none;font-size:1rem;font-weight:900;color:#3A3590;background:#f0f5ff;padding:.75rem .9375rem}

/* Badges */
.badge-inv{display:inline-flex;align-items:center;padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700}
.bs-gray  {background:rgba(107,114,128,.1);color:#6b7280;border:1px solid rgba(107,114,128,.2)}
.bs-blue  {background:rgba(59,130,246,.1);color:#2563eb;border:1px solid rgba(59,130,246,.2)}
.bs-green {background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)}
.bs-red   {background:rgba(220,38,38,.1);color:#dc2626;border:1px solid rgba(220,38,38,.2)}

/* Note & description */
.inv-note{margin-top:1.25rem;padding:1rem 1.125rem;background:#fffbeb;border-left:3px solid #008457;border-radius:0 6px 6px 0;font-size:.8125rem;color:#555;line-height:1.6}

/* Print */
@media print {
  .inv-screen-hdr,.sidebar,.main-topbar,form,.no-print{display:none!important}
  .inv-sheet{border:none;padding:0;max-width:100%;box-shadow:none;margin:0}
}
</style>

{{-- Screen header --}}
<div class="inv-screen-hdr">
  <div class="inv-screen-left">
    <a href="{{ route('admin.invoices.index') }}" class="btn-print no-print">
      <i class="fas fa-arrow-left"></i>
    </a>
    <span class="inv-screen-ref">{{ $invoice->reference }}</span>
    <span class="badge-inv bs-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
    @if($invoice->due_date && $invoice->due_date->isPast() && !$invoice->isPaid() && !$invoice->isCancelled())
      <span style="font-size:.72rem;background:#fee2e2;color:#dc2626;padding:.2rem .6rem;border-radius:8px;font-weight:700">En retard</span>
    @endif
  </div>

  <div class="inv-screen-actions no-print">
    <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="btn-print"><i class="fas fa-file-pdf"></i> PDF</a>
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Imprimer</button>

    @if($invoice->isDraft())
      <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn-edit-inv"><i class="fas fa-pen"></i> Modifier</a>
      <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" style="display:inline"
            data-confirm="Envoyer la facture {{ $invoice->reference }} par e-mail au client ?">
        @csrf
        <button type="submit" class="btn-send-inv"><i class="fas fa-paper-plane"></i> Envoyer</button>
      </form>
      <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" style="display:inline"
            data-confirm="Supprimer ce brouillon définitivement ?">
        @csrf @method('DELETE')
        <button type="submit" class="btn-delete-inv"><i class="fas fa-trash"></i></button>
      </form>
    @endif

    @if($invoice->isSent())
      <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn-edit-inv"><i class="fas fa-pen"></i> Modifier</a>
      <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice) }}" style="display:inline"
            data-confirm="Confirmer le paiement de cette facture ?">
        @csrf
        <button type="submit" class="btn-paid-inv"><i class="fas fa-check"></i> Marquer payée</button>
      </form>
      <form method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}" style="display:inline"
            data-confirm="Annuler cette facture ?">
        @csrf
        <button type="submit" class="btn-cancel-inv"><i class="fas fa-ban"></i> Annuler</button>
      </form>
    @endif
  </div>
</div>

{{-- IBAN de règlement : modifiable tant que la facture n'est ni payée ni annulée --}}
@if($invoice->isDraft() || $invoice->isSent())
<form data-confirm="Enregistrer l’IBAN de règlement de la facture {{ $invoice->reference }} ?" data-confirm-title="IBAN de règlement" data-confirm-ok="Enregistrer" method="POST" action="{{ route('admin.invoices.payment', $invoice) }}" class="no-print"
      style="max-width:820px;margin:0 auto 1rem;padding:1rem 1.1rem;border:1.5px solid var(--c-border);border-radius:10px;background:var(--c-bg)">
  @csrf
  <div style="font-size:.8rem;font-weight:800;margin-bottom:.6rem"><i class="fas fa-building-columns"></i> IBAN de règlement de la facture</div>
  @if($errors->has('payment_iban') || $errors->has('payment_bic'))
  <div style="margin-bottom:.6rem;font-size:.78rem;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first('payment_iban') ?: $errors->first('payment_bic') }}</div>
  @endif
  <div style="display:grid;grid-template-columns:2fr 1fr 1.4fr 1.2fr auto;gap:.6rem;align-items:end" class="inv-pay-grid">
    <div>
      <label class="form-label-pro">IBAN *</label>
      <input type="text" name="payment_iban" class="form-control-pro" maxlength="40" required style="font-family:monospace;text-transform:uppercase"
             value="{{ old('payment_iban', \App\Models\Invoice::formatIban($invoice->paymentIban())) }}" placeholder="DE00 0000 0000 0000 0000 00">
    </div>
    <div>
      <label class="form-label-pro">BIC</label>
      <input type="text" name="payment_bic" class="form-control-pro" maxlength="11" style="font-family:monospace;text-transform:uppercase"
             value="{{ old('payment_bic', $invoice->paymentBic()) }}" placeholder="SOLBDEFF">
    </div>
    <div>
      <label class="form-label-pro">Bénéficiaire</label>
      <input type="text" name="payment_holder" class="form-control-pro" maxlength="100"
             value="{{ old('payment_holder', $invoice->payment_holder) }}" placeholder="{{ site_name() }}">
    </div>
    <div>
      <label class="form-label-pro">Type de virement</label>
      <select name="payment_type" class="form-control-pro">
        <option value="sepa" {{ old('payment_type', $invoice->paymentType()) === 'sepa' ? 'selected' : '' }}>SEPA</option>
        <option value="instant" {{ old('payment_type', $invoice->paymentType()) === 'instant' ? 'selected' : '' }}>Temps réel</option>
      </select>
    </div>
    <button type="submit" class="btn-accent btn-sm-pro" style="height:42px"><i class="fas fa-floppy-disk"></i> Enregistrer</button>
  </div>
</form>
<style>@media (max-width: 720px) { .inv-pay-grid { grid-template-columns: 1fr !important; } }</style>
@endif

{{-- Invoice sheet --}}
<div class="inv-sheet">

  {{-- Top: brand + ref --}}
  <div class="inv-top">
    <div class="inv-brand">
      <x-logo theme="light" size="md" />
      <div class="inv-brand-name" style="margin-top:.35rem"> {{ site_name() }}</div>
      <div class="inv-brand-sub">
        Organisme de financement<br>
        {{ site_email() }}
      </div>
    </div>
    <div class="inv-meta">
      <div class="inv-meta-ref">{{ $invoice->reference }}</div>
      <div class="inv-meta-dates">
        <span class="badge-inv bs-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span><br>
        Émise le {{ $invoice->issue_date->format('d/m/Y') }}
        @if($invoice->sent_at)
          <br>Envoyée le {{ $invoice->sent_at->format('d/m/Y') }}
        @endif
        @if($invoice->paid_at)
          <br><span style="color:#16a34a;font-weight:700">Payée le {{ $invoice->paid_at->format('d/m/Y') }}</span>
        @endif
        @if($invoice->due_date)
          <br>Échéance {{ $invoice->due_date->format('d/m/Y') }}
        @endif
      </div>
    </div>
  </div>

  {{-- Parties --}}
  <div class="inv-parties">
    <div>
      <div class="inv-party-lbl">Émetteur</div>
      <div class="inv-party-name">{{ site_name() }}</div>
      <div class="inv-party-info">Agent : {{ $invoice->admin->name }}</div>
    </div>
    <div>
      <div class="inv-party-lbl">Facturé à</div>
      <div class="inv-party-name">{{ $invoice->client->name }}</div>
      <div class="inv-party-info">
        {{ $invoice->client->email }}
        @if($invoice->client->phone)<br>{{ $invoice->client->phone }}@endif
        @if($invoice->client->address)<br>{{ $invoice->client->address }}@endif
      </div>
    </div>
  </div>

  @if($invoice->description)
  <p style="font-size:.875rem;color:#555;margin-bottom:1.75rem;line-height:1.7">{{ $invoice->displayDescription('fr') }}</p>
  @endif

  {{-- Line items --}}
  <table class="inv-lines">
    <thead>
      <tr>
        <th style="width:50%;text-align:left">Prestation</th>
        <th style="width:10%;text-align:center">Qté</th>
        <th style="width:20%">Prix unit.</th>
        <th style="width:20%">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items ?? [] as $item)
      <tr>
        <td>{{ $invoice->itemDescription($item, 'fr') }}</td>
        <td style="text-align:center">{{ $item['quantity'] }}</td>
        <td>{{ number_format((float)$item['unit_price'], 2, ',', ' ') }} {{ $invoice->currency }}</td>
        <td>{{ number_format((float)$item['total'], 2, ',', ' ') }} {{ $invoice->currency }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="inv-totals-wrap">
    <div class="inv-totals">
      <div class="inv-totals-row">
        <span>Sous-total</span>
        <span>{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $invoice->currency }}</span>
      </div>
      @if($invoice->tax_rate > 0)
      <div class="inv-totals-row">
        <span>TVA ({{ $invoice->tax_rate }}%)</span>
        <span>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
      </div>
      @endif
      <div class="inv-totals-row">
        <span>Total TTC</span>
        <span>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</span>
      </div>
    </div>
  </div>

  @if($invoice->linkedTransfer)
  @php $lt = $invoice->linkedTransfer; @endphp
  <div class="inv-note" style="background:#f5f3ff;border-left-color:#6d28d9">
    <strong>Virement concerné :</strong> {{ $lt->reference }} — {{ $lt->typeLabel('fr') }} — {{ $lt->beneficiary_name }}
    @if($lt->beneficiary_iban) — IBAN <span style="font-family:monospace">{{ \App\Models\Invoice::formatIban((string) $lt->beneficiary_iban) }}</span>@endif
    ({{ number_format($lt->amount, 2, ',', ' ') }} {{ $lt->currency }}) — statut : {{ $lt->statusLabel() }}
  </div>
  @endif

  @if($invoice->paymentIban() !== '' && $invoice->status !== 'paid')
  <div class="inv-note" style="background:#f0f5ff;border-left-color:#3A3590">
    <strong>Règlement par {{ $invoice->paymentTypeLabel('fr') }} :</strong>
    bénéficiaire <strong>{{ $invoice->paymentHolder() }}</strong> — IBAN <strong style="font-family:monospace">{{ \App\Models\Invoice::formatIban($invoice->paymentIban()) }}</strong>
    @if($invoice->paymentBic() !== '') — BIC <strong style="font-family:monospace">{{ $invoice->paymentBic() }}</strong>@endif
    — référence <strong>{{ $invoice->reference }}@if($invoice->linkedTransfer) · {{ $invoice->linkedTransfer->reference }}@endif</strong>
    @if($invoice->linkedTransfer) — {{ $invoice->linkedTransfer->typeLabel('fr') }} : {{ $invoice->linkedTransfer->beneficiary_name }}@endif
  </div>
  @elseif($invoice->isDraft())
  <div class="inv-note" style="background:#fef2f2;border-left-color:#dc2626">
    <strong>IBAN de règlement manquant.</strong> Modifiez la facture pour le renseigner avant de l'envoyer.
  </div>
  @endif

  @if($invoice->note)
  <div class="inv-note">
    <strong>Note :</strong> {{ $invoice->note }}
  </div>
  @endif

</div>
@endsection
