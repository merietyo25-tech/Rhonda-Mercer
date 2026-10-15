@extends('layouts.dashboard')
@section('title', 'Rôles & Permissions')
@section('page_title', 'Rôles & Permissions')

@push('styles')
<style>
/* ── Role cards ──────────────────────────────────── */
.role-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.75rem; }
@media(max-width:991px) { .role-cards { grid-template-columns:repeat(2,1fr); } }
@media(max-width:575px) { .role-cards { grid-template-columns:1fr; } }

.role-card {
  background:var(--c-surface); border-radius:var(--radius); border:1px solid var(--c-border);
  padding:1.5rem; box-shadow:var(--shadow-sm); position:relative; overflow:hidden;
  transition:var(--transition);
}
.role-card:hover { box-shadow:var(--shadow); transform:translateY(-1px); }
.role-card__top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1rem; }
.role-card__icon {
  width:50px; height:50px; border-radius:12px;
  display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;
}
.role-card__badge {
  font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em;
  padding:.18rem .5rem; border-radius:999px;
}
.role-card__count { font-size:2rem; font-weight:800; color:var(--c-navy); line-height:1; margin-bottom:.2rem; }
.role-card__name  { font-size:.9rem; font-weight:700; color:var(--c-navy); margin-bottom:.35rem; }
.role-card__desc  { font-size:.72rem; color:var(--c-muted); line-height:1.5; }
.role-card__accent {
  position:absolute; bottom:-30px; right:-30px;
  width:100px; height:100px; border-radius:50%; opacity:.05;
}

/* ── Permission matrix ───────────────────────────── */
.perm-matrix { width:100%; border-collapse:collapse; }
.perm-matrix thead th {
  padding:.875rem 1rem; font-size:.7rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.07em; color:var(--c-muted); background:#FAFBFC;
  border-bottom:2px solid var(--c-border); text-align:center; white-space:nowrap;
}
.perm-matrix thead th:first-child { text-align:left; }
.perm-matrix tbody td {
  padding:.8rem 1rem; font-size:.8125rem; border-bottom:1px solid #F3F4F6;
  vertical-align:middle; text-align:center;
}
.perm-matrix tbody td:first-child { text-align:left; }
.perm-matrix tbody tr:last-child td { border-bottom:none; }
.perm-matrix tbody tr:hover td { background:#F8FAFF; }
.perm-matrix .perm-section-row td {
  background:var(--c-bg); font-size:.62rem; font-weight:700;
  text-transform:uppercase; letter-spacing:.09em; color:var(--c-muted);
  padding:.45rem 1rem;
}
.perm-feature { font-weight:500; color:var(--c-text); }
.perm-feature small { display:block; font-size:.68rem; color:var(--c-muted); margin-top:.1rem; }
.perm-check  { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:6px; font-size:.65rem; }
.perm-yes { background:var(--c-green-l);  color:var(--c-green); }
.perm-no  { background:#F3F4F6;            color:#CBD5E1; }
.perm-col-head {
  display:flex; flex-direction:column; align-items:center; gap:.3rem;
}
.perm-col-icon {
  width:32px; height:32px; border-radius:8px;
  display:flex; align-items:center; justify-content:center; font-size:.8rem;
}

/* ── Role user form ──────────────────────────────── */
.role-select-wrap { display:flex; gap:.5rem; align-items:center; }
.role-select {
  padding:.35rem .65rem; border:1.5px solid var(--c-border);
  border-radius:var(--radius-sm); font-size:.78rem; font-family:inherit;
  color:var(--c-text); background:var(--c-bg); cursor:pointer;
  transition:var(--transition); appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236B7280'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right .5rem center; padding-right:1.5rem;
}
.role-select:focus { outline:none; border-color:var(--c-accent); }

/* ── User avatar ─────────────────────────────────── */
.u-avatar {
  width:34px; height:34px; border-radius:50%;
  display:inline-flex; align-items:center; justify-content:center;
  font-weight:800; font-size:.75rem; flex-shrink:0;
}

/* ── Pagination ──────────────────────────────────── */
.pagination { gap:.25rem; }
.page-item .page-link {
  border-radius:var(--radius-sm)!important; font-size:.78rem; padding:.35rem .65rem;
  border-color:var(--c-border); color:var(--c-text);
}
.page-item.active .page-link { background:var(--c-navy); border-color:var(--c-navy); color:#fff; }

/* ── Scroll wrapper matrice ──────────────────────── */
.perm-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 0 0 var(--radius) var(--radius);
}
.perm-scroll::-webkit-scrollbar { height: 4px; }
.perm-scroll::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 99px; }

/* ── En-tête repliable (matrice) ─────────────────── */
.card-pro-hdr--toggle { cursor:pointer; user-select:none; }
.card-pro-hdr--toggle .toggle-label { display:flex; align-items:center; gap:.4rem; }
.card-pro-hdr--toggle .toggle-label i { font-size:.65rem; transition:transform .18s ease; }
.card-pro-hdr--toggle.is-open .toggle-label i { transform:rotate(180deg); }

/* ─────────────────────────────────────────
   RESPONSIVE MOBILE — roles
   ─────────────────────────────────────────*/
@media(max-width:991px) {
  .role-cards { grid-template-columns: repeat(2,1fr); }
}
@media(max-width:768px) {
  .role-select { min-height: 40px; font-size: .85rem; padding: .5rem .75rem; }
}
@media(max-width:640px) {
  .role-cards { grid-template-columns: 1fr; }
  /* Matrice : min-width pour forcer le scroll horizontal */
  .perm-matrix { min-width: 480px; }
  .perm-matrix thead th { padding: .5rem .75rem; font-size: .6rem; }
  .perm-matrix tbody td { padding: .5rem .75rem; }
  /* Colonne feature : réduite */
  .perm-matrix thead th:first-child { min-width: 160px; }
  /* Cartes de rôle */
  .role-card { padding: 1.125rem; }
  .role-card__count { font-size: 1.5rem; }
  /* Sélect rôle : pleine largeur dans la cellule */
  .role-select-wrap { flex-direction: column; align-items: stretch; }
  .role-select { width: 100%; }
}
@media(max-width:400px) {
  .perm-check { width: 20px; height: 20px; font-size: .55rem; }
}
</style>
@endpush

@section('content')

{{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
<div class="page-hdr d-flex align-items-start justify-content-between flex-wrap gap-3">
  <div>
    <h4>Rôles &amp; Permissions</h4>
    <p>Gérez les niveaux d'accès et attribuez les rôles aux utilisateurs.</p>
  </div>
  <span class="badge-status bs-violet" style="font-size:.72rem;padding:.4rem .85rem">
    <i class="fas fa-shield-alt" style="font-size:.6rem"></i>
    {{ $roles->sum('users_count') }} utilisateurs gérés
  </span>
</div>

{{-- ── ROLE CARDS ───────────────────────────────────────────────── --}}
@php
$roleConf = [
  'super-admin' => [
    'icon'   => 'fa-crown',
    'color'  => 'var(--c-navy)',
    'bg'     => '#EEF2FF',
    'badge'  => 'bs-dark',
    'name'   => 'Super Administrateur',
    'desc'   => 'Contrôle total du système. Gestion des rôles, accès à toutes les données et configurations.',
    'accent' => 'var(--c-navy)',
  ],
  'admin' => [
    'icon'   => 'fa-shield-alt',
    'color'  => 'var(--c-accent-d)',
    'bg'     => '#FEF9EC',
    'badge'  => 'bs-amber',
    'name'   => 'Administrateur',
    'desc'   => 'Gestion des clients, des vérifications d\'identité, des comptes et des virements.',
    'accent' => 'var(--c-accent)',
  ],
  'client' => [
    'icon'   => 'fa-user',
    'color'  => 'var(--c-blue)',
    'bg'     => 'var(--c-blue-l)',
    'badge'  => 'bs-blue',
    'name'   => 'Client',
    'desc'   => 'Espace personnel : compte, cartes, IBAN, virements et factures.',
    'accent' => 'var(--c-blue)',
  ],
];
@endphp

<div class="role-cards">
  @foreach($roles as $role)
  @php $cfg = $roleConf[$role->name] ?? ['icon'=>'fa-user','color'=>'var(--c-muted)','bg'=>'#F3F4F6','badge'=>'bs-gray','name'=>ucfirst($role->name),'desc'=>'Rôle personnalisé','accent'=>'#6B7280']; @endphp
  <div class="role-card">
    <div class="role-card__top">
      <div class="role-card__icon" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }}">
        <i class="fas {{ $cfg['icon'] }}"></i>
      </div>
      <span class="badge-status {{ $cfg['badge'] }}">{{ $role->users_count }} {{ $role->users_count > 1 ? 'utilisateurs' : 'utilisateur' }}</span>
    </div>
    <div class="role-card__count">{{ $role->users_count }}</div>
    <div class="role-card__name">{{ $cfg['name'] }}</div>
    <div class="role-card__desc">{{ $cfg['desc'] }}</div>
    <div class="role-card__accent" style="background:{{ $cfg['accent'] }}"></div>
  </div>
  @endforeach
</div>

{{-- ── PERMISSION MATRIX ────────────────────────────────────────── --}}
<div class="card-pro mb-4">
  <div class="card-pro-hdr card-pro-hdr--toggle" id="matrixHdr" onclick="toggleMatrix()">
    <div class="card-pro-title">
      <span class="icon-dot"></span>Matrice des permissions
    </div>
    <span class="toggle-label" style="font-size:.7rem;color:var(--c-muted)">
      Détail par rôle <i class="fas fa-chevron-down"></i>
    </span>
  </div>
  <div class="perm-scroll" id="matrixBody" style="display:none">
    <table class="perm-matrix">
      <thead>
        <tr>
          <th style="width:55%;text-align:left">Fonctionnalité</th>
          <th>
            <div class="perm-col-head">
              <div class="perm-col-icon" style="background:#EEF2FF;color:var(--c-navy)"><i class="fas fa-user"></i></div>
              <span>Client</span>
            </div>
          </th>
          <th>
            <div class="perm-col-head">
              <div class="perm-col-icon" style="background:#FEF9EC;color:var(--c-accent-d)"><i class="fas fa-shield-alt"></i></div>
              <span>Admin</span>
            </div>
          </th>
          <th>
            <div class="perm-col-head">
              <div class="perm-col-icon" style="background:#EEF2FF;color:var(--c-navy)"><i class="fas fa-crown"></i></div>
              <span>Super Admin</span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="perm-section-row">
          <td colspan="4">Espace personnel</td>
        </tr>
        @php
        $matrix = [
          // [label, sub, client, admin, super]
          ['Tableau de bord personnel',         'Accès au dashboard',                        1, 1, 1],
          ['Consulter ses cartes et son IBAN',    'IBAN, BIC et cartes du compte',              1, 1, 1],
          ['Effectuer des virements',            'Après vérification d\'identité',             1, 1, 1],
          ['__section' => 'Gestion des clients'],
          ['Vérifier les identités (KYC)',       'Approuver, rejeter ou valider un compte',    0, 1, 1],
          ['Attribuer IBAN et carte',            'Génération automatique ou manuelle',         0, 1, 1],
          ['Valider les virements',              'Approuver ou rejeter les demandes',          0, 1, 1],
          ['__section' => 'Administration'],
          ['Gérer les utilisateurs',             'Créer, inviter et modifier les comptes',     0, 1, 1],
          ['Voir le tableau super admin',        'Vue d\'ensemble globale du système',         0, 0, 1],
          ['Attribuer les rôles',                'Modifier le niveau d\'accès d\'un compte',   0, 0, 1],
          ['Accéder à toutes les données',       'Toutes fonctionnalités sans restriction',    0, 0, 1],
        ];
        @endphp
        @foreach($matrix as $row)
          @if(isset($row['__section']))
          <tr class="perm-section-row">
            <td colspan="4">{{ $row['__section'] }}</td>
          </tr>
          @else
          <tr>
            <td>
              <div class="perm-feature">
                {{ $row[0] }}
                <small>{{ $row[1] }}</small>
              </div>
            </td>
            <td><span class="perm-check {{ $row[2] ? 'perm-yes' : 'perm-no' }}"><i class="fas fa-{{ $row[2] ? 'check' : 'times' }}"></i></span></td>
            <td><span class="perm-check {{ $row[3] ? 'perm-yes' : 'perm-no' }}"><i class="fas fa-{{ $row[3] ? 'check' : 'times' }}"></i></span></td>
            <td><span class="perm-check {{ $row[4] ? 'perm-yes' : 'perm-no' }}"><i class="fas fa-{{ $row[4] ? 'check' : 'times' }}"></i></span></td>
          </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── ATTRIBUTION DES RÔLES ────────────────────────────────────── --}}
<div class="card-pro">
  <div class="card-pro-hdr">
    <div class="card-pro-title">
      <span class="icon-dot"></span>Attribution des rôles
    </div>
    <div style="display:flex;align-items:center;gap:.75rem">
      <span style="font-size:.72rem;color:var(--c-muted)">{{ $users->total() }} utilisateurs</span>
    </div>
  </div>

  {{-- Search bar --}}
  <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--c-border);background:var(--c-bg)">
    <div class="filter-bar" style="margin:0;padding:0;border:none;background:transparent;gap:.625rem">
      <input type="text" placeholder="Rechercher un utilisateur…" id="userSearch"
             style="flex:1;min-width:180px"
             oninput="filterTable(this.value)">
      <select id="roleFilter" style="min-width:140px" onchange="filterTable(document.getElementById('userSearch').value)">
        <option value="">Tous les rôles</option>
        @foreach($roles as $role)
        <option value="{{ $role->name }}">{{ ucfirst(str_replace('-',' ',$role->name)) }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="table-responsive-pro">
    <table class="pro-table" id="usersTable">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Type</th>
          <th>Rôle actuel</th>
          <th>Changer le rôle</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
        @php
          $rn = $u->getRoleNames()->first() ?? '';
          $avatarColors = ['#2563EB','#059669','#D97706','#7C3AED','#DC2626','#0D9488','#008457','#312B81'];
          $avatarBg = $avatarColors[crc32($u->email) % count($avatarColors)];
          $roleBadge = ['super-admin'=>'bs-dark','admin'=>'bs-amber','client'=>'bs-blue'][$rn] ?? 'bs-gray';
        @endphp
        <tr data-role="{{ $rn }}" data-name="{{ strtolower($u->name) }} {{ strtolower($u->email) }}">
          <td data-label="Utilisateur">
            <div style="display:flex;align-items:center;gap:.75rem">
              <div class="u-avatar" style="background:{{ $avatarBg }}22;color:{{ $avatarBg }}">
                {{ strtoupper(mb_substr($u->name, 0, 1)) }}
              </div>
              <div>
                <div class="cell-name">{{ $u->name }}</div>
                <div class="cell-sub">{{ $u->email }}</div>
              </div>
            </div>
          </td>
          <td data-label="Type">
            <span class="badge-status {{ $u->type === 'staff' ? 'bs-violet' : 'bs-blue' }}">
              {{ $u->type === 'staff' ? 'Personnel' : 'Client' }}
            </span>
          </td>
          <td data-label="Rôle actuel">
            @if($rn)
            <span class="badge-status {{ $roleBadge }}">
              {{ ucfirst(str_replace('-',' ',$rn)) }}
            </span>
            @else
            <span class="badge-status bs-gray">Aucun</span>
            @endif
          </td>
          <td data-label="Changer le rôle">
            <form action="{{ route('super-admin.users.role', $u) }}" method="POST"
                  class="role-select-wrap" id="form-{{ $u->id }}">
              @csrf
              <select name="role" class="role-select" onchange="this.form.submit()">
                @foreach($roles as $role)
                <option value="{{ $role->name }}"
                  {{ $u->hasRole($role->name) ? 'selected' : '' }}>
                  {{ ucfirst(str_replace('-',' ',$role->name)) }}
                </option>
                @endforeach
              </select>
            </form>
          </td>
          <td data-label="">
            <a href="{{ route('admin.users') }}" class="btn-icon" title="Voir le profil">
              <i class="fas fa-external-link-alt"></i>
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:2.5rem;color:var(--c-muted)">
            <i class="fas fa-users" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
            Aucun utilisateur trouvé
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($users->hasPages())
  <div style="padding:.875rem 1.25rem;border-top:1px solid var(--c-border);display:flex;justify-content:flex-end">
    {{ $users->links('partials.pagination') }}
  </div>
  @endif
</div>

{{-- ── PERMISSIONS EXCEPTIONNELLES ──────────────────────────────── --}}
<div class="card-pro mt-4">
  <div class="card-pro-hdr">
    <div class="card-pro-title">
      <span class="icon-dot"></span>Permissions exceptionnelles
    </div>
    <span style="font-size:.72rem;color:var(--c-muted)">
      Donnez à un admin classique l'accès à une zone habituellement réservée au super-admin, sans changer son rôle.
    </span>
  </div>

  @if($adminUsers->isEmpty())
  <div style="padding:2.5rem;text-align:center;color:var(--c-muted)">
    <i class="fas fa-user-shield" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
    Aucun admin classique pour le moment.
  </div>
  @else
  <div class="table-responsive-pro">
    <table class="pro-table">
      <thead>
        <tr>
          <th>Admin</th>
          @foreach($exceptionalPerms as $label)
          <th style="text-align:center">{{ $label }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($adminUsers as $u)
        @php $userPerms = $u->getPermissionNames(); @endphp
        <tr>
          <td data-label="Admin">
            <div style="display:flex;align-items:center;gap:.75rem">
              <div class="u-avatar" style="background:#00845722;color:#008457">
                {{ strtoupper(mb_substr($u->name, 0, 1)) }}
              </div>
              <div>
                <div class="cell-name">{{ $u->name }}</div>
                <div class="cell-sub">{{ $u->email }}</div>
              </div>
            </div>
          </td>
          @foreach($exceptionalPerms as $permKey => $permLabel)
          <td style="text-align:center" data-label="{{ $permLabel }}">
            <input type="checkbox" form="perm-form-{{ $u->id }}" name="permissions[]" value="{{ $permKey }}"
                   {{ $userPerms->contains($permKey) ? 'checked' : '' }}
                   onchange="this.form.requestSubmit()"
                   style="width:16px;height:16px;accent-color:var(--c-navy);cursor:pointer">
          </td>
          @endforeach
          <td style="display:none">
            <form action="{{ route('super-admin.users.permissions', $u) }}" method="POST" id="perm-form-{{ $u->id }}">
              @csrf
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

@endsection

@push('scripts')
<script>
function toggleMatrix() {
  const body = document.getElementById('matrixBody');
  const hdr  = document.getElementById('matrixHdr');
  const open = body.style.display !== 'none';
  body.style.display = open ? 'none' : '';
  hdr.classList.toggle('is-open', !open);
}

function filterTable(query) {
  const q    = query.toLowerCase().trim();
  const role = document.getElementById('roleFilter').value;
  document.querySelectorAll('#usersTable tbody tr[data-name]').forEach(row => {
    const matchQ    = !q    || row.dataset.name.includes(q);
    const matchRole = !role || row.dataset.role === role;
    row.style.display = (matchQ && matchRole) ? '' : 'none';
  });
}
</script>
@endpush
