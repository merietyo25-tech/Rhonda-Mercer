@extends('layouts.dashboard')
@section('title', 'Modifier ' . $invoice->reference)
@section('page_title', 'Modifier la facture')

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 page-hdr">
  <div>
    <h4>Modifier — <span style="font-family:monospace;color:var(--c-accent)">{{ $invoice->reference }}</span></h4>
    <p>Seuls les brouillons peuvent être modifiés.</p>
  </div>
  <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn-ghost btn-sm-pro">
    <i class="fas fa-arrow-left"></i> Retour
  </a>
</div>

@if($errors->any())
<div class="flash flash-err mb-4"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<form data-confirm="Enregistrer les modifications de la facture {{ $invoice->reference }} ?" data-confirm-title="Modifier la facture" data-confirm-ok="Enregistrer" action="{{ route('admin.invoices.update', $invoice) }}" method="POST"
      x-data="invoiceEditForm()" x-init="init()">
  @csrf @method('PUT')

  <div class="row g-4">

    <div class="col-xl-5">
      <div class="card-pro mb-4">
        <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Informations générales</div></div>
        <div class="card-pro-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-pro">Client *</label>
              <select name="client_id" class="form-control-pro" required>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ $invoice->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->email }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label-pro">Virement concerné <span style="font-weight:400;color:var(--c-muted)">(facture de frais — facultatif)</span></label>
              <select name="transfer_id" id="inv-transfer" class="form-control-pro">
                <option value="">— Aucun —</option>
                @foreach($transfers as $t)
                <option value="{{ $t->id }}" data-client="{{ $t->user_id }}" {{ (string) old('transfer_id', $invoice->transfer_id) === (string) $t->id ? 'selected' : '' }}>{{ $t->reference }} — {{ $t->typeLabel('fr') }} — {{ $t->beneficiary_name }} ({{ number_format($t->amount, 2, ',', ' ') }} {{ $t->currency }})</option>
                @endforeach
              </select>
              <div style="font-size:.74rem;color:var(--c-muted);margin-top:.3rem">À l’envoi, le virement passe en « frais requis », le client reçoit l’e-mail et la notification avec le nom, le type et la référence du virement, et le message apparaît sur la page du virement.</div>
            </div>
            <div class="col-sm-6">
              <label class="form-label-pro">Date d'émission *</label>
              <input type="date" name="issue_date" class="form-control-pro"
                     value="{{ old('issue_date', $invoice->issue_date->toDateString()) }}" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label-pro">Date d'échéance</label>
              <input type="date" name="due_date" class="form-control-pro"
                     value="{{ old('due_date', $invoice->due_date?->toDateString()) }}">
            </div>
            <div class="col-sm-6">
              <label class="form-label-pro">Devise *</label>
              <select name="currency" class="form-control-pro" x-model="currency">
                @foreach($currencies as $cur)
                <option value="{{ $cur }}" {{ $invoice->currency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label-pro">TVA (%)</label>
              <input type="number" name="tax_rate" class="form-control-pro"
                     value="{{ old('tax_rate', $invoice->tax_rate) }}"
                     min="0" max="100" step="0.1" x-model.number="taxRate" @input="calcTotals()">
            </div>
            <div class="col-sm-8">
              <label class="form-label-pro">IBAN de règlement de la facture</label>
              <input type="text" name="payment_iban" class="form-control-pro" maxlength="40" style="font-family:monospace;letter-spacing:.04em;text-transform:uppercase"
                     value="{{ old('payment_iban', $invoice->payment_iban ?: \App\Models\SiteContact::current()->payment_iban) }}" placeholder="DE00 0000 0000 0000 0000 00">
              <div style="font-size:.74rem;color:var(--c-muted);margin-top:.3rem">Le client règle la facture sur cet IBAN. Il figure sur la facture, dans l'application et dans le PDF joint à l'e-mail.</div>
            </div>
            <div class="col-sm-4">
              <label class="form-label-pro">BIC</label>
              <input type="text" name="payment_bic" class="form-control-pro" maxlength="11" style="font-family:monospace;text-transform:uppercase"
                     value="{{ old('payment_bic', $invoice->payment_bic ?: \App\Models\SiteContact::current()->payment_bic) }}" placeholder="SOLBDEFF">
            </div>

            <div class="col-sm-6">
              <label class="form-label-pro">Bénéficiaire de l’IBAN (titulaire du compte)</label>
              <input type="text" name="payment_holder" class="form-control-pro" maxlength="100" value="{{ old('payment_holder', $invoice->payment_holder ?: \App\Models\SiteContact::current()->payment_holder) }}" placeholder="{{ site_name() }}">
            </div>
            <div class="col-sm-6">
              <label class="form-label-pro">Type de virement à exécuter</label>
              <select name="payment_type" class="form-control-pro">
                <option value="sepa" {{ old('payment_type', $invoice->paymentType()) === 'sepa' ? 'selected' : '' }}>Virement SEPA</option>
                <option value="instant" {{ old('payment_type', $invoice->paymentType()) === 'instant' ? 'selected' : '' }}>Virement en temps réel</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label-pro">Description</label>
              <textarea name="description" class="form-control-pro" rows="2">{{ old('description', $invoice->description) }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label-pro">Note</label>
              <input type="text" name="note" class="form-control-pro"
                     value="{{ old('note', $invoice->note) }}">
            </div>
          </div>
        </div>
      </div>
      <div class="card-pro" style="background:linear-gradient(135deg,#3A3590,#231F5F);color:#fff;border:none">
        <div class="card-pro-body">
          <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.8rem;color:rgba(255,255,255,.6)">
            <span>Sous-total</span><span x-text="fmt(subtotal) + ' ' + currency">0</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.8rem;color:rgba(255,255,255,.6)">
            <span>TVA (<span x-text="taxRate">0</span>%)</span><span x-text="fmt(taxAmt) + ' ' + currency">0</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding-top:.625rem;border-top:1px solid rgba(255,255,255,.15);font-size:1.25rem;font-weight:800;font-family:'Space Grotesk',sans-serif">
            <span>Total TTC</span><span x-text="fmt(total) + ' ' + currency">0</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-7">
      <div class="card-pro">
        <div class="card-pro-hdr">
          <div class="card-pro-title"><span class="icon-dot"></span>Lignes de facturation</div>
          <button type="button" @click="addLine()" class="btn-ghost btn-sm-pro"><i class="fas fa-plus"></i> Ajouter</button>
        </div>
        <div class="card-pro-body" style="padding:0">
          <div style="display:grid;grid-template-columns:1fr 80px 100px 100px 36px;gap:.5rem;padding:.625rem 1rem;background:rgba(0,0,0,.03);border-bottom:1px solid var(--c-border);font-size:.7rem;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.06em">
            <span>Description</span><span>Qté</span><span>Prix unit.</span><span>Total</span><span></span>
          </div>
          <div style="padding:.75rem 1rem;display:flex;flex-direction:column;gap:.625rem">
            <template x-for="(line, idx) in lines" :key="idx">
              <div style="display:grid;grid-template-columns:1fr 80px 100px 100px 36px;gap:.5rem;align-items:center">
                <input type="text" :name="'items['+idx+'][description]'" class="form-control-pro"
                       x-model="line.description" placeholder="Prestation…" required>
                <input type="number" :name="'items['+idx+'][quantity]'" class="form-control-pro"
                       x-model.number="line.qty" min="0.01" step="0.01" @input="calcTotals()" required>
                <input type="number" :name="'items['+idx+'][unit_price]'" class="form-control-pro"
                       x-model.number="line.price" min="0" step="0.01" @input="calcTotals()" required>
                <div style="padding:.5rem .625rem;background:rgba(0,132,87,.08);border:1.5px solid rgba(0,132,87,.25);border-radius:var(--radius-sm);font-size:.8125rem;font-weight:700;color:var(--c-navy);text-align:right"
                     x-text="fmt(line.qty * line.price)">0</div>
                <button type="button" @click="removeLine(idx)" x-show="lines.length > 1"
                        style="width:36px;height:36px;border:none;background:rgba(220,38,38,.1);color:#dc2626;border-radius:var(--radius-sm);cursor:pointer;font-size:.8rem">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
      <div style="margin-top:1.25rem;display:flex;gap:.75rem;justify-content:flex-end">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn-ghost btn-sm-pro">Annuler</a>
        <button type="submit" class="btn-navy btn-sm-pro"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </div>

  </div>
</form>

<script>
function invoiceEditForm() {
  return {
    lines: @json(collect($invoice->items)->map(fn($i) => ['description' => $i['description'], 'qty' => $i['quantity'], 'price' => $i['unit_price']])),
    currency: '{{ $invoice->currency }}',
    taxRate: {{ $invoice->tax_rate }},
    subtotal: 0, taxAmt: 0, total: 0,

    init() { this.calcTotals(); },
    addLine() { this.lines.push({ description: '', qty: 1, price: 0 }); this.calcTotals(); },
    removeLine(i) { this.lines.splice(i, 1); this.calcTotals(); },
    calcTotals() {
      this.subtotal = this.lines.reduce((s, l) => s + (l.qty * l.price), 0);
      this.taxAmt   = this.subtotal * this.taxRate / 100;
      this.total    = this.subtotal + this.taxAmt;
    },
    fmt(n) { return new Intl.NumberFormat('fr-FR', {minimumFractionDigits:2}).format(n || 0); }
  }
}
</script>
<script>
(function () {
  var client = document.querySelector('select[name=client_id]'), tr = document.getElementById('inv-transfer');
  if (!client || !tr) return;
  function sync() {
    var c = client.value, keep = tr.value, ok = false;
    Array.prototype.forEach.call(tr.options, function (o) {
      if (!o.value) return;
      var show = o.dataset.client === c; o.hidden = !show; o.disabled = !show;
      if (show && o.value === keep) ok = true;
    });
    if (!ok) tr.value = '';
  }
  client.addEventListener('change', sync); sync();
})();
</script>

@endsection
