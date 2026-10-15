@extends('layouts.dashboard')
@section('title', 'Tableau de bord — ' . site_name())
@section('page_title', 'Vue d\'ensemble')

@push('styles')
<style>
.adb-hero { background: linear-gradient(135deg, #312B81 0%, #3C3694 100%); border-radius: 16px; padding: 1.75rem 2rem; margin-bottom: 1.5rem; color: #fff; }
.adb-hero-tag { font-size: .62rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--c-accent); margin-bottom: .4rem; }
.adb-hero-title { font-size: 1.375rem; font-weight: 900; line-height: 1.2; margin-bottom: .25rem; }
.adb-hero-sub { font-size: .8rem; color: rgba(255,255,255,.55); }
.adb-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 991px) { .adb-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
.adb-kpi { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 14px; padding: 1.25rem 1.375rem; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.adb-kpi-ico { width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; margin-bottom: .75rem; }
.adb-kpi-val { font-size: 2rem; font-weight: 900; color: var(--c-navy); line-height: 1; margin-bottom: .25rem; }
.adb-kpi-lbl { font-size: .72rem; color: var(--c-muted); font-weight: 500; }
.adb-ico-navy  { background: rgba(49,43,129,.08); color: var(--c-navy); }
.adb-ico-green { background: rgba(5,150,105,.1); color: #059669; }
.adb-ico-amber { background: rgba(217,119,6,.1); color: #D97706; }
.adb-ico-accent{ background: rgba(0,132,87,.14); color: #006A45; }
.adb-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.adb-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--c-border); }
.adb-card-title { font-size: .9rem; font-weight: 800; margin: 0; }
.adb-row { display: flex; align-items: center; gap: .875rem; padding: .8rem 1.25rem; border-bottom: 1px solid var(--c-border); text-decoration: none; color: inherit; }
.adb-row:last-child { border-bottom: 0; }
.adb-row:hover { background: rgba(49,43,129,.03); }
.adb-av { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #312B81; color: #fff; font-weight: 800; font-size: .85rem; flex-shrink: 0; }
.adb-name { font-size: .85rem; font-weight: 700; }
.adb-mail { font-size: .74rem; color: var(--c-muted); }
.adb-date { margin-left: auto; font-size: .72rem; color: var(--c-muted); white-space: nowrap; }
.adb-empty { padding: 2.5rem 1rem; text-align: center; color: var(--c-muted); font-size: .85rem; }
</style>
@endpush

@section('content')
<div class="adb-hero">
  <div class="adb-hero-tag">{{ site_name() }}</div>
  <div class="adb-hero-title">Bonjour {{ \Illuminate\Support\Str::words(auth()->user()->name, 1, '') }}</div>
  <div class="adb-hero-sub">Voici l'état de vos clients et de leurs vérifications d'identité.</div>
</div>

<div class="adb-kpi-grid">
  <div class="adb-kpi">
    <div class="adb-kpi-ico adb-ico-navy"><i class="fas fa-users"></i></div>
    <div class="adb-kpi-val">{{ $stats['my_clients'] }}</div>
    <div class="adb-kpi-lbl">Mes clients</div>
  </div>
  <div class="adb-kpi">
    <div class="adb-kpi-ico adb-ico-green"><i class="fas fa-user-check"></i></div>
    <div class="adb-kpi-val">{{ $stats['verified'] }}</div>
    <div class="adb-kpi-lbl">Identités vérifiées</div>
  </div>
  <div class="adb-kpi">
    <div class="adb-kpi-ico adb-ico-amber"><i class="fas fa-hourglass-half"></i></div>
    <div class="adb-kpi-val">{{ $stats['pending_kyc'] }}</div>
    <div class="adb-kpi-lbl">Vérifications en attente</div>
  </div>
  <div class="adb-kpi">
    <div class="adb-kpi-ico adb-ico-accent"><i class="fas fa-user-plus"></i></div>
    <div class="adb-kpi-val">{{ $stats['new_this_month'] }}</div>
    <div class="adb-kpi-lbl">Nouveaux ce mois-ci</div>
  </div>
</div>

<div class="adb-card">
  <div class="adb-card-hdr">
    <h3 class="adb-card-title">Derniers clients</h3>
    <a href="{{ route('admin.users') }}" style="font-size:.78rem;font-weight:700;color:var(--c-accent)">Voir tous <i class="fas fa-arrow-right" style="font-size:.6rem"></i></a>
  </div>
  @forelse($recentClients as $client)
  <a href="{{ route('admin.users.show', $client) }}" class="adb-row">
    <div class="adb-av">{{ strtoupper(mb_substr($client->name, 0, 1)) }}</div>
    <div style="min-width:0">
      <div class="adb-name">{{ $client->name }}</div>
      <div class="adb-mail">{{ $client->email }}</div>
    </div>
    <div class="adb-date">{{ $client->created_at->format('d/m/Y') }}</div>
  </a>
  @empty
  <div class="adb-empty">Aucun client pour le moment.</div>
  @endforelse
</div>
@endsection
