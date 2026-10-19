@extends('layouts.auth-flow')
@section('title', __('auth.otp_heading'))

@section('aside')
  <h2 class="aside__title" style="margin-top:0">{{ __('onboarding.aside_title') }}</h2>
  <ul class="aside__list">
    <li><i class="fas fa-user-plus"></i>{{ __('onboarding.aside_s1') }}</li>
    <li><i class="fas fa-envelope-circle-check"></i>{{ __('onboarding.aside_s2') }}</li>
    <li><i class="fas fa-id-card"></i>{{ __('onboarding.aside_s3') }}</li>
  </ul>
@endsection

@section('content')
  <div class="card-head">
    <h1 class="card-title">{{ __('auth.otp_heading') }}</h1>
    <p class="card-sub">{{ __('auth.otp_subtitle') }} <strong>{{ $masked }}</strong></p>
  </div>

  @if(session('resent'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('resent') }}</span></div>
  @endif
  @if($errors->any())
  <div class="msg msg--err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <form method="POST" action="{{ route('activation.otp.verify') }}" onsubmit="this.querySelector('button[type=submit]').disabled=true">
    @csrf
    <div class="fgrp">
      <label class="flabel" for="code">{{ __('auth.otp_enter') }}</label>
      <div class="frel"><i class="fas fa-key ficon" aria-hidden="true"></i>
        <input type="text" id="code" name="code" class="finput {{ $errors->has('code') ? 'err' : '' }}" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
               autocomplete="one-time-code" placeholder="••••••" required autofocus style="letter-spacing:.5em;font-size:1.3rem;font-weight:700;text-align:center">
      </div>
    </div>
    <button type="submit" class="fbtn"><i class="fas fa-check"></i>{{ __('auth.otp_verify_btn') }}</button>
  </form>

  <form method="POST" action="{{ route('activation.otp.resend') }}" style="margin-top:.8rem" onsubmit="this.querySelector('button').disabled=true">
    @csrf
    <button type="submit" class="fbtn fbtn--ghost"><i class="fas fa-paper-plane"></i>{{ __('auth.otp_resend') }}</button>
  </form>

  <p class="alt-link"><a href="{{ route('login') }}">{{ __('onboarding.back_login') }}</a></p>
@endsection
