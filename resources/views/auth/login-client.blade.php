@extends('layouts.auth-flow')
@section('title', __('auth.client_login_title'))

@section('aside')
  <h2 class="aside__title" style="margin-top:0">{{ __('onboarding.login_aside_title') }}</h2>
  <ul class="aside__list">
    <li><i class="fas fa-chart-line"></i>{{ __('onboarding.login_a1') }}</li>
    <li><i class="fas fa-shield-halved"></i>{{ __('onboarding.login_a2') }}</li>
    <li><i class="fas fa-mobile-screen"></i>{{ __('onboarding.login_a3') }}</li>
  </ul>
@endsection

@push('styles')
<style>
.qlogin{display:flex;flex-direction:column;align-items:center;gap:.5rem;margin-bottom:1.6rem}
.qavatar{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,var(--navy2),var(--navy));display:flex;align-items:center;justify-content:center;
  font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:800;color:#fff;box-shadow:0 0 0 4px rgba(63,203,146,.14)}
.qname{font-size:.95rem;font-weight:700}
.qemail{font-size:.78rem;color:var(--sub);overflow-wrap:anywhere;text-align:center}
.qchange{display:inline-flex;align-items:center;gap:.4rem;font-size:.74rem;font-weight:600;color:var(--muted);border:1.5px solid var(--bdr);
  border-radius:8px;padding:.3rem .7rem;margin-top:.15rem}
.qchange:hover{color:var(--text);border-color:var(--accent)}
.frow{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin:.35rem 0 1.4rem}
.fcheck{display:flex;align-items:center;gap:.5rem}
.fcheck input{width:18px;height:18px;accent-color:var(--accent);cursor:pointer}
.fcheck label{font-size:.8rem;color:var(--sub);cursor:pointer}
.fforgot{font-size:.8rem;font-weight:600;color:var(--accent)}
.fforgot:hover{text-decoration:underline}
</style>
@endpush

@section('content')
  <div class="card-head">
    <h1 class="card-title">{{ __('auth.submit') }}</h1>
    <p class="card-sub">{{ __('auth.client_login_sub') }}</p>
  </div>

  @if(session('unblock_success'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('unblock_success') }}</span></div>
  @endif
  @if(session('verified_status'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('verified_status') }}</span></div>
  @endif
  @if(session('resent'))
  <div class="msg msg--ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('resent') }}</span></div>
  @endif
  @if($errors->any())
  <div class="msg msg--err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  @if(session('unverified_email'))
  <form action="{{ route('verification.resend') }}" method="POST" style="margin:-.4rem 0 1.1rem;text-align:center">
    @csrf
    <input type="hidden" name="email" value="{{ session('unverified_email') }}">
    <button type="submit" class="fforgot" style="background:none;border:none;cursor:pointer;font-family:inherit">
      <i class="fas fa-paper-plane" style="font-size:.7rem"></i> {{ __('onboarding.email_not_verified_resend') }}
    </button>
  </form>
  @endif

  <form id="login-form" action="{{ route('login.submit') }}" method="POST" novalidate>
    @csrf

    @if($remembered ?? null)
      @php $initials = collect(explode(' ', $remembered['name']))->take(2)->map(fn($w) => strtoupper(mb_substr($w,0,1)))->implode(''); @endphp
      <div class="qlogin">
        <div class="qavatar">{{ $initials }}</div>
        <div class="qname">{{ $remembered['name'] }}</div>
        <div class="qemail">{{ $remembered['email'] }}</div>
        <a href="{{ route('login.forget') }}" class="qchange"><i class="fas fa-repeat" style="font-size:.6rem"></i>{{ __('auth.change_account') }}</a>
      </div>
      <input type="hidden" name="identifier" value="{{ $remembered['email'] }}">
    @else
      <div class="fgrp">
        <label class="flabel" for="identifier">{{ __('auth.identifier') }}</label>
        <div class="frel"><i class="fas fa-envelope ficon"></i>
          <input type="text" id="identifier" name="identifier" class="finput {{ $errors->has('identifier') ? 'err' : '' }}"
                 value="{{ old('identifier') }}" placeholder="{{ __('auth.identifier_ph') }}"
                 autocomplete="username" inputmode="email" required>
        </div>
      </div>
    @endif

    <div class="fgrp">
      <label class="flabel" for="password">{{ __('auth.password_label') }}</label>
      <div class="frel has-eye"><i class="fas fa-lock ficon"></i>
        <input type="password" id="password" name="password" class="finput {{ $errors->has('password') ? 'err' : '' }}"
               placeholder="••••••••" autocomplete="current-password" required>
        <button type="button" class="feye" onclick="tglPwd(this)" aria-label="{{ __('onboarding.show_password') }}"><i class="fas fa-eye"></i></button>
      </div>
    </div>

    <div class="frow">
      @if(!($remembered ?? null))
      <div class="fcheck"><input type="checkbox" id="remember" name="remember"><label for="remember">{{ __('auth.remember') }}</label></div>
      @else
      <span></span>
      @endif
      <a href="{{ route('password.request') }}" class="fforgot">{{ __('auth.forgot_password') }}</a>
    </div>

    <button type="submit" id="submit-btn" class="fbtn"><span id="btn-text">{{ __('auth.submit') }}</span></button>
  </form>

  <p class="alt-link">
    {{ __('onboarding.no_account') }}
    <a href="{{ route('signup', ['locale' => app()->getLocale()]) }}">{{ __('onboarding.register_link') }}</a>
  </p>

@endsection

@push('scripts')
<script>
document.getElementById('login-form').addEventListener('submit', function () {
  document.getElementById('submit-btn').disabled = true;
  document.getElementById('btn-text').innerHTML = '<span class="spin"></span>';
});


</script>
@endpush
