@extends('layouts.auth-flow')
@section('title', __('onboarding.reset_title'))

@section('aside')
  <h2 class="aside__title" style="margin-top:0">{!! __('auth.staff_brand_title') !!}</h2>
  <ul class="aside__list">
    <li><i class="fas fa-envelope-circle-check"></i>{{ __('onboarding.recovery_aside_1') }}</li>
    <li><i class="fas fa-key"></i>{{ __('onboarding.recovery_aside_2') }}</li>
    <li><i class="fas fa-right-to-bracket"></i>{{ __('onboarding.recovery_aside_3') }}</li>
  </ul>
@endsection

@push('styles')
<style>
.strength-bar{height:4px;border-radius:4px;background:var(--bdr);margin-top:.5rem;overflow:hidden}
.strength-fill{height:100%;width:0;transition:width .25s,background .25s}
</style>
@endpush

@section('content')
  @php
    // Domaine issu de l'email du site configuré en admin (pas de marque figée).
    $phAdmin = 'admin@' . (\Illuminate\Support\Str::after(site_email(), '@') ?: request()->getHost());
  @endphp

  <div class="card-head">
    <h1 class="card-title">{{ __('onboarding.reset_title') }}</h1>
    <p class="card-sub">{{ __('onboarding.reset_sub_staff') }}</p>
  </div>

  @if($errors->any())
  <div class="msg msg--err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <form method="POST" action="{{ route('staff.password.update') }}" onsubmit="this.querySelector('button[type=submit]').disabled=true">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="fgrp">
      <label class="flabel" for="email">{{ __('onboarding.label_email') }}</label>
      <div class="frel"><i class="fas fa-envelope ficon"></i>
        <input type="email" id="email" name="email" class="finput {{ $errors->has('email') ? 'err' : '' }}"
               value="{{ old('email', $email) }}" placeholder="{{ $phAdmin }}" autocomplete="email" inputmode="email" required>
      </div>
    </div>

    <div class="fgrp">
      <label class="flabel" for="password">{{ __('onboarding.label_new_password') }}</label>
      <div class="frel has-eye"><i class="fas fa-lock ficon"></i>
        <input type="password" id="password" name="password" class="finput {{ $errors->has('password') ? 'err' : '' }}"
               placeholder="{{ __('onboarding.ph_new_password') }}" autocomplete="new-password" required oninput="updateStrength(this.value)">
        <button type="button" class="feye" onclick="tglPwd(this)" aria-label="{{ __('onboarding.show_password') }}"><i class="fas fa-eye"></i></button>
      </div>
      <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
    </div>

    <div class="fgrp">
      <label class="flabel" for="password_confirmation">{{ __('onboarding.label_password_confirm') }}</label>
      <div class="frel has-eye"><i class="fas fa-lock ficon"></i>
        <input type="password" id="password_confirmation" name="password_confirmation" class="finput"
               placeholder="••••••••" autocomplete="new-password" required>
        <button type="button" class="feye" onclick="tglPwd(this)" aria-label="{{ __('onboarding.show_password') }}"><i class="fas fa-eye"></i></button>
      </div>
    </div>

    <button type="submit" class="fbtn"><i class="fas fa-check-circle"></i>{{ __('onboarding.reset_btn') }}</button>
  </form>

  <p class="alt-link">{{ __('onboarding.remembered') }} <a href="{{ route('staff.login') }}">{{ __('onboarding.login_link') }}</a></p>
@endsection

@push('scripts')
<script>
function updateStrength(val) {
  var fill = document.getElementById('strength-fill'), score = 0;
  if (val.length >= 8) score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  var colors = ['#ef4444','#f97316','#eab308','#22c55e','#3FCB92'], widths = ['20%','40%','60%','80%','100%'];
  fill.style.width = score ? widths[score-1] : '0';
  fill.style.background = score ? colors[score-1] : 'transparent';
}
</script>
@endpush
