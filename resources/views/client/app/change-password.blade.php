@extends('layouts.client-app')
@section('title', __('app.change_password') . ' — ' . site_name())
@section('page_title', __('app.change_password'))
@section('back_btn', true)
@section('back_url', route('client.app.profile'))

@push('styles')
<style>
.cp-body { padding:1.25rem }

.cp-field { margin-bottom:1rem }
.cp-label {
  display:block;font-size:.75rem;font-weight:700;
  color:var(--ca-text-2);text-transform:uppercase;
  letter-spacing:.06em;margin-bottom:.45rem;
}
.cp-input-wrap { position:relative }
.cp-input {
  width:100%;padding:.875rem 3rem .875rem 3rem;
  background:var(--ca-bg3);border:1.5px solid var(--ca-border);
  border-radius:var(--ca-radius-sm);
  color:var(--ca-text);font-family:inherit;font-size:.925rem;
  transition:border-color var(--ca-transition);
  -webkit-appearance:none;appearance:none;
}
.cp-input:focus {
  outline:none;border-color:var(--ca-teal-l);
  box-shadow:0 0 0 3px rgba(0,132,87,.15);
}
.cp-ico-left {
  position:absolute;left:1rem;top:50%;transform:translateY(-50%);
  color:var(--ca-text-3);font-size:.85rem;pointer-events:none;
}
.cp-eye {
  position:absolute;right:.9rem;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--ca-text-3);
  font-size:.85rem;cursor:pointer;padding:.25rem;
}
.cp-eye:hover { color:var(--ca-text-2) }

/* Strength bar */
.cp-strength { margin-top:.5rem }
.cp-strength-track {
  height:4px;background:var(--ca-bg4);border-radius:99px;
  overflow:hidden;margin-bottom:.3rem;
}
.cp-strength-fill {
  height:100%;border-radius:99px;width:0;
  transition:width .25s,background .25s;
}
.cp-strength-lbl { font-size:.68rem;color:var(--ca-text-3) }

.cp-hint { font-size:.7rem;color:var(--ca-text-3);margin-top:.35rem }

.cp-btn {
  width:100%;padding:.9rem;margin-top:.75rem;
  background:linear-gradient(135deg,var(--ca-teal-l),var(--ca-teal));
  border:none;border-radius:var(--ca-radius-sm);
  color:#fff;font-size:.925rem;font-weight:700;
  cursor:pointer;font-family:inherit;
  transition:opacity .18s,transform .1s;
}
.cp-btn:active { transform:scale(.98) }

.cp-info {
  background:var(--ca-bg3);border:1px solid var(--ca-border);
  border-radius:14px;padding:.875rem 1rem;
  margin-bottom:1.5rem;
  display:flex;align-items:flex-start;gap:.75rem;
  font-size:.8rem;color:var(--ca-text-2);line-height:1.5;
}
.cp-info i { color:var(--ca-accent-l);font-size:1rem;flex-shrink:0;margin-top:.1rem }
[x-cloak] { display:none !important }
</style>
@endpush

@section('main_class', 'ca-main--narrow')
@section('content')
<div class="cp-body">

{{-- Info --}}
<div class="cp-info">
  <i class="fas fa-circle-info"></i>
  <span>{{ __('app.cp_info_text') }}</span>
</div>

{{-- Erreur globale --}}
@if($errors->any())
<div style="background:rgba(255,90,90,.08);border:1px solid rgba(255,90,90,.25);border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--ca-negative)">
  <i class="fas fa-triangle-exclamation" style="margin-right:.4rem"></i>
  {{ $errors->first() }}
</div>
@endif

<form data-confirm="{{ __('app.confirm_password') }}" method="POST" action="{{ route('client.app.profile.password.save') }}" x-data="cpForm()" @submit="submitting = true">
  @csrf

  {{-- Mot de passe actuel --}}
  <div class="cp-field">
    <label class="cp-label" for="cp-cur">{{ __('app.current_password') }}</label>
    <div class="cp-input-wrap">
      <i class="cp-ico-left fas fa-lock"></i>
      <input :type="showCur ? 'text' : 'password'" id="cp-cur" name="current_password"
             class="cp-input" style="@error('current_password') border-color:var(--ca-negative) @enderror"
             autocomplete="current-password" required>
      <button type="button" class="cp-eye" @click="showCur = !showCur" :aria-label="showCur ? '{{ __('app.cp_hide') }}' : '{{ __('app.cp_show') }}'"  >
        <i :class="showCur ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      </button>
    </div>
    @error('current_password')
    <div class="cp-hint" style="color:var(--ca-negative)"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
    @enderror
  </div>

  {{-- Nouveau --}}
  <div class="cp-field">
    <label class="cp-label" for="cp-new">{{ __('app.new_password') }}</label>
    <div class="cp-input-wrap">
      <i class="cp-ico-left fas fa-key"></i>
      <input :type="showNew ? 'text' : 'password'" id="cp-new" name="password"
             class="cp-input"
             x-model="pw" @input="strength()"
             autocomplete="new-password" required minlength="8">
      <button type="button" class="cp-eye" @click="showNew = !showNew" :aria-label="showNew ? '{{ __('app.cp_hide') }}' : '{{ __('app.cp_show') }}'"  >
        <i :class="showNew ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      </button>
    </div>
    {{-- Strength bar --}}
    <div class="cp-strength" x-show="pw.length > 0">
      <div class="cp-strength-track">
        <div class="cp-strength-fill" :style="{ width: (score*25)+'%', background: colors[score] }"></div>
      </div>
      <div class="cp-strength-lbl" :style="{ color: colors[score] }" x-text="labels[score]"></div>
    </div>
  </div>

  {{-- Confirmer --}}
  <div class="cp-field">
    <label class="cp-label" for="cp-confirm">{{ __('app.confirm_password') }}</label>
    <div class="cp-input-wrap">
      <i class="cp-ico-left fas fa-key"></i>
      <input :type="showConfirm ? 'text' : 'password'" id="cp-confirm" name="password_confirmation"
             class="cp-input"
             x-model="pwc"
             autocomplete="new-password" required>
      <button type="button" class="cp-eye" @click="showConfirm = !showConfirm" :aria-label="showConfirm ? '{{ __('app.cp_hide') }}' : '{{ __('app.cp_show') }}'"  >
        <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      </button>
    </div>
    <div class="cp-hint" x-show="pwc.length > 0 && pw !== pwc" style="color:var(--ca-negative)">
      <i class="fas fa-triangle-exclamation"></i> {{ __('app.cp_nomatch') }}
    </div>
    <div class="cp-hint" x-show="pwc.length > 0 && pw === pwc" style="color:var(--ca-positive)">
      <i class="fas fa-check"></i> {{ __('app.cp_match') }}
    </div>
  </div>

  <button type="submit" class="cp-btn" :disabled="submitting || pw.length < 8 || pw !== pwc">
    <i class="fas fa-spinner fa-spin" x-show="submitting" x-cloak style="margin-right:.5rem"></i>
    <i class="fas fa-shield-halved" x-show="!submitting" style="margin-right:.5rem"></i>
    <span x-text="submitting ? '{{ __('app.cp_saving') }}' : '{{ __('app.change_password') }}'"></span>
  </button>
</form>

</div>
@endsection

@push('scripts')
<script>
function cpForm() {
  return {
    showCur: false, showNew: false, showConfirm: false,
    submitting: false,
    pw: '', pwc: '', score: 0,
    colors: ['', '#ef4444','#f97316','#eab308','#22c55e'],
    labels: ['', @json(__('app.cp_strength_weak')), @json(__('app.cp_strength_fair')), @json(__('app.cp_strength_good')), @json(__('app.cp_strength_strong'))],
    strength() {
      const p = this.pw;
      let s = 0;
      if (p.length >= 8) s++;
      if (/[A-Z]/.test(p)) s++;
      if (/[0-9]/.test(p)) s++;
      if (/[^A-Za-z0-9]/.test(p)) s++;
      this.score = s;
    }
  };
}
</script>
@endpush
