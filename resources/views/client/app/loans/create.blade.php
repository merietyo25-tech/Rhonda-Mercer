@extends('layouts.client-app')
@section('title', __('loans.new_request') . ' — ' . site_name())
@section('page_title', __('loans.new_request'))
@section('back_btn', true)
@section('back_url', route('client.app.loans'))

@push('styles')
<style>
.lc-page{padding:1.25rem 1.25rem 2rem;max-width:620px}
.lc-card{background:var(--ca-bg2);border:1px solid var(--ca-border);border-radius:20px;padding:1.3rem 1.3rem 1.1rem;margin-bottom:1rem}
.lc-lbl{display:flex;justify-content:space-between;align-items:baseline;font-size:.78rem;font-weight:700;color:var(--ca-text-2);margin-bottom:.5rem}
.lc-val{font-family:'Space Grotesk',sans-serif;font-size:1.35rem;font-weight:800;color:var(--ca-text)}
.lc-range{width:100%;accent-color:#008457;height:34px;margin:0 0 .15rem}
.lc-bounds{display:flex;justify-content:space-between;font-size:.7rem;color:var(--ca-text-3);margin-bottom:1.1rem}
.lc-sim{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-top:.3rem}
.lc-sim div{background:var(--ca-bg3);border-radius:14px;padding:.8rem .9rem}
.lc-sim small{display:block;font-size:.66rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ca-text-3);margin-bottom:.25rem;font-weight:700}
.lc-sim strong{font-size:.98rem;color:var(--ca-text)}
.lc-sim .is-main{grid-column:1/-1;background:linear-gradient(135deg,#3A3590,#231F5F)}
.lc-sim .is-main small{color:rgba(255,255,255,.6)}
.lc-sim .is-main strong{color:#fff;font-size:1.5rem;font-family:'Space Grotesk',sans-serif}
.lc-note{font-size:.72rem;color:var(--ca-text-3);line-height:1.5;margin:.8rem 0 0}
.lc-field{margin-bottom:1rem}
.lc-field select,.lc-field textarea{width:100%;padding:.75rem .9rem;border-radius:12px;border:1px solid var(--ca-border);background:var(--ca-bg3);color:var(--ca-text);font-size:16px;font-family:inherit}
.lc-field select{min-height:48px}
.lc-field textarea{min-height:92px;resize:vertical}
.lc-field select:focus,.lc-field textarea:focus{outline:2px solid #008457}
.lc-err{color:#dc2626;font-size:.75rem;margin-top:.3rem}
.lc-send{width:100%;min-height:54px;border:0;border-radius:999px;background:linear-gradient(135deg,#3FCB92,#008457);color:#fff;font-weight:800;font-size:.95rem;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.6rem;box-shadow:0 10px 26px rgba(0,132,87,.3)}
.lc-send:disabled{opacity:.6}
</style>
@endpush

@section('content')
@php $cur = $user->currency ?: 'EUR'; $step = max(100, (int) (($settings['max'] - $settings['min']) / 100)); @endphp
<form class="lc-page" method="POST" action="{{ route('client.app.loans.store') }}"
      x-data="loanForm({{ $settings['min'] }}, {{ $settings['max'] }}, {{ $settings['rate'] }}, {{ \App\Models\LoanApplication::DURATION_MIN }}, {{ \App\Models\LoanApplication::DURATION_MAX }}, {{ (float) old('amount', min(max(5000, $settings['min']), $settings['max'])) }}, {{ (int) old('duration_months', 24) }})"
      @submit="busy = true">
  @csrf

  <div class="lc-card">
    <div class="lc-lbl"><span>{{ __('loans.amount') }}</span><span class="lc-val"><span x-text="fmt(amount)"></span> {{ $cur }}</span></div>
    <input class="lc-range" type="range" name="amount" :min="min" :max="max" step="{{ $step }}" x-model.number="amount" aria-label="{{ __('loans.amount') }}">
    <div class="lc-bounds"><span>{{ number_format($settings['min'], 0, ',', ' ') }}</span><span>{{ number_format($settings['max'], 0, ',', ' ') }} {{ $cur }}</span></div>
    @error('amount')<div class="lc-err">{{ $message }}</div>@enderror

    <div class="lc-lbl"><span>{{ __('loans.duration') }}</span><span class="lc-val"><span x-text="months"></span> {{ __('loans.months') }}</span></div>
    <input class="lc-range" type="range" name="duration_months" :min="dmin" :max="dmax" step="1" x-model.number="months" aria-label="{{ __('loans.duration') }}">
    <div class="lc-bounds"><span>{{ \App\Models\LoanApplication::DURATION_MIN }} {{ __('loans.months') }}</span><span>{{ \App\Models\LoanApplication::DURATION_MAX }} {{ __('loans.months') }}</span></div>
    @error('duration_months')<div class="lc-err">{{ $message }}</div>@enderror

    <div class="lc-sim" aria-live="polite">
      <div class="is-main"><small>{{ __('loans.monthly') }}</small><strong><span x-text="fmt(monthly, 2)"></span> {{ $cur }}</strong></div>
      <div><small>{{ __('loans.rate') }}</small><strong>{{ rtrim(rtrim(number_format($settings['rate'], 2, ',', ''), '0'), ',') }} %</strong></div>
      <div><small>{{ __('loans.total_cost') }}</small><strong><span x-text="fmt(cost, 2)"></span> {{ $cur }}</strong></div>
    </div>
    <p class="lc-note"><i class="fas fa-circle-info"></i> {{ __('loans.note') }}</p>
  </div>

  <div class="lc-card">
    <div class="lc-field">
      <label class="lc-lbl" for="purpose">{{ __('loans.purpose') }}</label>
      <select id="purpose" name="purpose" required>
        @foreach(\App\Models\LoanApplication::PURPOSES as $p)
        <option value="{{ $p }}" @selected(old('purpose') === $p)>{{ __('loans.p_' . $p) }}</option>
        @endforeach
      </select>
      @error('purpose')<div class="lc-err">{{ $message }}</div>@enderror
    </div>
    <div class="lc-field" style="margin-bottom:0">
      <label class="lc-lbl" for="message">{{ __('loans.message') }}</label>
      <textarea id="message" name="message" maxlength="1000" placeholder="{{ __('loans.message_ph') }}">{{ old('message') }}</textarea>
      @error('message')<div class="lc-err">{{ $message }}</div>@enderror
    </div>
  </div>

  <button type="submit" class="lc-send" :disabled="busy"><i class="fas fa-paper-plane"></i> {{ __('loans.send') }}</button>
  <p class="lc-note" style="text-align:center">{{ __('loans.limits', ['min' => number_format($settings['min'], 0, ',', ' '), 'max' => number_format($settings['max'], 0, ',', ' '), 'currency' => $cur, 'dmin' => \App\Models\LoanApplication::DURATION_MIN, 'dmax' => \App\Models\LoanApplication::DURATION_MAX]) }}</p>
</form>

<script>
function loanForm(min, max, rate, dmin, dmax, amount, months) {
  return {
    min, max, rate, dmin, dmax, amount, months, busy: false,
    get monthly() {
      var r = this.rate / 12 / 100, n = this.months;
      return r > 0 ? this.amount * r / (1 - Math.pow(1 + r, -n)) : this.amount / n;
    },
    get cost() { return this.monthly * this.months - this.amount; },
    fmt(v, d) { return new Intl.NumberFormat(document.documentElement.lang || 'fr', { minimumFractionDigits: d || 0, maximumFractionDigits: d || 0 }).format(v); }
  };
}
</script>
@endsection
