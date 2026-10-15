@extends('layouts.dashboard')
@section('title', 'Compte — ' . $account->name . ' — ' . site_name())
@section('page_title', 'Compte client')

@section('content')
<style>
[x-cloak]{display:none !important}
/* ── Top nav ── */
.acs-back{display:inline-flex;align-items:center;gap:.5rem;font-size:.8125rem;color:var(--c-muted);text-decoration:none;margin-bottom:1.375rem;font-weight:500;transition:.15s}
.acs-back:hover{color:var(--c-accent)}

/* ── Hero card ── */
.acs-hero{background:linear-gradient(135deg,#3A3590 0%,#231F5F 100%);border-radius:var(--radius-md);padding:2rem 2.25rem;color:#fff;display:grid;grid-template-columns:auto 1fr auto;gap:1.75rem;align-items:center;margin-bottom:1.75rem}
@media(max-width:640px){.acs-hero{grid-template-columns:1fr;padding:1.5rem;text-align:center}}
.acs-avatar-lg{width:68px;height:68px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.875rem;font-weight:800;border:2.5px solid rgba(255,255,255,.25)}
.acs-info__name{font-size:1.375rem;font-weight:800;margin-bottom:.25rem}
.acs-info__sub{font-size:.8rem;color:rgba(255,255,255,.55);line-height:1.7}
.acs-balance-box{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:var(--radius-sm);padding:1.25rem 1.75rem;text-align:center;min-width:180px}
.acs-balance-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.45);margin-bottom:.5rem}
.acs-balance-val{font-family:'Space Grotesk',sans-serif;font-size:2.25rem;font-weight:900;line-height:1}
.acs-balance-cur{font-size:.875rem;font-weight:600;color:rgba(255,255,255,.6);margin-left:.3rem}

/* ── Alert / flash ── */
.acs-flash{display:flex;align-items:center;gap:.625rem;padding:.875rem 1.125rem;border-radius:var(--radius-sm);margin-bottom:1.375rem;font-size:.8125rem;font-weight:500}
.acs-flash--ok{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#15803d}
.acs-flash--err{background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);color:#b91c1c}

/* ── Ops grid ── */
.acs-ops{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem}
@media(max-width:580px){.acs-ops{grid-template-columns:1fr}}

.acs-op{background:var(--c-bg);border:1.5px solid var(--c-border);border-radius:var(--radius-md);padding:1.375rem 1.5rem;transition:.2s}
.acs-op:hover{border-color:rgba(0,132,87,.35)}

.acs-op__head{display:flex;align-items:center;gap:.625rem;font-size:.9375rem;font-weight:700;margin-bottom:1.125rem}
.acs-op__head--credit{color:#16a34a}
.acs-op__head--debit{color:#dc2626}
.acs-op__head i{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem}
.acs-op__head--credit i{background:rgba(22,163,74,.12)}
.acs-op__head--debit i{background:rgba(220,38,38,.1)}

.acs-field{margin-bottom:.875rem}
.acs-field label{display:block;font-size:.72rem;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}
.acs-field input,.acs-field textarea{width:100%;padding:.55rem .875rem;border:1.5px solid var(--c-border);border-radius:var(--radius-sm);font-size:.875rem;background:var(--c-bg);color:var(--c-navy);outline:none;transition:.2s;font-family:inherit}
.acs-field select{width:100%;padding:.55rem 2rem .55rem .875rem;border:1.5px solid var(--c-border);border-radius:var(--radius-sm);font-size:.875rem;background-color:var(--c-bg);color:var(--c-navy);outline:none;transition:.2s;font-family:inherit;min-height:42px;cursor:pointer}
.acs-field select:focus{border-color:var(--c-accent);box-shadow:0 0 0 3px rgba(0,132,87,.1)}
.acs-field select option:disabled{color:#9ca3af}
.acs-row2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
@media(max-width:480px){.acs-row2{grid-template-columns:1fr}}
.acs-hint{font-size:.72rem;color:var(--c-muted);margin-top:.35rem;line-height:1.45}
.acs-hint--warn{color:#b45309}
.acs-cardinfo{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;padding:.6rem .8rem;margin-bottom:.875rem;border-radius:var(--radius-sm);background:rgba(27,73,118,.06);border:1px solid var(--c-border);font-size:.78rem}
.acs-cardinfo__bar{flex:1 1 100%;height:6px;border-radius:999px;background:var(--c-border);overflow:hidden}
.acs-cardinfo__bar span{display:block;height:100%;border-radius:999px;background:var(--c-accent)}
.acs-mvt__kind{display:inline-block;font-size:.66rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.12rem .5rem;border-radius:999px;margin-right:.4rem;vertical-align:middle}
.acs-mvt__kind--credit{background:rgba(22,163,74,.12);color:#15803d}
.acs-mvt__kind--debit{background:rgba(220,38,38,.1);color:#b91c1c}
.acs-mvt__sub{white-space:normal}
@media(max-width:560px){.acs-mvt{flex-wrap:wrap}.acs-mvt__right{min-width:0;margin-left:calc(38px + 1rem);text-align:left;width:calc(100% - 38px - 1rem)}}
.acs-field input:focus,.acs-field textarea:focus{border-color:var(--c-accent);box-shadow:0 0 0 3px rgba(0,132,87,.1)}

.btn-credit{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.65rem 1.25rem;border-radius:var(--radius-sm);font-size:.8125rem;font-weight:700;border:none;cursor:pointer;background:#16a34a;color:#fff;transition:.15s}
.btn-credit:hover{background:#15803d}
.btn-debit{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.65rem 1.25rem;border-radius:var(--radius-sm);font-size:.8125rem;font-weight:700;border:none;cursor:pointer;background:#dc2626;color:#fff;transition:.15s}
.btn-debit:hover{background:#b91c1c}

/* ── Movement history ── */
.acs-section{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.acs-section-title{font-size:1rem;font-weight:700;color:var(--c-navy);display:flex;align-items:center;gap:.5rem}

.acs-timeline{display:flex;flex-direction:column;gap:.5rem}
.acs-mvt{display:flex;align-items:center;gap:1rem;background:var(--c-bg);border:1.5px solid var(--c-border);border-radius:var(--radius-sm);padding:.875rem 1.125rem;transition:.15s}
.acs-mvt:hover{border-color:rgba(0,132,87,.3)}
.acs-mvt__ico{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.acs-mvt__ico--credit{background:rgba(22,163,74,.12);color:#16a34a}
.acs-mvt__ico--debit{background:rgba(220,38,38,.1);color:#dc2626}
.acs-mvt__body{flex:1;min-width:0}
.acs-mvt__label{font-size:.8125rem;font-weight:600;color:var(--c-navy)}
.acs-mvt__sub{font-size:.72rem;color:var(--c-muted);margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.acs-mvt__right{text-align:right;flex-shrink:0;min-width:120px}
.acs-mvt__amount--credit{font-family:'Space Grotesk',sans-serif;font-size:.9375rem;font-weight:800;color:#16a34a}
.acs-mvt__amount--debit{font-family:'Space Grotesk',sans-serif;font-size:.9375rem;font-weight:800;color:#dc2626}
.acs-mvt__balance{font-size:.7rem;color:var(--c-muted);margin-top:.15rem}

.acs-date-sep{font-size:.67rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700;padding:.75rem 0 .25rem}

.acs-empty{text-align:center;padding:3.5rem 2rem;color:var(--c-muted);background:var(--c-bg);border:1.5px solid var(--c-border);border-radius:var(--radius-md)}
.acs-empty i{font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.2}
</style>

<a href="{{ route('admin.accounts.index') }}" class="acs-back">
  <i class="fas fa-arrow-left"></i> Retour aux comptes
</a>

@php
  $cur = $account->currency ?? \App\Models\Currency::default();
  $bal = (float) $account->balance;
  $balColor = $bal >= 0 ? '#4ade80' : '#f87171';
@endphp

{{-- Hero --}}
<div class="acs-hero">
  <div class="acs-avatar-lg">{{ strtoupper(substr($account->name, 0, 1)) }}</div>
  <div class="acs-info">
    <div class="acs-info__name">{{ $account->name }}</div>
    <div class="acs-info__sub">
      {{ $account->email }}
      @if($account->phone)<br><i class="fas fa-phone" style="font-size:.65rem;opacity:.6;margin-right:.3rem"></i>{{ $account->phone }}@endif
      @if($account->bankAccount)<br><i class="fas fa-university" style="font-size:.65rem;opacity:.6;margin-right:.3rem"></i><span style="font-family:monospace;font-size:.78rem">{{ chunk_split((string) $account->bankAccount->iban, 4, ' ') }}</span>@endif
    </div>
  </div>
  <div class="acs-balance-box">
    <div class="acs-balance-lbl">Solde disponible</div>
    <div>
      <span class="acs-balance-val" style="color:{{ $balColor }}">{{ number_format($bal, 2, ',', ' ') }}</span>
      <span class="acs-balance-cur">{{ $cur }}</span>
    </div>
  </div>
</div>

@if($errors->any())
<div class="acs-flash acs-flash--err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

{{-- Credit / Debit forms --}}
@php
  $kindLabels = ['sepa' => 'Virement SEPA', 'instant' => 'Virement en temps réel', 'deposit' => 'Dépôt', 'refund' => 'Remboursement', 'adjustment' => 'Ajustement / correction',
                 'card' => 'Paiement par carte', 'withdrawal' => 'Retrait', 'fee' => 'Frais'];
  $cardActive = $card && $card->status === \App\Models\Card::STATUS_ACTIVE;
  $cardLimit  = $card ? (int) ($card->spending_limit ?: \App\Models\Card::LIMIT_MIN) : 0;
  $cardPct    = $cardLimit > 0 ? min(100, round($cardSpent / $cardLimit * 100)) : 0;
@endphp
<div class="acs-ops">
@foreach(['credit' => 'Créditer le compte', 'debit' => 'Débiter le compte'] as $dir => $title)
  @php $isCredit = $dir === 'credit'; $firstKind = \App\Models\AccountMovement::KINDS[$dir][0]; @endphp
  <div class="acs-op">
    <div class="acs-op__head acs-op__head--{{ $dir }}">
      <i class="fas fa-{{ $isCredit ? 'plus' : 'minus' }}"></i> {{ $title }}
    </div>
    <form method="POST" action="{{ route('admin.accounts.' . $dir, $account) }}"
          x-data="{ kind: '{{ old('kind', $firstKind) }}', amount: '{{ old('amount') }}', labels: @js($kindLabels) }"
          :data-confirm="'{{ $isCredit ? 'Créditer' : 'Débiter' }} ' + (amount || '0') + ' {{ $cur }} ({{ $isCredit ? 'crédit' : 'débit' }} : ' + (labels[kind] || kind) + ') sur le compte de {{ addslashes($account->name) }} ? Le client sera prévenu par notification et par e-mail, avec la facture en pièce jointe.'"
          data-confirm-title="{{ $isCredit ? 'Confirmer le crédit' : 'Confirmer le débit' }}" data-confirm-ok="{{ $isCredit ? 'Créditer' : 'Débiter' }}" @if(! $isCredit) data-confirm-danger="1" @endif>
      @csrf
      <div class="acs-field">
        <label>Type d'opération</label>
        <select name="kind" x-model="kind" required>
          @foreach(\App\Models\AccountMovement::KINDS[$dir] as $k)
          <option value="{{ $k }}" @disabled($k === 'card' && ! $cardActive)>{{ $kindLabels[$k] }}@if($k === 'card'){{ $card ? ' (•••• ' . $card->last_four . ($cardActive ? '' : ' — ' . ($card->isSuspended() ? 'suspendue' : 'bloquée')) . ')' : ' — aucune carte' }}@endif</option>
          @endforeach
        </select>
      </div>

      @unless($isCredit)
      <div class="acs-cardinfo" x-show="kind === 'card'" x-cloak>
        <i class="fas fa-credit-card"></i>
        <span><strong>{{ number_format($cardSpent, 2, ',', ' ') }}</strong> dépensés ce mois-ci sur un plafond de <strong>{{ number_format($cardLimit, 0, ',', ' ') }} {{ $cur }}</strong></span>
        <div class="acs-cardinfo__bar"><span style="width:{{ $cardPct }}%"></span></div>
      </div>
      @endunless

      <div class="acs-field">
        <label>Montant ({{ $cur }})@unless($isCredit) — solde actuel : <span style="color:{{ $bal < 0 ? '#f87171' : 'inherit' }}">{{ number_format($bal, 2, ',', ' ') }}</span>@endunless</label>
        <input type="number" name="amount" step="0.01" min="0.01" max="9999999" placeholder="0,00" value="{{ old('amount') }}" x-model="amount" required>
      </div>

      <div class="acs-row2">
        <div class="acs-field">
          <label x-text="kind === 'card' ? 'Commerçant *' : '{{ $isCredit ? 'Expéditeur' : 'Bénéficiaire' }}'"></label>
          <input type="text" name="counterparty" maxlength="120" value="{{ old('counterparty') }}"
                 :placeholder="kind === 'card' ? 'Ex : Carrefour, Amazon…' : '{{ $isCredit ? 'Nom de l’expéditeur' : 'Nom du bénéficiaire' }}'" :required="kind === 'card'">
        </div>
        <div class="acs-field">
          <label>Référence</label>
          <input type="text" name="reference" maxlength="60" value="{{ old('reference') }}" placeholder="N° de virement, de ticket…">
        </div>
      </div>

      <div class="acs-field" x-show="kind === 'sepa' || kind === 'instant'" x-cloak>
        <label>IBAN {{ $isCredit ? 'de l’expéditeur' : 'du bénéficiaire' }}</label>
        <input type="text" name="counterparty_iban" maxlength="40" value="{{ old('counterparty_iban') }}" placeholder="DE00 0000 0000 0000 0000 00" style="font-family:monospace;text-transform:uppercase">
      </div>

      <div class="acs-field">
        <label>Note / motif</label>
        <input type="text" name="note" maxlength="255" placeholder="{{ $isCredit ? 'Ex : remboursement, ajustement…' : 'Ex : frais, correction…' }}" value="{{ old('note') }}">
      </div>

      @if(! $isCredit && $bal < 0)
      <p class="acs-hint acs-hint--warn" style="margin:-.25rem 0 .75rem"><i class="fas fa-triangle-exclamation"></i> Solde négatif ({{ number_format($bal, 2, ',', ' ') }} {{ $cur }}) — le débit sera régularisé lors du prochain crédit.</p>
      @endif
      <p class="acs-hint" style="margin:-.25rem 0 .75rem"><i class="fas fa-envelope"></i> Le client est prévenu par notification et par e-mail, dans sa langue.</p>

      <button type="submit" class="btn-{{ $dir }}"><i class="fas fa-{{ $isCredit ? 'plus' : 'minus' }}"></i> {{ $isCredit ? 'Créditer' : 'Débiter' }}</button>
    </form>
  </div>
@endforeach
</div>

{{-- Movement history --}}
<div class="acs-section">
  <span class="acs-section-title"><i class="fas fa-history" style="color:var(--c-muted)"></i> Historique des mouvements</span>
  <span style="font-size:.75rem;color:var(--c-muted)">{{ $movements->total() }} opération{{ $movements->total() > 1 ? 's' : '' }}</span>
</div>

@if($movements->isEmpty())
  <div class="acs-empty">
    <i class="fas fa-inbox"></i>
    Aucun mouvement enregistré sur ce compte.
  </div>
@else

<div class="acs-timeline">
@php $lastDate = null; @endphp
@foreach($movements as $mvt)
@php
  $dl = $mvt->created_at->isToday() ? "Aujourd'hui" : ($mvt->created_at->isYesterday() ? 'Hier' : $mvt->created_at->format('d/m/Y'));
@endphp
@if($dl !== $lastDate)
  <div class="acs-date-sep">{{ $dl }}</div>
  @php $lastDate = $dl; @endphp
@endif
<div class="acs-mvt">
  <div class="acs-mvt__ico acs-mvt__ico--{{ $mvt->type }}">
    <i class="fas fa-{{ $mvt->type === 'credit' ? 'plus' : 'minus' }}"></i>
  </div>
  <div class="acs-mvt__body">
    <div class="acs-mvt__label">
      <span class="acs-mvt__kind acs-mvt__kind--{{ $mvt->type }}">{{ $mvt->type === 'credit' ? 'Crédit' : 'Débit' }}</span>{{ $mvt->kind ? __('movement.kind_' . $mvt->kind, [], 'fr') : 'Opération' }}
    </div>
    @php
      $detail = collect([$mvt->counterparty, $mvt->counterparty_iban ? \App\Models\Invoice::formatIban($mvt->counterparty_iban) : null, $mvt->reference])->filter()->implode(' · ');
    @endphp
    @if($detail)<div class="acs-mvt__sub">{{ $detail }}</div>@endif
    @if($mvt->note)<div class="acs-mvt__sub" style="font-style:italic">{{ $mvt->note }}</div>@endif
    <div class="acs-mvt__sub">{{ $mvt->created_at->format('H:i') }}@if($mvt->admin) · par {{ $mvt->admin->name }}@endif</div>
  </div>
  <div class="acs-mvt__right">
    <div class="acs-mvt__amount--{{ $mvt->type }}">
      {{ $mvt->type === 'credit' ? '+' : '-' }}{{ number_format($mvt->amount, 2, ',', ' ') }} {{ $mvt->currency }}
    </div>
    <div class="acs-mvt__balance">{{ number_format($mvt->balance_after, 2, ',', ' ') }} {{ $mvt->currency }}</div>
  </div>
</div>
@endforeach
</div>

@if($movements->hasPages())
<div style="padding:1.25rem 0">{{ $movements->links() }}</div>
@endif
@endif

@endsection
