@extends('layouts.dashboard')
@section('title', 'Demandes de prêt')
@section('page_title', 'Demandes de prêt')

@section('content')
@push('styles')
<style>
[x-cloak]{display:none !important}
.ln-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.1rem}
.ln-tab{padding:.45rem .95rem;border-radius:999px;border:1px solid var(--c-border);background:var(--c-white,#fff);color:var(--c-navy);font-size:.78rem;font-weight:700;text-decoration:none}
.ln-tab.is-on{background:var(--c-accent);border-color:var(--c-accent);color:var(--c-on-accent,#fff)}
.ln-card{background:var(--c-white,#fff);border:1px solid var(--c-border);border-radius:14px;margin-bottom:.9rem;overflow:hidden;box-shadow:0 1px 3px rgba(15,13,54,.05)}
.ln-top{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr) auto;gap:1.25rem;align-items:center;padding:1rem 1.3rem}
.ln-t{font-size:.66rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700;margin-bottom:.2rem}
.ln-n{font-size:.9rem;font-weight:700;color:var(--c-navy)}
.ln-sub{font-size:.75rem;color:var(--c-muted)}
.ln-amt{font-family:'Space Grotesk',sans-serif;font-size:1.25rem;font-weight:800;color:var(--c-navy)}
.ln-badge{font-size:.66rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.2rem .6rem;border-radius:999px;white-space:nowrap}
.ln-badge--pending{background:rgba(245,158,11,.16);color:#b45309}
.ln-badge--approved{background:rgba(22,163,74,.14);color:#15803d}
.ln-badge--rejected{background:rgba(220,38,38,.12);color:#b91c1c}
.ln-badge--cancelled{background:rgba(100,116,139,.16);color:#475569}
.ln-mid{padding:.75rem 1.3rem;border-top:1px solid var(--c-border);background:var(--c-bg);font-size:.8rem;display:flex;gap:1.5rem;flex-wrap:wrap}
.ln-act{padding:.9rem 1.3rem;border-top:1px solid var(--c-border);display:flex;gap:.6rem;flex-wrap:wrap;align-items:center}
.ln-act input[type=text]{flex:1 1 240px;min-width:0;padding:.55rem .85rem;border:1.5px solid var(--c-border);border-radius:10px;font-family:inherit;font-size:.84rem;background:var(--c-bg);color:var(--c-navy)}
.ln-b{border:0;border-radius:999px;padding:.55rem 1.1rem;font-weight:800;font-size:.8rem;cursor:pointer;font-family:inherit;display:inline-flex;gap:.45rem;align-items:center}
.ln-b--ok{background:#16a34a;color:#fff}.ln-b--ok:hover{background:#15803d}
.ln-b--ko{background:rgba(220,38,38,.1);color:#b91c1c}.ln-b--ko:hover{background:rgba(220,38,38,.18)}
@media (max-width:820px){.ln-top{grid-template-columns:1fr;gap:.8rem}}
</style>
@endpush

<div class="page-hdr-row">
  <div class="page-hdr">
    <h1>Demandes de prêt</h1>
    <p>Prêts demandés par les clients depuis leur application : acceptez ou refusez, le client est prévenu dans sa langue.</p>
  </div>
</div>

@if(session('success'))<div class="flash flash-ok" style="margin-bottom:1rem"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>@endif
@if($errors->any())<div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

<div class="ln-tabs">
  <a class="ln-tab {{ $status === null ? 'is-on' : '' }}" href="{{ route('admin.loans.index') }}">Toutes</a>
  <a class="ln-tab {{ $status === 'pending' ? 'is-on' : '' }}" href="{{ route('admin.loans.index', ['status' => 'pending']) }}">En attente @if($pendingCount)({{ $pendingCount }})@endif</a>
  <a class="ln-tab {{ $status === 'approved' ? 'is-on' : '' }}" href="{{ route('admin.loans.index', ['status' => 'approved']) }}">Acceptées</a>
  <a class="ln-tab {{ $status === 'rejected' ? 'is-on' : '' }}" href="{{ route('admin.loans.index', ['status' => 'rejected']) }}">Refusées</a>
</div>

@php
  $purposes = ['personal' => 'Projet personnel', 'auto' => 'Véhicule', 'home' => 'Habitat / travaux', 'business' => 'Activité professionnelle', 'education' => 'Études'];
  $labels   = ['pending' => 'En attente', 'approved' => 'Acceptée', 'rejected' => 'Refusée', 'cancelled' => 'Annulée'];
@endphp

@forelse($applications as $a)
<div class="ln-card">
  <div class="ln-top">
    <div>
      <div class="ln-t">Client</div>
      <div class="ln-n">{{ $a->user->name }}</div>
      <div class="ln-sub">{{ $a->user->email }} · {{ $a->reference }}</div>
    </div>
    <div>
      <div class="ln-t">Montant demandé</div>
      <div class="ln-amt">{{ number_format($a->amount, 2, ',', ' ') }} {{ $a->currency }}</div>
      <div class="ln-sub">{{ $a->duration_months }} mois · {{ number_format($a->monthly_payment, 2, ',', ' ') }} {{ $a->currency }}/mois · {{ rtrim(rtrim(number_format($a->interest_rate, 2, ',', ''), '0'), ',') }} %</div>
    </div>
    <span class="ln-badge ln-badge--{{ $a->status }}">{{ $labels[$a->status] ?? $a->status }}</span>
  </div>
  <div class="ln-mid">
    <span><strong>Objet :</strong> {{ $purposes[$a->purpose] ?? $a->purpose }}</span>
    <span><strong>Coût du crédit :</strong> {{ number_format($a->total_cost, 2, ',', ' ') }} {{ $a->currency }}</span>
    <span><strong>Demandé le :</strong> {{ $a->created_at->format('d/m/Y H:i') }}</span>
    @if($a->message)<span style="flex:1 1 100%"><strong>Message du client :</strong> {{ $a->message }}</span>@endif
    @if($a->admin_note)<span style="flex:1 1 100%"><strong>Votre réponse :</strong> {{ $a->admin_note }}</span>@endif
  </div>
  @if($a->isPending())
  <div class="ln-act" x-data="{ note: '' }">
    <input type="text" x-model="note" maxlength="1000" placeholder="Message pour le client (facultatif)">
    <form method="POST" action="{{ route('admin.loans.approve', $a) }}"
          data-confirm="Accepter la demande {{ $a->reference }} de {{ number_format($a->amount, 2, ',', ' ') }} {{ $a->currency }} ? {{ addslashes($a->user->name) }} sera prévenu(e)."
          data-confirm-title="Accepter la demande" data-confirm-ok="Accepter">
      @csrf <input type="hidden" name="admin_note" :value="note">
      <button type="submit" class="ln-b ln-b--ok"><i class="fas fa-check"></i> Accepter</button>
    </form>
    <form method="POST" action="{{ route('admin.loans.reject', $a) }}"
          data-confirm="Refuser la demande {{ $a->reference }} ? {{ addslashes($a->user->name) }} sera prévenu(e)."
          data-confirm-title="Refuser la demande" data-confirm-ok="Refuser" data-confirm-danger="1">
      @csrf <input type="hidden" name="admin_note" :value="note">
      <button type="submit" class="ln-b ln-b--ko"><i class="fas fa-times"></i> Refuser</button>
    </form>
  </div>
  @endif
</div>
@empty
<div class="ln-card" style="padding:2rem;text-align:center;color:var(--c-muted)">Aucune demande de prêt.</div>
@endforelse

<div style="margin-top:1rem">{{ $applications->links() }}</div>
@endsection
