@extends('layouts.client-app')
@section('title', __('loans.new_request') . ' — ' . site_name())
@section('page_title', __('loans.new_request'))
@section('back_btn', true)
@section('back_url', route('client.app.loans'))

@push('styles')
<style>
.lc-page{padding:1.25rem 1.25rem 2rem;max-width:620px}
.lc-card{background:var(--ca-bg2);border:1px solid var(--ca-border);border-radius:20px;padding:1.3rem 1.3rem 1.1rem;margin-bottom:1rem}
.lc-lbl{display:block;font-size:.78rem;font-weight:700;color:var(--ca-text-2);margin-bottom:.5rem}
.lc-input{position:relative;margin-bottom:.35rem}
.lc-input input{width:100%;min-height:54px;padding:.7rem 4.6rem .7rem 1rem;border-radius:14px;border:1.5px solid var(--ca-border);background:var(--ca-bg3);color:var(--ca-text);font-family:'Space Grotesk',sans-serif;font-size:1.35rem;font-weight:800;-moz-appearance:textfield;appearance:textfield}
.lc-input input::-webkit-outer-spin-button,.lc-input input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.lc-input input:focus{outline:0;border-color:#008457;box-shadow:0 0 0 3.5px rgba(0,132,87,.16)}
.lc-input input.is-bad{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.12)}
.lc-input .lc-unit{position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:.85rem;font-weight:700;color:var(--ca-text-3);pointer-events:none}
.lc-bounds{font-size:.72rem;color:var(--ca-text-3);margin-bottom:1.1rem;line-height:1.45}
.lc-bounds.is-bad{color:#dc2626;font-weight:600}
.lc-quick{display:flex;flex-wrap:wrap;gap:.45rem;margin:.1rem 0 1.1rem}
.lc-quick button{border:1px solid var(--ca-border);background:var(--ca-bg3);color:var(--ca-text);border-radius:999px;padding:.4rem .8rem;font-size:.78rem;font-weight:700;font-family:inherit;cursor:pointer;min-height:34px}
.lc-quick button:hover{border-color:#008457;color:#008457}
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
      x-data="loanForm({{ $settings['min'] }}, {{ $settings['max'] }}, {{ $settings['rate'] }}, {{ $settings['dmin'] }}, {{ $settings['dmax'] }}, {{ old('amount') !== null ? (float) old('amount') : "''" }}, {{ old('duration_months') !== null ? (int) old('duration_months') : "''" }})"
      @submit="busy = true">
  @csrf

  <div class="lc-card">
    <label class="lc-lbl" for="amount">{{ __('loans.amount') }}</label>
    <div class="lc-input">
      <input id="amount" type="number" name="amount" inputmode="decimal" step="any" min="{{ $settings['min'] }}" max="{{ $settings['max'] }}"
             x-model.number="amount" :class="{ 'is-bad': amount !== '' && !amountOk }" placeholder="{{ number_format($settings['min'], 0, ',', ' ') }}" required autocomplete="off">
      <span class="lc-unit">{{ $cur }}</span>
    </div>
    <div class="lc-bounds" :class="{ 'is-bad': amount !== '' && !amountOk }">{{ number_format($settings['min'], 0, ',', ' ') }} – {{ number_format($settings['max'], 0, ',', ' ') }} {{ $cur }}</div>
    @error('amount')<div class="lc-err">{{ $message }}</div>@enderror

    <label class="lc-lbl" for="duration_months">{{ __('loans.duration') }}</label>
    <div class="lc-input">
      <input id="duration_months" type="number" name="duration_months" inputmode="numeric" step="1" min="{{ $settings['dmin'] }}" max="{{ $settings['dmax'] }}"
             x-model.number="months" :class="{ 'is-bad': months !== '' && !monthsOk }" placeholder="{{ $settings['dmin'] }}" required autocomplete="off">
      <span class="lc-unit">{{ __('loans.months') }}</span>
    </div>
    <div class="lc-bounds" :class="{ 'is-bad': months !== '' && !monthsOk }">{{ $settings['dmin'] }} – {{ $settings['dmax'] }} {{ __('loans.months') }}</div>
    @error('duration_months')<div class="lc-err">{{ $message }}</div>@enderror

    <div class="lc-sim" aria-live="polite">
      <div class="is-main"><small>{{ __('loans.monthly') }}</small><strong><span x-text="ready ? fmt(monthly, 2) : '—'"></span> {{ $cur }}</strong></div>
      <div><small>{{ __('loans.rate') }}</small><strong>{{ rtrim(rtrim(number_format($settings['rate'], 2, ',', ''), '0'), ',') }} %</strong></div>
      <div><small>{{ __('loans.total_cost') }}</small><strong><span x-text="ready ? fmt(cost, 2) : '—'"></span> {{ $cur }}</strong></div>
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

  <button type="submit" class="lc-send" :disabled="busy || !ready"><i class="fas fa-paper-plane"></i> {{ __('loans.send') }}</button>
  <p class="lc-note" style="text-align:center">{{ __('loans.limits', ['min' => number_format($settings['min'], 0, ',', ' '), 'max' => number_format($settings['max'], 0, ',', ' '), 'currency' => $cur, 'dmin' => $settings['dmin'], 'dmax' => $settings['dmax']]) }}</p>
</form>

<script>
function loanForm(min, max, rate, dmin, dmax, amount, months) {
  return {
    min, max, rate, dmin, dmax, amount, months, busy: false,
    get amountOk() { return this.amount !== '' && this.amount >= this.min && this.amount <= this.max; },
    get monthsOk() { return this.months !== '' && Number.isInteger(this.months) && this.months >= this.dmin && this.months <= this.dmax; },
    get ready() { return this.amountOk && this.monthsOk; },
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
