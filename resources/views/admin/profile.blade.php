@extends('layouts.dashboard')
@section('title', 'Mon Profil — ' . site_name())
@section('page_title', 'Mon Profil')

@section('content')
@push('styles')
<style>
[x-cloak]{display:none !important}
.ap{max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:1.25rem}
.ap-id{display:flex;align-items:center;gap:1.4rem;padding:1.6rem 1.75rem}
.ap-av{width:84px;height:84px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--c-navy),var(--c-navy-3));display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:var(--c-accent)}
.ap-name{font-size:1.2rem;font-weight:800;color:var(--c-navy)}
.ap-role{display:inline-block;padding:.2rem .65rem;border-radius:999px;background:var(--c-amber-l);color:var(--c-amber);font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin:.4rem .3rem 0 0}
.ap-sec{display:flex;flex-direction:column}
.ap-row{display:flex;align-items:center;gap:1rem;padding:1rem 1.75rem;border-top:1px solid var(--c-border)}
.ap-row:first-child{border-top:0}
.ap-row__ico{width:38px;height:38px;border-radius:11px;background:var(--c-bg);color:var(--c-navy);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ap-row__k{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700}
.ap-row__v{font-size:.9rem;font-weight:600;color:var(--c-text);margin-top:.1rem;word-break:break-word}
.ap-row__b{flex:1;min-width:0}
.ap-actions{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.ap-act{display:flex;align-items:center;gap:.9rem;padding:1.1rem 1.25rem;border:1px solid var(--c-border);border-radius:14px;background:var(--c-white,#fff);cursor:pointer;text-align:left;font-family:inherit;transition:.15s}
.ap-act:hover{border-color:var(--c-accent);box-shadow:0 4px 14px rgba(15,13,54,.08)}
.ap-act__ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ap-act__t{font-size:.875rem;font-weight:700;color:var(--c-navy)}
.ap-act__s{font-size:.72rem;color:var(--c-muted);margin-top:.1rem}
@media (max-width:640px){.ap-id{flex-direction:column;text-align:center}.ap-actions{grid-template-columns:1fr}}

.am{position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.am__bg{position:absolute;inset:0;background:rgba(15,13,54,.55);backdrop-filter:blur(2px)}
.am__box{position:relative;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;background:var(--c-white,#fff);border-radius:18px;box-shadow:0 24px 60px rgba(15,13,54,.3);padding:1.6rem 1.7rem 1.4rem}
.am__x{position:absolute;top:.9rem;right:.9rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--c-bg);color:var(--c-muted);cursor:pointer}
.am__title{font-size:1.0625rem;font-weight:800;color:var(--c-navy);margin:0 0 .25rem}
.am__sub{font-size:.8rem;color:var(--c-muted);margin:0 0 1.2rem}
.am__f{margin-bottom:1rem}
.am__f label{display:block;font-size:.75rem;font-weight:600;color:var(--c-muted);margin-bottom:.375rem}
.am__f input{width:100%;padding:.65rem .875rem;border-radius:var(--radius-sm);border:1.5px solid var(--c-border);background:var(--c-bg);color:var(--c-text);font-size:.8375rem;outline:none;transition:.15s}
.am__f input:focus{border-color:var(--c-accent)}
.am__f.err input{border-color:var(--c-red)}
.am__e{font-size:.72rem;color:var(--c-red);margin-top:.3rem}
.am__h{font-size:.7rem;color:var(--c-muted);margin-top:.35rem}
.am__foot{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.2rem}
</style>
@endpush

@php
  $openModal = ($errors->has('current_password') || $errors->has('password')) ? 'password'
             : (($errors->has('name') || $errors->has('email') || $errors->has('phone')) ? 'info' : 'null');
  $openModal = $openModal === 'null' ? 'null' : "'$openModal'";
@endphp

<div class="ap" x-data="{ m: {{ $openModal }} }" @keydown.escape.window="m = null">

  {{-- Identité --}}
  <div class="card-pro ap-id">
    <div class="ap-av">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
    <div>
      <div class="ap-name">{{ $user->name }}</div>
      <div>@foreach($user->getRoleNames() as $role)<span class="ap-role">{{ $role }}</span>@endforeach</div>
    </div>
  </div>

  {{-- Coordonnées --}}
  <div class="card-pro ap-sec">
    <div class="ap-row">
      <div class="ap-row__ico"><i class="fas fa-envelope"></i></div>
      <div class="ap-row__b"><div class="ap-row__k">Adresse e-mail</div><div class="ap-row__v">{{ $user->email }}</div></div>
    </div>
    <div class="ap-row">
      <div class="ap-row__ico"><i class="fab fa-whatsapp" style="color:#25D366"></i></div>
      <div class="ap-row__b"><div class="ap-row__k">Téléphone (WhatsApp)</div><div class="ap-row__v">{{ $user->phone ?: 'Non renseigné' }}</div></div>
    </div>
  </div>

  {{-- Actions --}}
  <div class="ap-actions">
    <button type="button" class="ap-act" @click="m = 'info'">
      <span class="ap-act__ico" style="background:#DBEAFE;color:var(--c-blue)"><i class="fas fa-user-edit"></i></span>
      <span><div class="ap-act__t">Modifier mes informations</div><div class="ap-act__s">Nom, e-mail, téléphone</div></span>
    </button>
    <button type="button" class="ap-act" @click="m = 'password'">
      <span class="ap-act__ico" style="background:#FEE2E2;color:var(--c-red)"><i class="fas fa-lock"></i></span>
      <span><div class="ap-act__t">Changer le mot de passe</div><div class="ap-act__s">Sécurisez votre accès</div></span>
    </button>
  </div>

  {{-- Modale : informations --}}
  <div class="am" x-show="m === 'info'" x-cloak x-transition.opacity>
    <div class="am__bg" @click="m = null"></div>
    <div class="am__box" @click.stop>
      <button type="button" class="am__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <h3 class="am__title">Mes informations</h3>
      <p class="am__sub">Ces informations sont visibles par vos clients.</p>
      <form data-confirm="Enregistrer les modifications de votre profil ?" data-confirm-title="Profil" data-confirm-ok="Enregistrer" method="POST" action="{{ route('admin.profile.update') }}">
        @csrf
        <div class="am__f @error('name') err @enderror">
          <label>Nom complet</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}">
          @error('name')<div class="am__e">{{ $message }}</div>@enderror
        </div>
        <div class="am__f @error('email') err @enderror">
          <label>Adresse e-mail</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}">
          @error('email')<div class="am__e">{{ $message }}</div>@enderror
        </div>
        <div class="am__f @error('phone') err @enderror">
          <label>Numéro de téléphone <span style="font-weight:400">(WhatsApp)</span></label>
          <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+33 6 00 00 00 00">
          <div class="am__h">Ce numéro sera visible par vos clients pour vous contacter sur WhatsApp.</div>
          @error('phone')<div class="am__e">{{ $message }}</div>@enderror
        </div>
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modale : mot de passe --}}
  <div class="am" x-show="m === 'password'" x-cloak x-transition.opacity>
    <div class="am__bg" @click="m = null"></div>
    <div class="am__box" @click.stop>
      <button type="button" class="am__x" @click="m = null" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <h3 class="am__title">Changer le mot de passe</h3>
      <p class="am__sub">Choisissez un mot de passe d’au moins 8 caractères.</p>
      <form data-confirm="Changer votre mot de passe ?" data-confirm-title="Mot de passe" data-confirm-ok="Changer" method="POST" action="{{ route('admin.profile.password') }}">
        @csrf
        <div class="am__f @error('current_password') err @enderror">
          <label>Mot de passe actuel</label>
          <input type="password" name="current_password" autocomplete="current-password">
          @error('current_password')<div class="am__e">{{ $message }}</div>@enderror
        </div>
        <div class="am__f @error('password') err @enderror">
          <label>Nouveau mot de passe</label>
          <input type="password" name="password" autocomplete="new-password">
          @error('password')<div class="am__e">{{ $message }}</div>@enderror
        </div>
        <div class="am__f">
          <label>Confirmer le mot de passe</label>
          <input type="password" name="password_confirmation" autocomplete="new-password">
        </div>
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="m = null">Annuler</button>
          <button type="submit" class="btn-navy btn-sm-pro" style="background:var(--c-red)"><i class="fas fa-lock"></i> Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
