@extends('layouts.client-app')
@section('title', __('kyc.title') . ' — ' . site_name())
@section('page_title', __('kyc.title'))
@section('back_btn', true)
@section('back_url', route('client.app.profile'))

@push('styles')
<style>
.kyc-body { padding:1.25rem 1.25rem 2.5rem; max-width:720px; margin:0 auto; width:100% }

/* Indicateur de progression */
.kyc-steps { display:flex; align-items:flex-start; margin-bottom:.6rem }
.kyc-step { flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; gap:.45rem; position:relative; min-width:0 }
.kyc-step + .kyc-step::before {
  content:''; position:absolute; top:17px; right:50%; width:100%; height:2px;
  background:var(--ca-border); z-index:0;
}
.kyc-step.is-done + .kyc-step::before,
.kyc-step.is-current + .kyc-step.is-done::before { background:var(--ca-accent) }
.kyc-step.is-done + .kyc-step.is-current::before { background:var(--ca-accent) }
.kyc-step__dot {
  width:36px; height:36px; border-radius:50%; z-index:1;
  display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:700;
  background:var(--ca-bg2); border:2px solid var(--ca-border); color:var(--ca-text-3);
}
.kyc-step.is-current .kyc-step__dot { border-color:var(--ca-accent); color:var(--ca-accent); box-shadow:0 0 0 4px rgba(0,132,87,.15) }
.kyc-step.is-done .kyc-step__dot { background:var(--ca-accent); border-color:var(--ca-accent); color:#fff }
.kyc-step__label { font-size:.74rem; font-weight:600; color:var(--ca-text-3); line-height:1.3; padding:0 .25rem; overflow-wrap:anywhere }
.kyc-step.is-current .kyc-step__label, .kyc-step.is-done .kyc-step__label { color:var(--ca-text) }
.kyc-progress-txt { text-align:center; font-size:.76rem; color:var(--ca-text-3); margin-bottom:1.4rem }

.kyc-head { margin-bottom:1.1rem }
.kyc-head h2 { font-size:1.1rem; font-weight:700; color:var(--ca-text); margin-bottom:.3rem }
.kyc-head p  { font-size:.82rem; color:var(--ca-text-3); line-height:1.55 }

.kyc-status {
  display:flex; align-items:center; gap:.75rem;
  padding:1rem 1.125rem; border-radius:var(--ca-radius-md);
  margin-bottom:1.25rem; border:1px solid var(--ca-border);
}
.kyc-status--non_soumis { background:var(--ca-bg2) }
.kyc-status--en_attente { background:rgba(245,158,11,.1); border-color:rgba(245,158,11,.3) }
.kyc-status--approuve   { background:rgba(0,200,150,.1); border-color:rgba(0,200,150,.3) }
.kyc-status--rejete     { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3) }
.kyc-status__ico { font-size:1.3rem }
.kyc-status--non_soumis .kyc-status__ico { color:var(--ca-text-3) }
.kyc-status--en_attente .kyc-status__ico { color:#f59e0b }
.kyc-status--approuve   .kyc-status__ico { color:var(--ca-positive) }
.kyc-status--rejete     .kyc-status__ico { color:#ef4444 }
.kyc-status__title { font-size:.9rem; font-weight:700; color:var(--ca-text) }
.kyc-status__sub   { font-size:.78rem; color:var(--ca-text-3); margin-top:.1rem; overflow-wrap:anywhere }

/* Formulaires */
.kyc-grid { display:grid; grid-template-columns:1fr; gap:0 1rem }
.kyc-form-group { margin-bottom:1.1rem; min-width:0 }
.kyc-form-group label { display:block; font-size:.8rem; font-weight:600; color:var(--ca-text-2); margin-bottom:.4rem }
.kyc-form-group label em { font-style:normal; font-weight:400; color:var(--ca-text-3) }
.kyc-form-group select,
.kyc-form-group input[type=text],
.kyc-form-group input[type=date],
.kyc-form-group textarea {
  width:100%; min-height:46px; padding:.7rem .9rem; border-radius:var(--ca-radius-sm);
  border:1px solid var(--ca-border); background:var(--ca-bg2); color:var(--ca-text);
  font-size:16px; font-family:inherit;
}
.kyc-form-group textarea { min-height:84px; resize:vertical }
.kyc-form-group select:focus, .kyc-form-group input:focus, .kyc-form-group textarea:focus { outline:2px solid var(--ca-accent); outline-offset:0 }
.kyc-hint { font-size:.74rem; color:var(--ca-text-3); margin-top:.3rem }

/* Dépôt de fichiers */
.kyc-file {
  display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
  padding:.8rem .9rem; border:1.5px dashed var(--ca-border); border-radius:var(--ca-radius-md);
  background:var(--ca-bg2); cursor:pointer;
}
.kyc-file:hover { border-color:var(--ca-accent) }
.kyc-file i { color:var(--ca-accent); font-size:1.1rem }
.kyc-file__txt { flex:1; min-width:0; font-size:.82rem; color:var(--ca-text-2); overflow-wrap:anywhere }
.kyc-file input[type=file] { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none }

.kyc-docs { display:grid; gap:.6rem; margin-bottom:1.4rem }
.kyc-doc { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.75rem .9rem;
  border:1px solid var(--ca-border); border-radius:var(--ca-radius-sm); background:var(--ca-bg2); font-size:.82rem; color:var(--ca-text) }
.kyc-doc__name { min-width:0; overflow-wrap:anywhere }
.kyc-badge { flex-shrink:0; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:999px }
.kyc-badge--ok { background:rgba(0,200,150,.14); color:var(--ca-positive) }
.kyc-badge--no { background:rgba(239,68,68,.12); color:#ef4444 }
.kyc-badge--opt { background:var(--ca-border); color:var(--ca-text-3) }

.kyc-actions { display:flex; flex-direction:column; gap:.6rem; margin-top:.5rem }
.kyc-submit-btn, .kyc-ghost-btn {
  width:100%; min-height:48px; padding:.85rem; border-radius:var(--ca-radius-sm);
  font-size:.92rem; font-weight:700; cursor:pointer; text-align:center; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
}
.kyc-submit-btn { background:var(--ca-accent); color:#fff; border:none }
.kyc-ghost-btn { background:transparent; color:var(--ca-text-2); border:1px solid var(--ca-border) }
.kyc-selfie-preview { width:100%; max-height:220px; object-fit:cover; border-radius:var(--ca-radius-md); margin-top:.5rem; display:none }

@media (min-width:600px) {
  .kyc-body { padding:1.75rem 1.75rem 3rem }
  .kyc-grid--2 { grid-template-columns:1fr 1fr }
  .kyc-grid--2 .span-2 { grid-column:1 / -1 }
  .kyc-actions { flex-direction:row-reverse }
  .kyc-actions > * { width:auto; flex:1 }
}
</style>
@endpush

@section('content')
<div class="kyc-body">

@php
  $K = \App\Models\KycVerification::class;
  $status = $kyc->status ?? $K::STATUS_NON_SOUMIS;
  $locked = in_array($status, [$K::STATUS_EN_ATTENTE, $K::STATUS_APPROUVE]);
  $isLast = $step === $total;
  $hasFiles = \App\Services\KycForm::hasFiles();

  // Intitulé d'une étape selon son contenu : informations, pièces justificatives ou les deux
  $meta = function ($stepFields) {
      $hasFile = $stepFields->contains(fn ($f) => $f->isFile());
      $hasInfo = $stepFields->contains(fn ($f) => ! $f->isFile());
      if ($hasInfo && ! $hasFile) return ['onboarding.step1_label', 'onboarding.step1_title', 'onboarding.step1_sub'];
      if ($hasFile && ! $hasInfo) return ['onboarding.step2_label', 'onboarding.step2_title', 'onboarding.step2_sub'];
      return ['kyc.title', 'kyc.title', 'kyc.combined_sub'];
  };
@endphp

{{-- Indicateur de progression --}}
@if($total > 1)
<div class="kyc-steps" role="list">
  @foreach($steps as $n => $stepFields)
  @php $done = $locked || ($n !== $step && ! \App\Services\KycForm::missingRequired($user, $stepFields)); @endphp
  <div class="kyc-step {{ $done ? 'is-done' : ($n === $step ? 'is-current' : '') }}" role="listitem">
    <div class="kyc-step__dot">@if($done)<i class="fas fa-check"></i>@else {{ $n }} @endif</div>
    <div class="kyc-step__label">{{ __($meta($stepFields)[0]) }}</div>
  </div>
  @endforeach
</div>
<div class="kyc-progress-txt">{{ __('onboarding.kyc_progress', ['current' => $locked ? $total : $step, 'total' => $total]) }}</div>
@endif

@php [$lblKey, $titleKey, $subKey] = $meta($fields); @endphp

@if($status !== $K::STATUS_NON_SOUMIS)
<div class="kyc-status kyc-status--{{ $status }}">
  <div class="kyc-status__ico">
    @switch($status)
      @case($K::STATUS_EN_ATTENTE) <i class="fas fa-hourglass-half"></i> @break
      @case($K::STATUS_APPROUVE)   <i class="fas fa-check-circle"></i> @break
      @case($K::STATUS_REJETE)     <i class="fas fa-times-circle"></i> @break
      @default <i class="fas fa-id-card"></i>
    @endswitch
  </div>
  <div>
    <div class="kyc-status__title">{{ __('kyc.status_' . $status) }}</div>
    @if($status === $K::STATUS_EN_ATTENTE)
      <div class="kyc-status__sub">{{ __($hasFiles ? 'kyc.pending_sub_docs' : 'kyc.pending_sub_info') }}</div>
    @endif
    @if($status === $K::STATUS_REJETE && $kyc->rejection_reason)
      <div class="kyc-status__sub">{{ __('kyc.rejection_reason_label') }} : {{ $kyc->rejection_reason }}</div>
    @endif
  </div>
</div>
@endif

@if($kyc && $kyc->submitted_at)
<div class="kyc-head"><h2 style="font-size:.95rem">{{ __('onboarding.doc_status') }}</h2></div>
<div class="kyc-docs">
  @foreach($allFields->filter(fn ($f) => $f->isFile()) as $f)
  @php $has = (bool) \App\Services\KycForm::filePath($user, $f); @endphp
  <div class="kyc-doc">
    <span class="kyc-doc__name">{{ $f->displayLabel() }}</span>
    @if($has)
      <span class="kyc-badge kyc-badge--ok"><i class="fas fa-check"></i> {{ __('onboarding.doc_sent') }}</span>
    @elseif(! $f->required)
      <span class="kyc-badge kyc-badge--opt">{{ __('onboarding.doc_optional') }}</span>
    @else
      <span class="kyc-badge kyc-badge--no">{{ __('onboarding.doc_missing') }}</span>
    @endif
  </div>
  @endforeach
</div>
@endif

@if(! $locked)
<div class="kyc-head">
  <h2>{{ __($titleKey) }}</h2>
  <p>{{ __($subKey) }}</p>
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:1rem">{{ $errors->first() }}</div>
@endif

<form data-confirm="{{ $isLast ? __($hasFiles ? 'onboarding.confirm_submit' : 'kyc.confirm_submit_info') : __('onboarding.confirm_info') }}" method="POST" action="{{ route('client.app.kyc.store') }}" enctype="multipart/form-data" novalidate>
  @csrf
  <input type="hidden" name="step" value="{{ $step }}">

  <div class="kyc-grid kyc-grid--2">
    @foreach($fields as $f)
      @include('client.app.kyc._field', ['f' => $f, 'user' => $user])
    @endforeach
  </div>

  <div class="kyc-actions">
    <button type="submit" class="kyc-submit-btn">
      @if($isLast){{ $status === $K::STATUS_REJETE ? __('kyc.resubmit') : __('kyc.submit') }}@else{{ __('onboarding.continue') }} <i class="fas fa-arrow-right"></i>@endif
    </button>
    @if($step > 1)
    <a href="{{ route('client.app.kyc.show', ['step' => $step - 1]) }}" class="kyc-ghost-btn"><i class="fas fa-arrow-left"></i> {{ __('onboarding.back') }}</a>
    @endif
  </div>
</form>
@endif

</div>
@endsection

@push('scripts')
<script>
/* Sélecteur de pays : liste localisée (Intl) et détection automatique du pays de l'utilisateur */
var KYC_COUNTRY_CODES = [
    'AD','AE','AF','AG','AI','AL','AM','AO','AR','AS','AT','AU','AW','AX','AZ',
    'BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BW','BY','BZ',
    'CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ',
    'DE','DJ','DK','DM','DO','DZ',
    'EC','EE','EG','EH','ER','ES','ET',
    'FI','FJ','FK','FM','FO','FR',
    'GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP','GQ','GR','GT','GU','GW','GY',
    'HK','HN','HR','HT','HU',
    'ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT',
    'JE','JM','JO','JP',
    'KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ',
    'LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY',
    'MA','MC','MD','ME','MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ',
    'NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ',
    'OM',
    'PA','PE','PF','PG','PH','PK','PL','PM','PN','PR','PS','PT','PW','PY',
    'QA',
    'RE','RO','RS','RU','RW',
    'SA','SB','SC','SD','SE','SG','SH','SI','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ',
    'TC','TD','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ',
    'UA','UG','US','UY','UZ',
    'VA','VC','VE','VG','VI','VN','VU',
    'WF','WS',
    'YE','YT',
    'ZA','ZM','ZW',
];
window.kycCountry = function (initial) {
  return {
    country: initial || '',
    countries: [],
    detected: false,
    locale: "{{ str_replace('_', '-', app()->getLocale()) }}",

    init() { this.build(); this.detect(); },

    build() {
      var names = null;
      try { names = new Intl.DisplayNames([this.locale, 'fr', 'en'], { type: 'region' }); } catch (e) {}
      this.countries = KYC_COUNTRY_CODES
        .map(function (code) {
          var name = code;
          if (names) { try { name = names.of(code) || code; } catch (e) {} }
          return { code: code, name: name };
        })
        .sort(function (a, b) { return a.name.localeCompare(b.name, this.locale); }.bind(this));
    },

    async detect() {
      if (this.country) return; // déjà renseigné (enregistrement précédent ou retour de validation)

      // 1) Détection par IP (service gratuit, sans clé) ; repli silencieux en cas d'échec.
      try {
        var ctrl = new AbortController(), timer = setTimeout(function () { ctrl.abort(); }, 3000);
        var res = await fetch('https://get.geojs.io/v1/ip/country.json', { signal: ctrl.signal });
        clearTimeout(timer);
        if (res.ok) {
          var json = await res.json();
          var found = this.countries.find(function (c) { return c.code === (json.country || '').toUpperCase(); });
          if (found && !this.country) { this.country = found.name; this.detected = true; return; }
        }
      } catch (e) { /* pas de réseau : repli sur la langue du navigateur */ }

      // 2) Repli : région de la langue du navigateur (ex. « pt-PT » → PT).
      if (!this.country) {
        var nav = navigator.language || (navigator.languages && navigator.languages[0]) || '';
        var region = nav.split('-')[1];
        if (region) {
          var f = this.countries.find(function (c) { return c.code === region.toUpperCase(); });
          if (f) { this.country = f.name; this.detected = true; }
        }
      }
    },
  };
};
document.querySelectorAll('.kyc-file input[type=file]').forEach(function (input) {
  input.addEventListener('change', function () {
    var txt = input.closest('.kyc-file').querySelector('.kyc-file__txt');
    txt.textContent = input.files[0] ? input.files[0].name : txt.dataset.default;
  });
});
document.getElementById('kyc-selfie-input')?.addEventListener('change', function (e) {
  var file = e.target.files[0], preview = document.getElementById('kyc-selfie-preview');
  if (file && file.type.indexOf('image/') === 0) { preview.src = URL.createObjectURL(file); preview.style.display = 'block'; }
});
</script>
@endpush
