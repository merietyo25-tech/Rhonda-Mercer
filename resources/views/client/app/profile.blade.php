@extends('layouts.client-app')
@section('title', __('app.profile_title') . ' — ' . site_name())
@section('page_title', __('app.profile_title'))
@section('back_btn', true)
@section('back_url', route('client.app.home'))

@section('main_class', 'ca-main--narrow')
@section('content')

@push('styles')
<style>
[x-cloak]{display:none !important}
.pf{padding:1rem 1.25rem 0;display:flex;flex-direction:column;gap:1.25rem}

/* Carte identité */
.pf-id{display:flex;align-items:center;gap:1rem;background:var(--ca-bg3);border:1px solid var(--ca-border);border-radius:20px;padding:1.1rem 1.25rem}
.pf-id__av{width:64px;height:64px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,rgba(0,132,87,.45),rgba(0,132,87,.15));border:2px solid rgba(0,132,87,.4);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:var(--ca-teal-l)}
.pf-id__body{flex:1;min-width:0}
.pf-id__name{font-size:1.05rem;font-weight:800;color:var(--ca-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pf-id__mail{font-size:.75rem;color:var(--ca-text-3);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pf-id__tags{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.55rem}
.pf-tag{display:inline-flex;align-items:center;gap:.3rem;font-size:.65rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;background:rgba(63,203,146,.12);border:1px solid rgba(63,203,146,.25);color:var(--ca-accent-l)}
.pf-tag--soft{background:var(--ca-bg4);border-color:var(--ca-border);color:var(--ca-text-3);font-weight:600}

/* Solde */
.pf-bal{display:flex;align-items:center;justify-content:space-between;text-decoration:none;background:linear-gradient(135deg,#3A3590,#231F5F);border-radius:18px;padding:1rem 1.25rem;border:1px solid rgba(255,255,255,.08)}
.pf-bal__lbl{font-size:.65rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em}
.pf-bal__val{font-family:'Space Grotesk',sans-serif;font-size:1.45rem;font-weight:800;color:#fff;margin-top:.2rem}
.pf-bal__go{font-size:.75rem;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:.35rem}

/* Groupes */
.pf-group{display:flex;flex-direction:column;gap:.55rem}
.pf-group__t{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--ca-text-3);padding-left:.3rem}
.pf-list{background:var(--ca-bg3);border:1px solid var(--ca-border);border-radius:18px;overflow:hidden}
.pf-item{display:flex;align-items:center;gap:.9rem;width:100%;padding:.9rem 1.1rem;background:none;border:0;border-bottom:1px solid var(--ca-border-2);text-decoration:none;color:inherit;font-family:inherit;text-align:left;cursor:pointer;transition:.15s}
.pf-item:last-child{border-bottom:0}
.pf-item:hover{background:var(--ca-bg4)}
.pf-item__ico{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.pf-item__txt{flex:1;min-width:0}
.pf-item__l{font-size:.875rem;font-weight:600;color:var(--ca-text);display:flex;align-items:center;gap:.5rem}
.pf-item__s{font-size:.72rem;color:var(--ca-text-3);margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pf-item__r{color:var(--ca-text-3);font-size:.75rem;flex-shrink:0}
.pf-badge{min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--ca-purple);color:#fff;font-size:.6rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center}

/* Conseiller */
.pf-adv{display:flex;align-items:center;gap:.9rem;background:var(--ca-bg3);border:1px solid var(--ca-border);border-radius:18px;padding:1rem 1.1rem}
.pf-adv__av{width:46px;height:46px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,rgba(0,132,87,.35),rgba(0,132,87,.12));border:1.5px solid rgba(0,132,87,.3);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--ca-teal-l)}
.pf-adv__wa{width:42px;height:42px;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;text-decoration:none;flex-shrink:0}

.pf-logout{color:var(--ca-negative)}
.pf-ver{text-align:center;padding:.25rem 0 0;font-size:.7rem;color:var(--ca-text-3)}

/* Modales */
.pf-modal{position:fixed;inset:0;z-index:20000;display:flex;align-items:flex-end;justify-content:center;padding:1rem}
.pf-modal__bg{position:absolute;inset:0;background:rgba(2,10,20,.66);backdrop-filter:blur(3px)}
.pf-modal__box{position:relative;width:100%;max-width:420px;background:var(--ca-bg2);color:var(--ca-text);border:1px solid var(--ca-border);border-radius:22px;padding:1.4rem 1.4rem 1.25rem;box-shadow:0 24px 60px rgba(0,0,0,.5);max-height:88vh;overflow-y:auto}
@media (min-width:768px){.pf-modal{align-items:center}}
.pf-modal__x{position:absolute;top:.8rem;right:.8rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--ca-bg4);color:var(--ca-text-2);cursor:pointer}
.pf-modal__title{font-size:1rem;font-weight:800;margin-bottom:.9rem;padding-right:2rem}
.pf-kv{display:flex;justify-content:space-between;gap:1rem;padding:.7rem 0;border-top:1px solid var(--ca-border-2);font-size:.8rem}
.pf-kv__k{color:var(--ca-text-3);flex-shrink:0}
.pf-kv__v{color:var(--ca-text);font-weight:600;text-align:right;word-break:break-word}
.pf-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;margin-top:1rem;padding:.85rem;border-radius:14px;border:0;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;text-decoration:none;background:linear-gradient(145deg,#D9F2E7,#3FCB92);color:#1a2b3c}
.pf-lang{display:flex;align-items:center;justify-content:space-between;width:100%;padding:.85rem 1rem;margin-bottom:.45rem;border-radius:14px;border:1px solid var(--ca-border);background:var(--ca-bg3);color:var(--ca-text-2);font-family:inherit;font-size:.875rem;cursor:pointer}
.pf-lang.is-on{border-color:var(--ca-accent-l);color:var(--ca-text);font-weight:700;background:var(--ca-bg4)}
.pf-lang.is-on i{color:var(--ca-teal-l)}
</style>
@endpush

@php
  $currency  = $user->currency ?? \App\Models\Currency::default();
  $supportUnread = \App\Models\SupportMessage::where('client_id', Auth::id())->where('sender_type','admin')->whereNull('read_at')->count();
  $langs     = \App\Models\Language::enabledList();
  $curLang   = $langs->firstWhere('code', app()->getLocale())->native_name ?? app()->getLocale();
  $advisor   = $user->created_by ? \App\Models\User::find($user->created_by) : null;
  $advisor ??= \App\Models\User::role('super-admin')->first();
@endphp

<div class="pf" x-data="{ modal: null }" @keydown.escape.window="modal = null">

  {{-- Identité --}}
  <div class="pf-id">
    <div class="pf-id__av">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
    <div class="pf-id__body">
      <div class="pf-id__name">{{ $user->name }}</div>
      <div class="pf-id__mail">{{ $user->email }}</div>
      <div class="pf-id__tags">
        <span class="pf-tag"><i class="fas fa-crown" style="font-size:.6rem"></i> {{ __('app.premium_member') }}</span>
        <span class="pf-tag pf-tag--soft">{{ __('app.member_since') }} {{ $user->created_at->format('m/Y') }}</span>
      </div>
    </div>
  </div>

  {{-- Solde --}}
  <a href="{{ route('client.app.movements') }}" class="pf-bal">
    <div>
      <div class="pf-bal__lbl">{{ __('app.balance') }}</div>
      <div class="pf-bal__val">{{ $currency }} {{ number_format((float)$user->balance, 2, ',', ' ') }}</div>
    </div>
    <div class="pf-bal__go">{{ __('app.movements_title') }} <i class="fas fa-chevron-right" style="font-size:.6rem"></i></div>
  </a>

  {{-- Compte --}}
  <div class="pf-group">
    <div class="pf-group__t">{{ __('app.account_settings') }}</div>
    <div class="pf-list">
      <button type="button" class="pf-item" @click="modal = 'info'">
        <span class="pf-item__ico" style="background:rgba(0,132,87,.18);color:var(--ca-teal-l)"><i class="fas fa-user"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.personal_info') }}</div><div class="pf-item__s">{{ $user->name }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </button>
      <a href="{{ route('client.app.profile.edit') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(0,132,87,.18);color:var(--ca-teal-l)"><i class="fas fa-pen"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.edit_profile') }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>
      <a href="{{ route('client.app.payment-methods') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(63,203,146,.18);color:var(--ca-accent-l)"><i class="fas fa-credit-card"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">IBAN</div><div class="pf-item__s">{{ $user->bankAccount ? $user->bankAccount->maskedIban() : __('app.not_configured') }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>
      <a href="{{ route('client.app.invoices') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fas fa-file-invoice"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.invoices_title') }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>
    </div>
  </div>

  {{-- Sécurité et préférences --}}
  <div class="pf-group">
    <div class="pf-group__t">{{ __('app.security') }}</div>
    <div class="pf-list">
      <a href="{{ route('client.app.profile.password') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(63,203,146,.18);color:var(--ca-accent-l)"><i class="fas fa-lock"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.change_password') }}</div><div class="pf-item__s">••••••••</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>

      <div class="pf-item" x-data="togglePref()" :style="blocked ? 'opacity:.45;pointer-events:none' : ''" style="cursor:default">
        <span class="pf-item__ico" style="background:rgba(74,158,255,.18);color:var(--ca-blue)">
          <i class="fas fa-bell" x-show="!loading"></i>
          <i class="fas fa-spinner fa-spin" x-show="loading" style="font-size:.85rem"></i>
        </span>
        <span class="pf-item__txt">
          <div class="pf-item__l">{{ __('app.notifications') }}</div>
          <div class="pf-item__s" x-show="blocked" style="color:var(--ca-negative)">Bloquées dans les paramètres du navigateur</div>
        </span>
        <label class="ca-toggle" @click.prevent="toggle()">
          <input type="checkbox" :checked="on" readonly tabindex="-1">
          <div class="ca-toggle__track"></div>
          <div class="ca-toggle__thumb"></div>
        </label>
      </div>

      <div class="pf-item" x-data="themeToggle()" style="cursor:default">
        <span class="pf-item__ico" style="background:rgba(139,92,246,.18);color:var(--ca-purple)">
          <i class="fas fa-moon" x-show="isDark"></i><i class="fas fa-sun" x-show="!isDark"></i>
        </span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.dark_mode') }}</div><div class="pf-item__s" x-text="isDark ? 'Dark' : 'Light'"></div></span>
        <label class="ca-toggle">
          <input type="checkbox" :checked="isDark" @change="toggle()">
          <div class="ca-toggle__track"></div>
          <div class="ca-toggle__thumb"></div>
        </label>
      </div>

      <button type="button" class="pf-item" @click="modal = 'lang'">
        <span class="pf-item__ico" style="background:rgba(245,158,11,.18);color:var(--ca-amber)"><i class="fas fa-globe"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.language_pref') }}</div><div class="pf-item__s">{{ $curLang }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </button>
    </div>
  </div>

  {{-- Aide --}}
  <div class="pf-group">
    <div class="pf-group__t">Support</div>
    <div class="pf-list">
      <a href="{{ route('client.app.notifications') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(74,158,255,.18);color:var(--ca-blue)"><i class="fas fa-inbox"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l">{{ __('app.notifications_title') }}</div></span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>
      <a href="{{ route('client.app.support') }}" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(139,92,246,.18);color:var(--ca-purple)"><i class="fas fa-headset"></i></span>
        <span class="pf-item__txt">
          <div class="pf-item__l">Support @if($supportUnread > 0)<span class="pf-badge">{{ $supportUnread }}</span>@endif</div>
          <div class="pf-item__s">Contacter votre conseiller</div>
        </span>
        <span class="pf-item__r"><i class="fas fa-chevron-right"></i></span>
      </a>
    </div>
  </div>

  @if($advisor)
  <div class="pf-adv">
    <div class="pf-adv__av">{{ mb_strtoupper(mb_substr($advisor->name, 0, 1)) }}</div>
    <div style="flex:1;min-width:0">
      <div style="font-size:.875rem;font-weight:700;color:var(--ca-text)">{{ $advisor->name }}</div>
      <div style="font-size:.7rem;color:var(--ca-text-3);margin-top:.1rem">Votre conseiller {{ site_name() }}</div>
    </div>
    @if($advisor->phone)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $advisor->phone) }}" target="_blank" rel="noopener" class="pf-adv__wa" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
    @endif
  </div>
  @endif

  {{-- Déconnexion --}}
  <div class="pf-list">
    <form data-confirm="{{ __('app.confirm_logout') }}" data-confirm-title="{{ __('app.logout') }}" data-confirm-danger="1" method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="pf-item">
        <span class="pf-item__ico" style="background:rgba(255,90,90,.15);color:var(--ca-negative)"><i class="fas fa-sign-out-alt"></i></span>
        <span class="pf-item__txt"><div class="pf-item__l pf-logout">{{ __('app.logout') }}</div></span>
      </button>
    </form>
  </div>

  <div class="pf-ver">{{ site_name() }} Mobile &nbsp;&bull;&nbsp; v2.0.0</div>

  {{-- Modale : informations personnelles --}}
  <div class="pf-modal" x-show="modal === 'info'" x-cloak x-transition.opacity>
    <div class="pf-modal__bg" @click="modal = null"></div>
    <div class="pf-modal__box" @click.stop>
      <button type="button" class="pf-modal__x" @click="modal = null" aria-label="×"><i class="fas fa-xmark"></i></button>
      <div class="pf-modal__title">{{ __('app.personal_info') }}</div>
      <div class="pf-kv"><span class="pf-kv__k">{{ __('app.full_name') }}</span><span class="pf-kv__v">{{ $user->name }}</span></div>
      <div class="pf-kv"><span class="pf-kv__k">Email</span><span class="pf-kv__v">{{ $user->email }}</span></div>
      @if($user->phone)<div class="pf-kv"><span class="pf-kv__k">Tel.</span><span class="pf-kv__v">{{ $user->phone }}</span></div>@endif
      @if($user->address || $user->country)<div class="pf-kv"><span class="pf-kv__k">{{ __('app.address') }}</span><span class="pf-kv__v">{{ trim($user->address . ($user->country ? ', ' . $user->country : ''), ', ') }}</span></div>@endif
      <a href="{{ route('client.app.profile.edit') }}" class="pf-btn"><i class="fas fa-pen"></i> {{ __('app.edit_profile') }}</a>
    </div>
  </div>

  {{-- Modale : langue --}}
  <div class="pf-modal" x-show="modal === 'lang'" x-cloak x-transition.opacity>
    <div class="pf-modal__bg" @click="modal = null"></div>
    <div class="pf-modal__box" @click.stop>
      <button type="button" class="pf-modal__x" @click="modal = null" aria-label="×"><i class="fas fa-xmark"></i></button>
      <div class="pf-modal__title">{{ __('app.language_pref') }}</div>
      @foreach($langs as $lang)
      <form method="POST" action="{{ route('client.app.locale') }}">
        @csrf
        <input type="hidden" name="locale" value="{{ $lang->code }}">
        <button type="submit" class="pf-lang {{ app()->getLocale() === $lang->code ? 'is-on' : '' }}">
          {{ $lang->native_name }}
          @if(app()->getLocale() === $lang->code)<i class="fas fa-check"></i>@endif
        </button>
      </form>
      @endforeach
    </div>
  </div>
</div>

<div style="height:1.5rem"></div>
@endsection
