@extends('layouts.auth-flow')
@section('title', __('auth.otp_heading'))

@section('aside')
  <h2 class="aside__title" style="margin-top:0">{{ __('onboarding.otp_aside_title') }}</h2>
  <ul class="aside__list">
    <li><i class="fas fa-envelope-circle-check"></i>{{ __('onboarding.otp_aside_1') }}</li>
    <li><i class="fas fa-clock"></i>{{ __('onboarding.otp_aside_2') }}</li>
    <li><i class="fas fa-shield-halved"></i>{{ __('onboarding.otp_aside_3') }}</li>
  </ul>
@endsection

@push('styles')
<style>
.otp-icon{width:64px;height:64px;margin:0 auto 1rem;border-radius:50%;display:flex;align-items:center;justify-content:center;
  background:rgba(63,203,146,.12);color:var(--accent);font-size:1.6rem}
.odigits{display:flex;justify-content:center;gap:.55rem;margin:1.4rem 0 1rem}
.odigit{width:46px;height:56px;border-radius:12px;background:var(--inp);border:1.5px solid var(--bdr);display:flex;align-items:center;justify-content:center;
  font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;transition:border-color .15s,box-shadow .15s}
.odigit.filled{border-color:rgba(63,203,146,.55)}
.odigit.active{border-color:var(--accent);box-shadow:0 0 0 3.5px rgba(63,203,146,.16)}
.odigit.err{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.15)}
.odigit.shake{animation:oshake .35s}
@keyframes oshake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.resend-row{display:flex;align-items:center;justify-content:center;gap:.5rem;min-height:28px;font-size:.8rem;color:var(--sub);margin-bottom:.5rem}
.resend-row strong{color:var(--text)}
.resend-btn{background:none;border:none;color:var(--accent);font-weight:700;font-size:.82rem;cursor:pointer;font-family:inherit}
.resend-btn:hover{text-decoration:underline}
.resend-msg{display:block;text-align:center;font-size:.76rem;margin-bottom:.75rem}
.resend-msg.ok{color:var(--ok)}.resend-msg.fail{color:var(--danger)}
.keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:.55rem;margin-top:1.1rem;-webkit-user-select:none;user-select:none}
.kbtn{min-height:54px;border-radius:14px;background:var(--inp);border:1px solid var(--bdr);color:var(--text);cursor:pointer;display:flex;flex-direction:column;
  align-items:center;justify-content:center;font-family:inherit;touch-action:manipulation}
.kbtn:active{background:rgba(63,203,146,.18)}
.kbtn .knum{font-size:1.25rem;font-weight:600;line-height:1}
.kbtn .ksub{font-size:.55rem;letter-spacing:.12em;color:var(--muted);margin-top:.15rem}
.kbtn-empty{visibility:hidden}
/* Sur grand écran avec souris, la saisie se fait au clavier */
@media (hover:hover) and (min-width:992px){.keypad{display:none}}
@media (max-width:380px){.odigit{width:40px;height:50px}.odigits{gap:.4rem}}
</style>
@endpush

@section('content')
<div x-data="otpApp()">
  <div class="card-head">
    <div class="otp-icon"><i class="fas fa-shield-halved"></i></div>
    <h1 class="card-title">{{ __('auth.otp_heading') }}</h1>
    <p class="card-sub">{{ __('auth.otp_subtitle') }}<br><strong style="color:var(--text)">{{ $masked }}</strong></p>
  </div>

  <div class="msg msg--err" role="alert" x-show="errorMsg" x-transition style="display:none">
    <i class="fas fa-circle-exclamation"></i><span x-text="errorMsg"></span>
  </div>

  <form id="otp-form" action="{{ route('otp.verify') }}" method="POST" novalidate>
    @csrf
    <input type="hidden" name="code" :value="digits.join('')">

    {{-- Champ invisible pour le remplissage automatique par SMS/e-mail --}}
    <input id="otp-real" type="text" inputmode="none" autocomplete="one-time-code" maxlength="6" tabindex="-1" aria-hidden="true"
           style="position:fixed;opacity:0;width:1px;height:1px;pointer-events:none;top:-100px" @input="onSmsAutofill($event)">

    <div class="odigits" id="odigits-row">
      <template x-for="(d, i) in digits" :key="i">
        <div class="odigit" :class="{filled: d !== '' && !hasErr, active: d === '' && i === activeIndex && !hasErr, err: hasErr, shake: hasErr}">
          <span x-text="d" x-show="d !== ''"></span>
        </div>
      </template>
    </div>

    <div class="resend-row">
      <span x-show="timeLeft > 0">{{ __('auth.otp_resend_in') }} <strong x-text="fmtTime()"></strong></span>
      <template x-if="timeLeft <= 0 && !resending">
        <button type="button" class="resend-btn" @click="resend()">{{ __('auth.otp_resend') }}</button>
      </template>
      <template x-if="resending"><span><i class="fas fa-circle-notch fa-spin" style="color:var(--accent)"></i></span></template>
    </div>
    <span class="resend-msg" :class="resendOk ? 'ok' : 'fail'" x-show="resendMsg" x-text="resendMsg"></span>

    <button type="submit" class="fbtn" :disabled="digits.join('').length < 6 || submitting" @click.prevent="doSubmit()">
      <template x-if="!submitting"><span><i class="fas fa-check" style="margin-right:.4rem"></i>{{ __('auth.otp_verify_btn') }}</span></template>
      <template x-if="submitting"><span class="spin"></span></template>
    </button>
  </form>

  <div class="keypad">
    @foreach([['1',''],['2','ABC'],['3','DEF'],['4','GHI'],['5','JKL'],['6','MNO'],['7','PQRS'],['8','TUV'],['9','WXYZ']] as [$n,$s])
    <button type="button" class="kbtn" @pointerdown.prevent="$dispatch('otp-press', '{{ $n }}')">
      <span class="knum">{{ $n }}</span>@if($s)<span class="ksub">{{ $s }}</span>@endif
    </button>
    @endforeach
    <button type="button" class="kbtn" @pointerdown.prevent="$dispatch('otp-del')"><i class="fas fa-delete-left"></i></button>
    <button type="button" class="kbtn" @pointerdown.prevent="$dispatch('otp-press', '0')"><span class="knum">0</span></button>
    <div class="kbtn kbtn-empty"></div>
  </div>

  <p class="alt-link"><a href="{{ $backUrl ?? route('login') }}"><i class="fas fa-chevron-left" style="font-size:.6rem"></i> {{ __('auth.otp_back') }}</a></p>
</div>
@endsection

@push('scripts')
<script>
function otpApp() {
  return {
    digits:      ['','','','','',''],
    activeIndex: 0,
    timeLeft:    120,
    timer:       null,
    resending:   false,
    submitting:  false,
    resendMsg:   '',
    resendOk:    true,
    errorMsg:    '',
    hasErr:      false,

    init() {
      this.startTimer();
      document.addEventListener('keydown', (e) => {
        if (this.submitting || this.hasErr) return;
        if (e.key >= '0' && e.key <= '9') { e.preventDefault(); this.press(e.key); }
        if (e.key === 'Backspace')         { e.preventDefault(); this.del(); }
        if (e.key === 'Enter')             { e.preventDefault(); this.doSubmit(); }
      });
      window.addEventListener('otp-press', (e) => { if (!this.submitting && !this.hasErr) this.press(e.detail); });
      window.addEventListener('otp-del',   ()  => { if (!this.submitting && !this.hasErr) this.del(); });
    },

    startTimer() {
      clearInterval(this.timer);
      this.timeLeft = 120;
      this.timer = setInterval(() => { if (this.timeLeft > 0) this.timeLeft--; }, 1000);
    },

    fmtTime() {
      var m = Math.floor(this.timeLeft / 60), s = this.timeLeft % 60;
      return (m < 10 ? '0'+m : m) + ':' + (s < 10 ? '0'+s : s);
    },

    press(n) {
      if (this.activeIndex >= 6 || this.submitting) return;
      this.digits[this.activeIndex] = n;
      this.digits = [...this.digits];
      this.activeIndex = Math.min(this.activeIndex + 1, 6);
      if (this.activeIndex === 6) {
        this.$nextTick(() => { if (!this.submitting) this.doSubmit(); });
      }
    },

    del() {
      if (this.submitting) return;
      var idx = this.activeIndex - 1;
      if (idx < 0) return;
      this.digits[idx] = '';
      this.digits = [...this.digits];
      this.activeIndex = idx;
    },

    onSmsAutofill(e) {
      var val = (e.target.value || '').replace(/\D/g,'').slice(0,6);
      e.target.value = val;
      for (var i = 0; i < 6; i++) this.digits[i] = val[i] || '';
      this.digits = [...this.digits];
      this.activeIndex = Math.min(val.length, 6);
      if (val.length === 6) this.$nextTick(() => this.doSubmit());
    },

    showError(msg) {
      this.errorMsg   = msg;
      this.hasErr     = true;
      this.submitting = false;
      /* Après l'animation shake, on vide les cases et on remet le curseur */
      setTimeout(() => {
        this.hasErr      = false;
        this.digits      = ['','','','','',''];
        this.activeIndex = 0;
      }, 500);
    },

    /* Jeton CSRF courant : rafraîchi automatiquement si une requête échoue en 419
       (session expirée pendant que l'onglet restait ouvert). */
    csrfToken: '{{ csrf_token() }}',

    async refreshCsrfToken() {
      try {
        var r = await fetch('{{ route("csrf.refresh") }}', { headers: { 'Accept': 'application/json' } });
        var j = await r.json();
        if (j && j.token) this.csrfToken = j.token;
      } catch (e) { /* on retentera avec l'ancien token */ }
    },

    async postJson(url, body) {
      var doFetch = () => fetch(url, {
        method:  'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: body ? JSON.stringify(body) : undefined,
      });

      var resp = await doFetch();
      if (resp.status === 419) {
        await this.refreshCsrfToken();
        resp = await doFetch();
      }
      return resp;
    },

    async doSubmit() {
      if (this.digits.join('').length < 6 || this.submitting) return;
      this.submitting = true;
      this.errorMsg   = '';
      try {
        var resp = await this.postJson('{{ route("otp.verify") }}', { code: this.digits.join('') });
        var data = await resp.json();

        if (data.status === 'success') { window.location.href = data.url; return; }
        if (data.status === 'blocked' || data.status === 'redirect') { window.location.href = data.url || '/login'; return; }
        /* Mauvais code */
        this.showError(data.message || '{{ __("auth.otp_invalid", ["remaining" => 1]) }}');
      } catch(err) {
        this.showError('{{ __("auth.otp_send_failed") }}');
      }
    },

    async resend() {
      if (this.resending || this.timeLeft > 0) return;
      this.resending  = true;
      this.resendMsg  = '';
      try {
        var resp = await this.postJson('{{ route("otp.resend") }}');
        var data = await resp.json();
        if (resp.ok) {
          this.resendOk    = true;
          this.resendMsg   = data.message || '{{ __("auth.otp_resend_success") }}';
          this.digits      = ['','','','','',''];
          this.activeIndex = 0;
          this.errorMsg    = '';
          this.startTimer();
        } else {
          this.resendOk  = false;
          this.resendMsg = data.error || '{{ __("auth.otp_send_failed") }}';
        }
      } catch(err) {
        this.resendOk  = false;
        this.resendMsg = '{{ __("auth.otp_send_failed") }}';
      }
      this.resending = false;
      var self = this;
      setTimeout(function(){ self.resendMsg = ''; }, 4000);
    },
  };
}
</script>
@endpush
