@extends('layouts.client-app')
@section('title', __('app.edit_profile') . ' — ' . site_name())
@section('page_title', __('app.edit_profile'))
@section('back_btn', true)
@section('back_url', route('client.app.profile'))

@push('styles')
<style>
.ep-body { padding:1.25rem 1.25rem 2rem }

/* Info row (lecture seule) */
.ep-info-row {
  display:flex;align-items:center;gap:.875rem;
  background:var(--ca-bg2);border:1px solid var(--ca-border);
  border-radius:var(--ca-radius-sm);padding:.875rem 1rem;
  margin-bottom:.625rem;
}
.ep-info-ico {
  width:38px;height:38px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:.85rem;
}
.ep-info-body { flex:1;min-width:0 }
.ep-info-lbl { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ca-text-3);margin-bottom:.15rem }
.ep-info-val { font-size:.92rem;font-weight:500;color:var(--ca-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }

/* Séparateur section */
.ep-sep {
  display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 1rem;
}
.ep-sep-line { flex:1;height:1px;background:var(--ca-border) }
.ep-sep-lbl { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--ca-text-3);white-space:nowrap }

/* Email champ */
.ep-field { margin-bottom:1rem }
.ep-label {
  display:block;font-size:.75rem;font-weight:700;
  color:var(--ca-text-2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.45rem;
}
.ep-input-wrap { position:relative }
.ep-input {
  width:100%;padding:.875rem 1rem .875rem 3rem;
  background:var(--ca-bg3);border:1.5px solid var(--ca-border);
  border-radius:var(--ca-radius-sm);
  color:var(--ca-text);font-family:inherit;font-size:.925rem;
  transition:border-color var(--ca-transition);
  -webkit-appearance:none;appearance:none;
}
.ep-input:focus {
  outline:none;border-color:var(--ca-teal-l);
  box-shadow:0 0 0 3px rgba(0,132,87,.15);
}
.ep-ico {
  position:absolute;left:1rem;top:50%;transform:translateY(-50%);
  color:var(--ca-text-3);font-size:.85rem;pointer-events:none;
}
.ep-hint { font-size:.72rem;color:var(--ca-text-3);margin-top:.4rem;line-height:1.45 }

.ep-btn {
  width:100%;padding:.9rem;margin-top:.25rem;
  background:linear-gradient(135deg,var(--ca-teal-l),var(--ca-teal));
  border:none;border-radius:var(--ca-radius-sm);
  color:#fff;font-size:.925rem;font-weight:700;
  cursor:pointer;font-family:inherit;
  transition:opacity .18s,transform .1s;
}
.ep-btn:active { transform:scale(.98) }

/* OTP panel */
.ep-otp-panel {
  background:rgba(0,132,87,.07);
  border:1.5px solid rgba(0,132,87,.3);
  border-radius:var(--ca-radius-md);
  padding:1.25rem;margin-bottom:1.5rem;
}
.ep-otp-panel__title {
  font-size:.9rem;font-weight:700;color:var(--ca-teal-l);
  display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem;
}
.ep-otp-panel__body { font-size:.8rem;color:var(--ca-text-2);margin-bottom:1rem;line-height:1.55 }
.ep-otp-input {
  letter-spacing:.45em;font-size:1.35rem;font-weight:800;
  text-align:center;font-family:'Space Grotesk',monospace;
}
[x-cloak] { display:none !important }
</style>
@endpush

@section('main_class', 'ca-main--narrow')
@section('content')
<div class="ep-body">

{{-- ── Panel OTP (quand changement email en attente) ── --}}
@if(session('otp_sent') || $pendingOtp)
<div class="ep-otp-panel">
  <div class="ep-otp-panel__title">
    <i class="fas fa-shield-halved"></i>
    {{ __('app.otp_confirm_change') }}
  </div>
  <div class="ep-otp-panel__body">{{ __('app.profile_otp_sent') }}</div>

  @error('otp')
  <div style="font-size:.8rem;color:var(--ca-negative);margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem">
    <i class="fas fa-triangle-exclamation"></i> {{ $message }}
  </div>
  @enderror

  <form method="POST" action="{{ route('client.app.profile.edit.otp') }}" x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    <div class="ep-field">
      <label class="ep-label">{{ __('auth.otp_heading') }}</label>
      <div class="ep-input-wrap">
        <i class="ep-ico fas fa-key"></i>
        <input type="text" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6"
               class="ep-input ep-otp-input" autocomplete="one-time-code"
               placeholder="000000" required>
      </div>
    </div>
    <button type="submit" class="ep-btn" :disabled="submitting">
      <i class="fas fa-spinner fa-spin" x-show="submitting" x-cloak style="margin-right:.5rem"></i>
      <i class="fas fa-check" x-show="!submitting" style="margin-right:.5rem"></i>
      <span x-text="submitting ? '{{ __('app.otp_confirming') }}' : '{{ __('app.otp_confirm_change') }}'"></span>
    </button>
  </form>
</div>
@endif

{{-- ── Informations du compte (lecture seule) ── --}}
<div class="ep-sep">
  <div class="ep-sep-line"></div>
  <span class="ep-sep-lbl">{{ __('app.personal_info') }}</span>
  <div class="ep-sep-line"></div>
</div>

<div class="ep-info-row">
  <div class="ep-info-ico" style="background:rgba(0,132,87,.15);color:var(--ca-teal-l)">
    <i class="fas fa-user"></i>
  </div>
  <div class="ep-info-body">
    <div class="ep-info-lbl">{{ __('app.full_name') }}</div>
    <div class="ep-info-val">{{ $user->name }}</div>
  </div>
  <i class="fas fa-lock" style="color:var(--ca-text-3);font-size:.75rem"></i>
</div>

<div class="ep-info-row">
  <div class="ep-info-ico" style="background:rgba(74,158,255,.15);color:var(--ca-blue)">
    <i class="fas fa-phone"></i>
  </div>
  <div class="ep-info-body">
    <div class="ep-info-lbl">{{ __('app.phone_number') }}</div>
    <div class="ep-info-val">{{ $user->phone ?? '—' }}</div>
  </div>
  <i class="fas fa-lock" style="color:var(--ca-text-3);font-size:.75rem"></i>
</div>

<div class="ep-info-row">
  <div class="ep-info-ico" style="background:rgba(139,92,246,.15);color:var(--ca-purple)">
    <i class="fas fa-location-dot"></i>
  </div>
  <div class="ep-info-body">
    <div class="ep-info-lbl">{{ __('app.address') }}</div>
    <div class="ep-info-val">{{ $user->address ?? '—' }}</div>
  </div>
  <i class="fas fa-lock" style="color:var(--ca-text-3);font-size:.75rem"></i>
</div>

{{-- ── Modifier l'adresse e-mail ── --}}
<div class="ep-sep" style="margin-top:1.75rem">
  <div class="ep-sep-line"></div>
  <span class="ep-sep-lbl">Modifier l'email</span>
  <div class="ep-sep-line"></div>
</div>

<form method="POST" action="{{ route('client.app.profile.save') }}" x-data="{ submitting: false }" @submit="submitting = true">
  @csrf
  {{-- Champs cachés pour passer les valeurs non-modifiables --}}
  <input type="hidden" name="name"    value="{{ $user->name }}">
  <input type="hidden" name="phone"   value="{{ $user->phone }}">
  <input type="hidden" name="address" value="{{ $user->address }}">

  <div class="ep-field">
    <label class="ep-label" for="ep-email">Email</label>
    <div class="ep-input-wrap">
      <i class="ep-ico fas fa-envelope"></i>
      <input type="email" id="ep-email" name="email"
             class="ep-input"
             style="@error('email') border-color:var(--ca-negative); @enderror"
             value="{{ old('email', $user->email) }}" required>
    </div>
    @error('email')
    <div class="ep-hint" style="color:var(--ca-negative)"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
    @enderror
    <div class="ep-hint">
      <i class="fas fa-shield-halved" style="color:var(--ca-teal-l)"></i>
      {{ __('app.enter_otp_to_confirm') }}
    </div>
  </div>

  <button type="submit" class="ep-btn" :disabled="submitting">
    <i class="fas fa-spinner fa-spin" x-show="submitting" x-cloak style="margin-right:.5rem"></i>
    <i class="fas fa-paper-plane" x-show="!submitting" style="margin-right:.5rem"></i>
    <span x-text="submitting ? '{{ __('app.saving') }}' : '{{ __('app.save_changes') }}'"></span>
  </button>
</form>

</div>
@endsection
