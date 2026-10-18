@extends('layouts.dashboard')
@section('title', 'Réglages des prêts')
@section('page_title', 'Réglages des prêts')

@section('content')
@push('styles')
<style>
.ls-wrap{max-width:720px}
.ls-card{background:var(--c-white,#fff);border:1px solid var(--c-border);border-radius:14px;padding:1.4rem 1.5rem;margin-bottom:1.1rem;box-shadow:0 1px 3px rgba(15,13,54,.05)}
.ls-h{display:flex;align-items:center;gap:.6rem;font-weight:800;color:var(--c-navy);margin-bottom:1rem;font-size:.95rem}
.ls-h i{width:32px;height:32px;border-radius:50%;background:rgba(0,132,87,.12);color:var(--c-accent);display:flex;align-items:center;justify-content:center;font-size:.8rem}
.ls-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media(max-width:560px){.ls-grid{grid-template-columns:1fr}}
.ls-f label{display:block;font-size:.72rem;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}
.ls-f input{width:100%;padding:.6rem .85rem;border:1.5px solid var(--c-border);border-radius:var(--radius-sm,10px);font-size:.9rem;background:var(--c-bg);color:var(--c-navy);font-family:inherit}
.ls-f input:focus{outline:0;border-color:var(--c-accent);box-shadow:0 0 0 3px rgba(0,132,87,.1)}
.ls-hint{font-size:.74rem;color:var(--c-muted);margin-top:.35rem;line-height:1.45}
.ls-prev{display:flex;gap:1.5rem;flex-wrap:wrap;background:var(--c-bg);border-radius:12px;padding:.9rem 1.1rem;font-size:.84rem;color:var(--c-navy)}
.ls-prev strong{font-family:'Space Grotesk',sans-serif;font-size:1.1rem}
.ls-btn{border:0;border-radius:999px;padding:.7rem 1.6rem;background:var(--c-accent);color:var(--c-on-accent,#fff);font-weight:800;font-size:.88rem;cursor:pointer;font-family:inherit}
</style>
@endpush

<div class="page-hdr-row">
  <div class="page-hdr">
    <h1>Réglages des prêts</h1>
    <p>Taux, montants et durées proposés aux clients lorsqu'ils demandent un prêt depuis leur application. Les demandes déjà envoyées gardent leurs conditions.</p>
  </div>
  <a href="{{ route('admin.loans.index') }}" class="btn-secondary" style="align-self:flex-start;text-decoration:none"><i class="fas fa-arrow-left"></i> Demandes de prêt</a>
</div>

@if(session('success'))<div class="flash flash-ok" style="margin-bottom:1rem"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
@if($errors->any())<div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

<form class="ls-wrap" method="POST" action="{{ route('admin.loans.settings.update') }}"
      x-data="{ rate: {{ old('annual_rate', $s['rate']) }}, min: {{ old('min_amount', $s['min']) }}, max: {{ old('max_amount', $s['max']) }}, dmin: {{ old('duration_min', $s['dmin']) }}, dmax: {{ old('duration_max', $s['dmax']) }},
        pay(p, n) { var r = this.rate / 12 / 100; return r > 0 ? p * r / (1 - Math.pow(1 + r, -n)) : p / n; },
        fmt(v) { return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v); } }"
      data-confirm="Enregistrer ces réglages ? Ils s'appliquent aux nouvelles demandes de prêt." data-confirm-title="Réglages des prêts" data-confirm-ok="Enregistrer">
  @csrf

  <div class="ls-card">
    <div class="ls-h"><i class="fas fa-percent"></i> Taux d'intérêt</div>
    <div class="ls-f">
      <label for="annual_rate">Taux annuel (%)</label>
      <input id="annual_rate" type="number" name="annual_rate" step="0.01" min="0" max="100" x-model.number="rate" required>
      <div class="ls-hint">Appliqué à la simulation et à la mensualité affichées au client. Mettez 0 pour un prêt sans intérêt.</div>
    </div>
  </div>

  <div class="ls-card">
    <div class="ls-h"><i class="fas fa-coins"></i> Montants proposés</div>
    <div class="ls-grid">
      <div class="ls-f"><label for="min_amount">Montant minimum</label><input id="min_amount" type="number" name="min_amount" step="1" min="1" x-model.number="min" required></div>
      <div class="ls-f"><label for="max_amount">Montant maximum</label><input id="max_amount" type="number" name="max_amount" step="1" min="1" x-model.number="max" required></div>
    </div>
  </div>

  <div class="ls-card">
    <div class="ls-h"><i class="fas fa-calendar-days"></i> Durées proposées (en mois)</div>
    <div class="ls-grid">
      <div class="ls-f"><label for="duration_min">Durée minimale</label><input id="duration_min" type="number" name="duration_min" step="1" min="1" max="600" x-model.number="dmin" required></div>
      <div class="ls-f"><label for="duration_max">Durée maximale</label><input id="duration_max" type="number" name="duration_max" step="1" min="1" max="600" x-model.number="dmax" required></div>
    </div>
  </div>

  <div class="ls-card">
    <div class="ls-h"><i class="fas fa-calculator"></i> Aperçu pour le client</div>
    <div class="ls-prev">
      <span>Le plus petit prêt<br><strong x-text="fmt(pay(min, dmax))"></strong> / mois <small>(<span x-text="new Intl.NumberFormat('fr-FR').format(min)"></span> sur <span x-text="dmax"></span> mois)</small></span>
      <span>Le plus grand prêt<br><strong x-text="fmt(pay(max, dmax))"></strong> / mois <small>(<span x-text="new Intl.NumberFormat('fr-FR').format(max)"></span> sur <span x-text="dmax"></span> mois)</small></span>
    </div>
  </div>

  <button type="submit" class="ls-btn"><i class="fas fa-floppy-disk"></i> Enregistrer les réglages</button>
</form>
@endsection
