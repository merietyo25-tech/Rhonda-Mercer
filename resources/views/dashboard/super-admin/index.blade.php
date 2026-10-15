@extends('layouts.dashboard')
@section('title', 'Super Admin — ' . site_name())
@section('page_title', 'Vue d\'ensemble')

@push('styles')
<style>
.sad-hero { background: linear-gradient(135deg, #312B81 0%, #3C3694 100%); border-radius: 16px; padding: 1.75rem 2rem; margin-bottom: 1.5rem; color: #fff; }
.sad-hero-tag { font-size: .62rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--c-accent); margin-bottom: .4rem; }
.sad-hero-title { font-size: 1.375rem; font-weight: 900; line-height: 1.2; margin-bottom: .25rem; }
.sad-hero-sub { font-size: .8rem; color: rgba(255,255,255,.55); }
.sad-kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 991px) { .sad-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .sad-kpi-grid { grid-template-columns: 1fr; } }
.sad-kpi { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 14px; padding: 1.25rem 1.375rem; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.sad-kpi-ico { width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; margin-bottom: .75rem; }
.sad-kpi-val { font-size: 2rem; font-weight: 900; color: var(--c-navy); line-height: 1; margin-bottom: .25rem; }
.sad-kpi-lbl { font-size: .72rem; color: var(--c-muted); font-weight: 500; }
.sad-ico-navy  { background: rgba(49,43,129,.08); color: var(--c-navy); }
.sad-ico-green { background: rgba(5,150,105,.1); color: #059669; }
.sad-ico-amber { background: rgba(217,119,6,.1); color: #D97706; }
.sad-ico-accent{ background: rgba(0,132,87,.14); color: #006A45; }
.sad-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.sad-card-hdr { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--c-border); }
.sad-card-title { font-size: .9rem; font-weight: 800; margin: 0; }
.sad-row { display: flex; align-items: center; gap: .875rem; padding: .8rem 1.25rem; border-bottom: 1px solid var(--c-border); }
.sad-row:last-child { border-bottom: 0; }
.sad-av { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #312B81; color: #fff; font-weight: 800; font-size: .85rem; flex-shrink: 0; }
.sad-name { font-size: .85rem; font-weight: 700; }
.sad-mail { font-size: .74rem; color: var(--c-muted); }
.sad-role { margin-left: auto; font-size: .68rem; font-weight: 700; padding: .2rem .65rem; border-radius: 999px; background: rgba(49,43,129,.08); color: var(--c-navy); white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="sad-hero">
  <div class="sad-hero-tag">Super administration</div>
  <div class="sad-hero-title">{{ site_name() }}</div>
  <div class="sad-hero-sub">Vue globale des comptes et des vérifications d'identité.</div>
</div>

<div class="sad-kpi-grid">
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-navy"><i class="fas fa-users"></i></div>
    <div class="sad-kpi-val">{{ $stats['total_users'] }}</div>
    <div class="sad-kpi-lbl">Utilisateurs</div>
  </div>
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-accent"><i class="fas fa-user"></i></div>
    <div class="sad-kpi-val">{{ $stats['total_clients'] }}</div>
    <div class="sad-kpi-lbl">Clients</div>
  </div>
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-navy"><i class="fas fa-user-shield"></i></div>
    <div class="sad-kpi-val">{{ $stats['total_staff'] }}</div>
    <div class="sad-kpi-lbl">Équipe (staff)</div>
  </div>
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-amber"><i class="fas fa-hourglass-half"></i></div>
    <div class="sad-kpi-val">{{ $stats['kyc_pending'] }}</div>
    <div class="sad-kpi-lbl">Vérifications en attente</div>
  </div>
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-green"><i class="fas fa-user-check"></i></div>
    <div class="sad-kpi-val">{{ $stats['kyc_approved'] }}</div>
    <div class="sad-kpi-lbl">Identités vérifiées</div>
  </div>
  <div class="sad-kpi">
    <div class="sad-kpi-ico sad-ico-accent"><i class="fas fa-user-plus"></i></div>
    <div class="sad-kpi-val">{{ $stats['new_this_month'] }}</div>
    <div class="sad-kpi-lbl">Nouveaux clients ce mois-ci</div>
  </div>
</div>

<div class="sad-card">
  <div class="sad-card-hdr">
    <h3 class="sad-card-title">Derniers comptes créés</h3>
    <a href="{{ route('admin.users') }}" style="font-size:.78rem;font-weight:700;color:var(--c-accent)">Gérer les utilisateurs <i class="fas fa-arrow-right" style="font-size:.6rem"></i></a>
  </div>
  @foreach($recentUsers as $u)
  <div class="sad-row">
    <div class="sad-av">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
    <div style="min-width:0">
      <div class="sad-name">{{ $u->name }}</div>
      <div class="sad-mail">{{ $u->email }}</div>
    </div>
    <span class="sad-role">{{ $u->roles->first()?->name ?? $u->type }}</span>
  </div>
  @endforeach
</div>
@endsection
