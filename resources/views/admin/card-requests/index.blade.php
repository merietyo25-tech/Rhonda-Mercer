@extends('layouts.dashboard')
@section('title', 'Demandes de carte')
@section('page_title', 'Demandes de carte')

@section('content')
@push('styles')
<style>
[x-cloak]{display:none !important}
.cr-list{display:flex;flex-direction:column;gap:.875rem}
.cr-card{background:var(--c-white,#fff);border:1px solid var(--c-border);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(15,13,54,.05)}
.cr-top{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr) auto;gap:1.5rem;align-items:center;padding:1.1rem 1.4rem}
.cr-t{font-size:.67rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700;margin-bottom:.2rem}
.cr-n{font-size:.9rem;font-weight:700;color:var(--c-navy)}
.cr-chip{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700;padding:.25rem .7rem;border-radius:999px;background:var(--c-bg);border:1px solid var(--c-border);color:var(--c-navy)}
.cr-mid{padding:.75rem 1.4rem;border-top:1px solid var(--c-border);background:var(--c-bg);font-size:.8rem;display:flex;gap:1.25rem;flex-wrap:wrap;align-items:center}
.cr-acts{display:flex;gap:.5rem;flex-wrap:wrap;padding:.8rem 1.4rem;border-top:1px solid var(--c-border)}
@media (max-width:820px){.cr-top{grid-template-columns:1fr;gap:.9rem}}
.am{position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.am__bg{position:absolute;inset:0;background:rgba(15,13,54,.55);backdrop-filter:blur(2px)}
.am__box{position:relative;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;background:var(--c-white,#fff);border-radius:18px;box-shadow:0 24px 60px rgba(15,13,54,.3);padding:1.6rem 1.7rem 1.4rem}
.am__x{position:absolute;top:.9rem;right:.9rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--c-bg);color:var(--c-muted);cursor:pointer}
.am__ico{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.8rem}
.am__title{font-size:1.0625rem;font-weight:800;color:var(--c-navy);margin:0 0 .25rem}
.am__sub{font-size:.8rem;color:var(--c-muted);margin:0 0 1.15rem;line-height:1.5}
.am__grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:1rem}
.am__grid .full{grid-column:1/-1}
.am__foot{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.1rem}
@media (max-width:560px){.am__grid{grid-template-columns:1fr}}
</style>
@endpush

<div class="page-hdr-row">
  <div class="page-hdr">
    <h1>Demandes de carte</h1>
    <p>Cartes Visa demandées par les clients : fixez les frais (facture avec l’IBAN de règlement), puis émettez la carte.</p>
  </div>
</div>

@if($errors->any())
<div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

@php $site = \App\Models\SiteContact::current(); $S = \App\Models\CardRequest::class; @endphp

<div class="cr-list">
@forelse($requests as $r)
@php
  $open = $r->isOpen();
  $inv  = $r->invoice;
  $stMap = [
    'pending'          => ['bs-amber', 'hourglass-half', 'À traiter'],
    'awaiting_payment' => ['bs-blue',  'file-invoice',   'Paiement des frais en attente'],
    'approved'         => ['bs-green', 'check',          'Carte émise'],
    'rejected'         => ['bs-red',   'ban',            'Refusée'],
  ];
  [$cls, $ico, $lbl] = $stMap[$r->status] ?? ['bs-gray', 'circle', $r->status];
@endphp
<div class="cr-card" x-data="{ m: {{ (int) session('open_card') === $r->id ? "'invoice'" : 'null' }} }" @keydown.escape.window="m = null">
  <div class="cr-top">
    <div>
      <div class="cr-n">{{ $r->user->name }}</div>
      <div class="cell-sub">{{ $r->user->email }} · demandée le {{ $r->created_at->format('d/m/Y à H:i') }}</div>
      @if($r->reason)<div class="cell-sub">Motif : {{ $r->reason }}</div>@endif
    </div>
    <div>
      <div class="cr-t">Carte demandée</div>
      <span class="cr-chip"><i class="fas fa-{{ $r->isPhysical() ? 'credit-card' : 'mobile-screen' }}"></i> {{ $r->isPhysical() ? 'Physique' : 'Virtuelle' }}</span>
      <div class="cell-sub" style="margin-top:.35rem">Nom sur la carte : <strong>{{ $r->holder_name ?: '—' }}</strong></div>
    </div>
    <div style="text-align:right">
      <span class="badge-status {{ $cls }}"><i class="fas fa-{{ $ico }}" style="font-size:.6rem"></i> {{ $lbl }}</span>
      @if($r->fee_amount)<div class="cell-sub" style="margin-top:.35rem">Frais : <strong>{{ number_format($r->fee_amount, 2, ',', ' ') }} {{ $inv?->currency ?? 'EUR' }}</strong></div>@endif
    </div>
  </div>

  @if($r->isPhysical())
  <div class="cr-mid"><span><i class="fas fa-truck" style="color:var(--c-muted)"></i> <strong>Livraison :</strong> {{ $r->deliveryLine() ?: '—' }}</span></div>
  @endif
  @if($inv)
  <div class="cr-mid">
    <span><i class="fas fa-file-invoice" style="color:var(--c-muted)"></i> Facture <a href="{{ route('admin.invoices.show', $inv) }}" style="color:var(--c-accent);font-weight:700">{{ $inv->reference }}</a> ({{ $inv->status === 'paid' ? 'payée' : ($inv->status === 'cancelled' ? 'annulée' : 'envoyée') }})</span>
    <span class="cell-sub">À régler par {{ $inv->paymentTypeLabel('fr') }} : {{ $inv->paymentHolder() }} — <span style="font-family:monospace">{{ \App\Models\Invoice::formatIban($inv->paymentIban()) }}</span></span>
  </div>
  @endif

  @if($open)
  <div class="cr-acts">
    <button type="button" class="btn-accent btn-sm-pro" @click="m = 'invoice'"><i class="fas fa-file-invoice-dollar"></i> {{ $inv ? 'Modifier les frais' : 'Fixer les frais' }}</button>
    <button type="button" class="btn-navy btn-sm-pro" style="background:var(--c-green)" @click="m = 'issue'"><i class="fab fa-cc-visa"></i> Émettre la carte</button>
    <button type="button" class="btn-ghost btn-sm-pro" @click="m = 'reject'"><i class="fas fa-times"></i> Refuser</button>
  </div>

  {{-- Modale : frais de carte + IBAN de règlement --}}
  <div class="am" x-show="m === 'invoice'" x-cloak x-transition.opacity>
    <div class="am__bg" @click="m = null"></div>
    <div class="am__box" @click.stop>
      <button type="button" class="am__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="am__ico" style="background:#DBEAFE;color:var(--c-blue)"><i class="fas fa-file-invoice-dollar"></i></div>
      <h3 class="am__title">Frais de la carte {{ $r->isPhysical() ? 'physique' : 'virtuelle' }}</h3>
      <p class="am__sub">Une facture est envoyée à {{ $r->user->name }} (e-mail + notification). Il retrouve l’IBAN de règlement et la référence à indiquer dans son espace Cartes.</p>
      <form method="POST" action="{{ route('admin.card-requests.invoice', $r) }}">
        @csrf
        <div class="am__grid">
          <div class="full">
            <label class="form-label-pro">Montant des frais * ({{ $r->user->currency ?: 'EUR' }})</label>
            <input type="number" name="fee_amount" class="form-control-pro" step="0.01" min="0.01" required value="{{ old('fee_amount', $r->fee_amount) }}" placeholder="0,00">
          </div>
          <div class="full">
            <label class="form-label-pro">IBAN de règlement *</label>
            <input type="text" name="payment_iban" class="form-control-pro" maxlength="40" required style="font-family:monospace;text-transform:uppercase"
              value="{{ old('payment_iban', $inv?->payment_iban ?: $site->payment_iban) }}" placeholder="DE00 0000 0000 0000 0000 00">
          </div>
          <div>
            <label class="form-label-pro">Bénéficiaire de l’IBAN</label>
            <input type="text" name="payment_holder" class="form-control-pro" maxlength="100" value="{{ old('payment_holder', $inv?->payment_holder ?: $site->payment_holder) }}" placeholder="{{ site_name() }}">
          </div>
          <div>
            <label class="form-label-pro">BIC</label>
            <input type="text" name="payment_bic" class="form-control-pro" maxlength="11" style="font-family:monospace;text-transform:uppercase"
              value="{{ old('payment_bic', $inv?->payment_bic ?: $site->payment_bic) }}" placeholder="SOLBDEFF">
          </div>
          <div class="full">
            <label class="form-label-pro">Type de virement à exécuter</label>
            <select name="payment_type" class="form-control-pro">
              @php $pt = old('payment_type', $inv?->payment_type ?: $site->payment_type ?: 'sepa'); @endphp
              <option value="sepa" {{ $pt === 'sepa' ? 'selected' : '' }}>Virement SEPA</option>
              <option value="instant" {{ $pt === 'instant' ? 'selected' : '' }}>Virement en temps réel</option>
            </select>
          </div>
        </div>
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-accent btn-sm-pro"><i class="fas fa-paper-plane"></i> Créer &amp; envoyer la facture</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modale : émettre la carte --}}
  <div class="am" x-show="m === 'issue'" x-cloak x-transition.opacity>
    <div class="am__bg" @click="m = null"></div>
    <div class="am__box" @click.stop style="max-width:440px">
      <button type="button" class="am__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="am__ico" style="background:#D1FAE5;color:var(--c-green)"><i class="fab fa-cc-visa"></i></div>
      <h3 class="am__title">Émettre la carte Visa ?</h3>
      <p class="am__sub">
        Carte {{ $r->isPhysical() ? 'physique' : 'virtuelle' }} au nom de <strong>{{ $r->holder_name }}</strong> pour {{ $r->user->name }}.
        @if($inv) La facture {{ $inv->reference }} sera marquée comme payée : confirmez que le règlement a bien été reçu.@endif
        @if($r->isPhysical()) <br>Livraison : {{ $r->deliveryLine() }}.@endif
      </p>
      <form method="POST" action="{{ route('admin.card-requests.approve', $r) }}">
        @csrf
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro" style="background:var(--c-green)"><i class="fas fa-check"></i> Émettre la carte</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modale : refuser --}}
  <div class="am" x-show="m === 'reject'" x-cloak x-transition.opacity>
    <div class="am__bg" @click="m = null"></div>
    <div class="am__box" @click.stop style="max-width:440px">
      <button type="button" class="am__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="am__ico" style="background:#FEE2E2;color:var(--c-red)"><i class="fas fa-ban"></i></div>
      <h3 class="am__title">Refuser la demande ?</h3>
      <p class="am__sub">{{ $r->user->name }} sera prévenu{{ $inv ? ' et la facture ' . $inv->reference . ' sera annulée' : '' }}.</p>
      <form method="POST" action="{{ route('admin.card-requests.reject', $r) }}">
        @csrf
        <label class="form-label-pro">Motif (facultatif)</label>
        <input type="text" name="reason" class="form-control-pro" maxlength="500" placeholder="Ex : informations incomplètes">
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro" style="background:var(--c-red)"><i class="fas fa-times"></i> Refuser</button>
        </div>
      </form>
    </div>
  </div>
  @endif
</div>
@empty
<div class="card-pro" style="padding:3rem;text-align:center;color:var(--c-muted)">Aucune demande de carte.</div>
@endforelse
</div>

<div style="margin-top:1rem">{{ $requests->links() }}</div>
@endsection
