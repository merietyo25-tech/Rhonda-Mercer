@extends('layouts.auth-flow')
@section('title', __('onboarding.check_title'))

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
    <div class="otp-icon" style="width:64px;height:64px;margin:0 auto 1rem;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(63,203,146,.12);color:var(--accent);font-size:1.6rem">
      <i class="fas fa-envelope-open-text"></i>
    </div>
    <h1 class="card-title">{{ __('onboarding.check_title') }}</h1>
    <p class="card-sub">{{ __('onboarding.check_sub', ['email' => $email]) }}</p>
  </div>

  @if(session('signup_ok') && ! session('error'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('signup_ok') }}</span></div>
  @endif
  @if(session('resent'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('resent') }}</span></div>
  @endif
  @if(session('error'))
  <div class="msg msg--err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>
  @endif
  @if($errors->any())
  <div class="msg msg--err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <div class="msg msg--info"><i class="fas fa-circle-info"></i><span>{{ __('onboarding.check_hint') }}</span></div>

  <form method="POST" action="{{ route('verification.resend') }}" onsubmit="this.querySelector('button').disabled=true">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <button type="submit" class="fbtn fbtn--ghost"><i class="fas fa-paper-plane"></i>{{ __('onboarding.resend') }}</button>
  </form>

  <p class="alt-link"><a href="{{ route('login') }}">{{ __('onboarding.back_login') }}</a></p>
@endsection
