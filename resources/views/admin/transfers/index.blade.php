@extends('layouts.dashboard')
@section('title', 'Transferts — ' . site_name())
@section('page_title', 'Transferts clients')

@section('content')

{{-- Page header ── --}}
<div class="page-hdr-row">
  <div class="page-hdr">
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
      <h1>Transferts clients</h1>
      @if($isSuperAdmin)
        <span class="badge-status bs-violet"><i class="fas fa-shield-alt" style="font-size:.6rem"></i> Vue globale</span>
      @endif
    </div>
    <p>Validation et suivi des virements soumis par les clients</p>
  </div>
</div>

{{-- KPI ── --}}
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-card__icon mi-amber"><i class="fas fa-hourglass-half"></i></div>
    <div class="metric-card__val" style="color:var(--c-amber)">{{ $stats['pending'] }}</div>
    <div class="metric-card__lbl">En attente</div>
    <div class="metric-card__accent" style="background:var(--c-amber)"></div>
  </div>
  <div class="metric-card">
    <div class="metric-card__icon mi-blue"><i class="fas fa-file-invoice"></i></div>
    <div class="metric-card__val" style="color:var(--c-blue)">{{ $stats['fee_required'] }}</div>
    <div class="metric-card__lbl">Frais requis</div>
    <div class="metric-card__accent" style="background:var(--c-blue)"></div>
  </div>
  <div class="metric-card">
    <div class="metric-card__icon mi-green"><i class="fas fa-check-circle"></i></div>
    <div class="metric-card__val" style="color:var(--c-green)">{{ $stats['completed'] }}</div>
    <div class="metric-card__lbl">Validés</div>
    <div class="metric-card__accent" style="background:var(--c-green)"></div>
  </div>
  <div class="metric-card">
    <div class="metric-card__icon mi-red"><i class="fas fa-times-circle"></i></div>
    <div class="metric-card__val" style="color:var(--c-red)">{{ $stats['rejected'] }}</div>
    <div class="metric-card__lbl">Rejetés</div>
    <div class="metric-card__accent" style="background:var(--c-red)"></div>
  </div>
</div>

@if($errors->any())
<div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

{{-- Filters ── --}}
<div class="card-pro" style="margin-bottom:1.25rem">
  <div class="card-pro-body" style="padding:.75rem 1.25rem">
    <form method="GET" style="display:flex;gap:.625rem;flex-wrap:wrap;align-items:center">
      <div style="position:relative;flex:1;min-width:200px">
        <i class="fas fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--c-muted);font-size:.75rem;pointer-events:none"></i>
        <input type="text" name="search" value="{{ request('search') }}"
          placeholder="Référence, client, bénéficiaire…" class="form-control-pro" style="padding-left:2.25rem">
      </div>
      <select name="status" class="form-control-pro" style="width:auto;min-width:180px" onchange="this.form.submit()">
        <option value="">En attente + frais</option>
        <option value="pending"       {{ request('status') === 'pending'       ? 'selected':'' }}>En attente</option>
        <option value="fee_required"  {{ request('status') === 'fee_required'  ? 'selected':'' }}>Frais requis</option>
        <option value="completed"     {{ request('status') === 'completed'     ? 'selected':'' }}>Validés</option>
        <option value="rejected"      {{ request('status') === 'rejected'      ? 'selected':'' }}>Rejetés</option>
      </select>
      <button type="submit" class="btn-navy btn-sm-pro"><i class="fas fa-search"></i> Filtrer</button>
      @if(request()->hasAny(['search','status']))
      <a href="{{ route('admin.transfers.index') }}" class="btn-ghost btn-sm-pro">
        <i class="fas fa-times"></i> Réinitialiser
      </a>
      @endif
    </form>
  </div>
</div>

{{-- Transfer list ── --}}
@if($transfers->isEmpty())
<div class="card-pro" style="text-align:center;padding:5rem 2rem">
  <i class="fas fa-exchange-alt" style="font-size:2.5rem;color:var(--c-muted);opacity:.2;display:block;margin-bottom:1rem"></i>
  <p style="font-size:.9375rem;font-weight:600;color:var(--c-muted)">Aucun transfert à afficher.</p>
  <p style="font-size:.8125rem;color:var(--c-muted);margin-top:.35rem">Modifiez les filtres ou attendez de nouveaux virements.</p>
</div>
@else

<style>
[x-cloak]{display:none !important}
.at-list{display:flex;flex-direction:column;gap:.875rem}
.at-card{background:var(--c-white,#fff);border:1px solid var(--c-border);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(15,13,54,.05)}
.at-top{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr) auto;gap:1.5rem;align-items:center;padding:1.1rem 1.4rem}
.at-who{display:flex;align-items:center;gap:.85rem;min-width:0}
.at-av{width:42px;height:42px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--c-navy),var(--c-navy-3));display:flex;align-items:center;justify-content:center;color:var(--c-accent);font-weight:800;font-size:.9rem}
.at-t{font-size:.67rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700;margin-bottom:.2rem}
.at-n{font-size:.875rem;font-weight:700;color:var(--c-navy);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.at-ref{font-family:monospace;font-size:.72rem;font-weight:700;color:var(--c-navy);margin-top:.15rem}
.at-amt{text-align:right}
.at-amt strong{font-family:'Space Grotesk',sans-serif;font-size:1.25rem;font-weight:900;color:var(--c-navy);display:block;line-height:1.2}
.at-amt strong small{font-size:.72rem;font-weight:600;color:var(--c-muted)}
.at-amt .badge-status{margin-top:.4rem}
.at-mid{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.75rem 1.4rem;border-top:1px solid var(--c-border);background:var(--c-bg)}
.at-bar{flex:1;min-width:160px}
.at-bar__row{display:flex;justify-content:space-between;font-size:.7rem;color:var(--c-muted);font-weight:700;margin-bottom:.3rem}
.at-bar__trk{height:6px;border-radius:999px;background:var(--c-border);overflow:hidden}
.at-bar__fill{height:100%;border-radius:999px}
.at-chip{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:600;padding:.3rem .7rem;border-radius:999px;background:var(--c-bg);border:1px solid var(--c-border);color:var(--c-text)}
.at-chip--warn{background:#FEF3C7;border-color:#FDE68A;color:#92400E}
.at-chip--ok{color:var(--c-green)}
.at-notes{padding:.75rem 1.4rem;border-top:1px solid var(--c-border);display:flex;gap:2rem;flex-wrap:wrap;font-size:.8125rem}
.at-acts{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;padding:.8rem 1.4rem;border-top:1px solid var(--c-border)}
.at-acts__time{margin-left:auto;font-size:.75rem;color:var(--c-muted)}
.at-done{padding:.7rem 1.4rem;border-top:1px solid var(--c-border);font-size:.78rem;color:var(--c-muted)}
@media (max-width:820px){.at-top{grid-template-columns:1fr;gap:.9rem}.at-amt{text-align:left}}

.at-modal{position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.at-modal__bg{position:absolute;inset:0;background:rgba(15,13,54,.55);backdrop-filter:blur(2px)}
.at-modal__box{position:relative;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;background:var(--c-white,#fff);border-radius:18px;box-shadow:0 24px 60px rgba(15,13,54,.3);padding:1.6rem 1.7rem 1.4rem}
.at-modal__x{position:absolute;top:.9rem;right:.9rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--c-bg);color:var(--c-muted);cursor:pointer}
.at-modal__ico{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.8rem}
.at-modal__title{font-size:1.0625rem;font-weight:800;color:var(--c-navy);margin:0 0 .25rem}
.at-modal__sub{font-size:.8rem;color:var(--c-muted);margin:0 0 1.15rem;line-height:1.5}
.at-grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:1rem}
.at-grid .full{grid-column:1/-1}
.at-modal__foot{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.1rem}
.at-code{display:block;text-align:center;font-size:2rem;font-weight:800;letter-spacing:.3em;color:#92400E;background:#FEF3C7;border:1px dashed #D97706;border-radius:12px;padding:.9rem;margin:.4rem 0 1rem}
@media (max-width:560px){.at-grid{grid-template-columns:1fr}}
</style>

<div class="at-list">
@foreach($transfers as $trf)
@php
  $isPending = in_array($trf->status, [\App\Models\Transfer::STATUS_PENDING, \App\Models\Transfer::STATUS_FEE_REQUIRED]);
  $statusMap = [
    'pending'      => ['cls'=>'bs-amber',  'icon'=>'hourglass-half', 'bar'=>'var(--c-amber)'],
    'fee_required' => ['cls'=>'bs-blue',   'icon'=>'file-invoice',   'bar'=>'var(--c-blue)'],
    'completed'    => ['cls'=>'bs-green',  'icon'=>'check',          'bar'=>'var(--c-green)'],
    'rejected'     => ['cls'=>'bs-red',    'icon'=>'times',          'bar'=>'var(--c-red)'],
  ];
  $sm = $statusMap[$trf->status] ?? ['cls'=>'bs-gray','icon'=>'circle','bar'=>'var(--c-muted)'];
  $site = \App\Models\SiteContact::current();
@endphp

<div class="at-card" style="border-left:4px solid {{ $sm['bar'] }};{{ $trf->status === 'rejected' ? 'opacity:.85' : '' }}"
     x-data="{ m: {{ (int) old('_trf') === $trf->id ? "'invoice'" : 'null' }} }" @keydown.escape.window="m = null">

  <div class="at-top">
    <div class="at-who">
      <div class="at-av">{{ mb_strtoupper(mb_substr($trf->user->name, 0, 1)) }}</div>
      <div style="min-width:0">
        <div class="at-n">{{ $trf->user->name }}</div>
        <div class="cell-sub" style="overflow:hidden;text-overflow:ellipsis">{{ $trf->user->email }}</div>
        <div class="at-ref">{{ $trf->reference }}</div>
      </div>
    </div>

    <div style="min-width:0">
      <div class="at-t">Bénéficiaire</div>
      <div class="at-n">{{ $trf->beneficiary_name ?? '—' }}</div>
      <div class="cell-sub">{{ $trf->typeLabel('fr') }}</div>
      @if($trf->beneficiary_iban)<div class="cell-mono" style="font-size:.72rem;overflow:hidden;text-overflow:ellipsis">{{ $trf->beneficiary_iban }}</div>@endif
    </div>

    <div class="at-amt">
      <strong>{{ number_format($trf->amount, 2, ',', ' ') }} <small>{{ $trf->currency }}</small></strong>
      <div class="cell-sub">{{ $trf->created_at->format('d/m/Y · H:i') }}</div>
      <span class="badge-status {{ $sm['cls'] }}"><i class="fas fa-{{ $sm['icon'] }}" style="font-size:.6rem"></i> {{ $trf->statusLabel() }}</span>
    </div>
  </div>

  {{-- Facture liée --}}
  @if($trf->invoice)
  <div class="at-mid">
    <span class="at-chip"><i class="fas fa-file-invoice"></i>
      <a href="{{ route('admin.invoices.show', $trf->invoice) }}" style="color:var(--c-accent);font-weight:700">{{ $trf->invoice->reference }}</a>
    </span>
    @if($trf->invoice->status === 'paid')
    <span class="cell-sub" style="color:var(--c-green)"><i class="fas fa-circle-check"></i> Facture payée{{ $trf->invoice->paid_at ? ' le ' . $trf->invoice->paid_at->format('d/m/Y') : '' }}</span>
    @else
    <span class="cell-sub">À régler par {{ $trf->invoice->paymentTypeLabel('fr') }} : {{ $trf->invoice->paymentHolder() }} — <span style="font-family:monospace">{{ \App\Models\Invoice::formatIban($trf->invoice->paymentIban()) }}</span></span>
    @endif
  </div>
  @endif

  {{-- Avancement + code --}}
  @if($isPending)
  <div class="at-mid">
    <div class="at-bar">
      <div class="at-bar__row"><span>Avancement</span><span>{{ $trf->progressValue() }} %</span></div>
      <div class="at-bar__trk"><div class="at-bar__fill" style="width:{{ $trf->progressValue() }}%;background:{{ $trf->code_required ? '#D97706' : 'var(--c-accent, #008457)' }}"></div></div>
    </div>
    @if($trf->code_required)
      <span class="at-chip at-chip--warn"><i class="fas fa-lock"></i> Code n°{{ (int) $trf->code_stage + 1 }} → {{ $trf->nextStageTarget() }} % en attente de saisie</span>
      <button type="button" class="btn-ghost btn-sm-pro" @click="m = 'code'"><i class="fas fa-eye"></i> Voir le code</button>
    @elseif($trf->code_verified_at)
      <span class="at-chip at-chip--ok"><i class="fas fa-circle-check"></i> Code n°{{ (int) $trf->code_stage }} saisi le {{ $trf->code_verified_at->format('d/m/Y H:i') }}</span>
    @endif
  </div>
  @endif

  @if($trf->note || $trf->admin_note)
  <div class="at-notes">
    @if($trf->note)<div><div class="at-t">Note client</div>{{ $trf->note }}</div>@endif
    @if($trf->admin_note)<div><div class="at-t">Note admin</div>{{ $trf->admin_note }}@if($trf->admin)<span style="color:var(--c-muted)"> · {{ $trf->admin->name }}</span>@endif</div>@endif
  </div>
  @endif

  {{-- Actions --}}
  @if($isPending)
  <div class="at-acts">
    <button type="button" class="btn-navy btn-sm-pro" style="background:var(--c-green)" @click="m = 'approve'"><i class="fas fa-check"></i> Valider</button>
    <button type="button" class="btn-navy btn-sm-pro" style="background:var(--c-red)" @click="m = 'reject'"><i class="fas fa-times"></i> Rejeter</button>
    @if($trf->nextStageTarget() !== null)
    <form data-confirm="Générer le code n°{{ (int) $trf->code_stage + 1 }} du virement {{ $trf->reference }} ? Le client devra le saisir (code à lui communiquer) pour faire avancer la barre." data-confirm-title="Générer le code" data-confirm-ok="Générer" method="POST" action="{{ route('admin.transfers.progress', $trf) }}" style="display:inline">
      @csrf
      <button type="submit" class="btn-ghost btn-sm-pro" data-no-confirm title="Génère le code que le client saisira pour passer à {{ $trf->nextStageTarget() }} %">
        <i class="fas fa-key"></i> {{ $trf->code_required ? 'Régénérer le code n°' . ((int) $trf->code_stage + 1) : 'Générer le code n°' . ((int) $trf->code_stage + 1) . ' (→ ' . $trf->nextStageTarget() . ' %)' }}
      </button>
    </form>
    @else
    <span class="btn-ghost btn-sm-pro" style="opacity:.7;cursor:default"><i class="fas fa-check"></i> {{ count(\App\Models\Transfer::CODE_STAGES) }} codes saisis · 100 %</span>
    @endif
    @if($trf->status === \App\Models\Transfer::STATUS_PENDING)
    <button type="button" class="btn-ghost btn-sm-pro" @click="m = 'invoice'"><i class="fas fa-file-invoice"></i> Facturer les frais</button>
    @endif
    <span class="at-acts__time"><i class="fas fa-clock" style="margin-right:.3rem"></i>Soumis {{ $trf->created_at->diffForHumans() }}</span>
  </div>

  {{-- Modale : valider --}}
  <div class="at-modal" x-show="m === 'approve'" x-cloak x-transition.opacity>
    <div class="at-modal__bg" @click="m = null"></div>
    <div class="at-modal__box" @click.stop>
      <button type="button" class="at-modal__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="at-modal__ico" style="background:#D1FAE5;color:var(--c-green)"><i class="fas fa-check"></i></div>
      <h3 class="at-modal__title">Valider ce virement ?</h3>
      <p class="at-modal__sub">{{ $trf->reference }} — {{ number_format($trf->amount, 2, ',', ' ') }} {{ $trf->currency }} vers {{ $trf->beneficiary_name }}. Le montant sera débité du compte du client.</p>
      <form method="POST" action="{{ route('admin.transfers.approve', $trf) }}">
        @csrf
        <label class="form-label-pro">Note de validation (optionnel)</label>
        <input type="text" name="admin_note" class="form-control-pro" placeholder="Ex : virement traité — délai estimé 2 jours ouvrés" maxlength="500">
        <div class="at-modal__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro" style="background:var(--c-green)"><i class="fas fa-check"></i> Confirmer la validation</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modale : rejeter --}}
  <div class="at-modal" x-show="m === 'reject'" x-cloak x-transition.opacity>
    <div class="at-modal__bg" @click="m = null"></div>
    <div class="at-modal__box" @click.stop>
      <button type="button" class="at-modal__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="at-modal__ico" style="background:#FEE2E2;color:var(--c-red)"><i class="fas fa-times"></i></div>
      <h3 class="at-modal__title">Rejeter ce virement ?</h3>
      <p class="at-modal__sub">{{ $trf->reference }} — les fonds seront recrédités au client.</p>
      <form method="POST" action="{{ route('admin.transfers.reject', $trf) }}">
        @csrf
        <label class="form-label-pro">Motif du rejet (optionnel)</label>
        <input type="text" name="admin_note" class="form-control-pro" placeholder="Ex : IBAN invalide, KYC incomplet, limite atteinte…" maxlength="500">
        <div class="at-modal__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro" style="background:var(--c-red)"><i class="fas fa-times"></i> Confirmer le rejet</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modale : code de déblocage --}}
  @if($trf->code_required)
  <div class="at-modal" x-show="m === 'code'" x-cloak x-transition.opacity>
    <div class="at-modal__bg" @click="m = null"></div>
    <div class="at-modal__box" @click.stop style="max-width:430px">
      <button type="button" class="at-modal__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="at-modal__ico" style="background:#FEF3C7;color:#D97706"><i class="fas fa-lock"></i></div>
      <h3 class="at-modal__title">Code n°{{ (int) $trf->code_stage + 1 }} (→ {{ $trf->nextStageTarget() }} %)</h3>
      <p class="at-modal__sub">Le client doit saisir ce code pour faire avancer la barre. Communiquez-le-lui vous-même.</p>
      <code class="at-code">{{ $trf->unlock_code }}</code>
      <div class="at-modal__foot" style="margin-top:0">
        <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Fermer</button>
        <button type="button" class="btn-accent btn-sm-pro" x-data="{ok:false}" @click="navigator.clipboard && navigator.clipboard.writeText('{{ $trf->unlock_code }}'); ok = true; setTimeout(()=>ok=false,2000)">
          <i class="fas fa-copy"></i> <span x-text="ok ? 'Copié' : 'Copier le code'"></span>
        </button>
      </div>
    </div>
  </div>
  @endif

  {{-- Modale : facturer les frais --}}
  @if($trf->status === \App\Models\Transfer::STATUS_PENDING)
  <div class="at-modal" x-show="m === 'invoice'" x-cloak x-transition.opacity>
    <div class="at-modal__bg" @click="m = null"></div>
    <div class="at-modal__box" @click.stop>
      <button type="button" class="at-modal__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <div class="at-modal__ico" style="background:#DBEAFE;color:var(--c-blue)"><i class="fas fa-file-invoice"></i></div>
      <h3 class="at-modal__title">Facturer les frais</h3>
      <p class="at-modal__sub">Une facture liée à {{ $trf->reference }} sera créée et envoyée au client (e-mail + notification).</p>
      <form method="POST" action="{{ route('admin.transfers.invoice', $trf) }}">
        @csrf
        <input type="hidden" name="_trf" value="{{ $trf->id }}">
        @if((int) old('_trf') === $trf->id && ($errors->has('payment_iban') || $errors->has('payment_bic') || $errors->has('fee_amount')))
        <div style="margin-bottom:.85rem;font-size:.78rem;color:#dc2626"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first('fee_amount') ?: ($errors->first('payment_iban') ?: $errors->first('payment_bic')) }}</div>
        @endif
        <div class="at-grid">
          <div>
            <label class="form-label-pro">Montant des frais *</label>
            <input type="number" name="fee_amount" class="form-control-pro" step="0.01" min="0.01" placeholder="0,00" required>
          </div>
          <div>
            <label class="form-label-pro">Description</label>
            <input type="text" name="description" class="form-control-pro" placeholder="Frais de traitement…" maxlength="500">
          </div>
          <div class="full">
            <label class="form-label-pro">IBAN de règlement *</label>
            <input type="text" name="payment_iban" class="form-control-pro" maxlength="40" required style="font-family:monospace;text-transform:uppercase"
              value="{{ old('payment_iban', $site->payment_iban) }}" placeholder="DE00 0000 0000 0000 0000 00">
          </div>
          <div>
            <label class="form-label-pro">Bénéficiaire de l’IBAN</label>
            <input type="text" name="payment_holder" class="form-control-pro" maxlength="100" value="{{ old('payment_holder', $site->payment_holder) }}" placeholder="{{ site_name() }}">
          </div>
          <div>
            <label class="form-label-pro">BIC</label>
            <input type="text" name="payment_bic" class="form-control-pro" maxlength="11" style="font-family:monospace;text-transform:uppercase"
              value="{{ old('payment_bic', $site->payment_bic) }}" placeholder="SOLBDEFF">
          </div>
          <div class="full">
            <label class="form-label-pro">Type de virement à exécuter</label>
            <select name="payment_type" class="form-control-pro">
              <option value="sepa" {{ old('payment_type', $site->payment_type ?: 'sepa') === 'sepa' ? 'selected' : '' }}>Virement SEPA</option>
              <option value="instant" {{ old('payment_type', $site->payment_type) === 'instant' ? 'selected' : '' }}>Virement en temps réel</option>
            </select>
          </div>
        </div>
        <div class="at-modal__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-accent btn-sm-pro"><i class="fas fa-paper-plane"></i> Créer &amp; envoyer</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  @else
  <div class="at-done">
    @if($trf->processed_at)
      <i class="fas fa-clock" style="margin-right:.35rem"></i>Traité le {{ $trf->processed_at->format('d/m/Y à H:i') }}@if($trf->admin) · par {{ $trf->admin->name }}@endif
    @endif
  </div>
  @endif

</div>
@endforeach
</div>

@if($transfers->hasPages())
<div style="margin-top:1.5rem">{{ $transfers->links('partials.pagination') }}</div>
@endif
@endif

@endsection
