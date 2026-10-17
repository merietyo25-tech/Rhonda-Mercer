@extends('layouts.app')
@section('title', __('site.lp_h1'))
@section('body_class', 'is-landing')
@section('no_band', '1')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}?v={{ @filemtime(public_path('assets/css/landing.css')) }}">
<script>document.documentElement.classList.add('lp-js');</script>
@endpush

@section('content')
@php
    $locale    = app()->getLocale();
    $name      = site_name();
    $langCount = \App\Models\Language::enabledList()->count();
    $signup    = route('signup', ['locale' => $locale]);

    $stats = [
        ['to' => 2,          'suffix' => '',    'label' => 'site.lp_stat_1'],
        ['to' => 2,          'suffix' => '',    'label' => 'site.lp_stat_2'],
        ['to' => 100,        'suffix' => ' %',  'label' => 'site.lp_stat_4'],
    ];
    $features = [
        ['icon' => 'fa-university',   'n' => 1],
        ['icon' => 'fa-credit-card',  'n' => 2],
        ['icon' => 'fa-exchange-alt', 'n' => 3],
        ['icon' => 'fa-file-invoice', 'n' => 4],
        ['icon' => 'fa-shield-alt',   'n' => 5],
        ['icon' => 'fa-language',     'n' => 6],
    ];
    $steps = [
        ['icon' => 'fa-user-plus',   'n' => 1],
        ['icon' => 'fa-id-card',     'n' => 2],
        ['icon' => 'fa-credit-card', 'n' => 3],
        ['icon' => 'fa-paper-plane', 'n' => 4],
    ];
@endphp

{{-- ═════════ 1. Hero : promesse, preuve, appel à l'action principal ═════════ --}}
<div class="lp-progress" id="lp-progress" aria-hidden="true"></div>
<section class="lp-hero" id="top" aria-labelledby="lp-title">
    <div class="lp-hero__bg" aria-hidden="true">
        <div class="lp-hero__photo" data-lp-parallax="0.18" style="background-image:url('{{ asset('assets/images/site/hero-1.jpg') }}')"></div>
    </div>
    <span class="lp-orb lp-orb--1" aria-hidden="true"></span>
    <span class="lp-orb lp-orb--2" aria-hidden="true"></span>
    <div class="bo-wrap lp-hero__grid">
        <div class="lp-hero__copy">
            <span class="lp-badge"><i class="fas fa-lock" aria-hidden="true"></i> @lang('site.lp_badge')</span>
            <h1 id="lp-title">@lang('site.lp_h1')</h1>
            <p class="lp-hero__sub">@lang('site.lp_sub')</p>
            <div class="lp-hero__cta">
                <a href="{{ $signup }}" class="lp-btn lp-btn--primary lp-btn--lg" data-lp-cta="hero">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                <a href="#app" class="lp-btn lp-btn--ghost lp-btn--lg">@lang('site.lp_cta2')</a>
            </div>
            <ul class="lp-hero__micro">
                @foreach (['site.lp_micro1', 'site.lp_micro2', 'site.lp_micro3'] as $m)
                <li><i class="fas fa-check-circle" aria-hidden="true"></i> @lang($m)</li>
                @endforeach
            </ul>
        </div>

        <div class="lp-hero__visual" aria-hidden="true">
            <figure class="lp-hero__img" data-lp-parallax="-0.06"><img src="{{ asset('assets/images/site/hero-2.jpg') }}" width="1400" height="933" alt="" decoding="async" fetchpriority="high"></figure>
            <div class="lp-card3d" data-lp-tilt>
                <div class="lp-card3d__shine"></div>
                <div class="lp-card3d__top">
                    <span class="lp-card3d__brand"><i class="fas fa-landmark"></i> {{ $name }}</span>
                    <span class="lp-card3d__chip"></span>
                </div>
                <div class="lp-card3d__num">•••• •••• •••• 0230</div>
                <div class="lp-card3d__bottom">
                    <div><small>@lang('site.lp_card_label')</small><strong>{{ mb_strtoupper($name) }}</strong></div>
                    <i class="fab fa-cc-visa lp-card3d__visa"></i>
                </div>
            </div>
            <div class="lp-chip lp-chip--a"><i class="fas fa-check-circle"></i> @lang('site.lp_chip1')</div>
            <div class="lp-chip lp-chip--b"><i class="fas fa-university"></i> @lang('site.lp_chip2')</div>
        </div>
    </div>
</section>

{{-- ═════════ 2. Bande de chiffres réels (aucun chiffre inventé) ═════════ --}}
<section class="lp-stats" aria-label="{{ $name }}">
    <div class="bo-wrap">
        <dl class="lp-stats__row">
            @foreach ($stats as $st)
            <div class="lp-stat lp-reveal">
                <dt><span class="lp-stat__num" data-lp-count="{{ $st['to'] }}" data-suffix="{{ $st['suffix'] }}">{{ $st['to'] }}{{ $st['suffix'] }}</span></dt>
                <dd>@lang($st['label'])</dd>
            </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- ═════════ 2b. Bande photos défilante ═════════ --}}
@php $strip = ['hero-1', 'manage-phone', 'about-1', 'promo-card', 'contact-hero', 'about-2', 'hero-2']; @endphp
<section class="lp-strip" aria-hidden="true">
    <div class="lp-strip__track">
        @foreach (array_merge($strip, $strip) as $im)
        <img src="{{ asset('assets/images/site/' . $im . '.jpg') }}" width="420" height="280" alt="" loading="lazy" decoding="async">
        @endforeach
    </div>
</section>

{{-- ═════════ 3. L'application en images ═════════ --}}
<section class="lp-section lp-app" id="app" aria-labelledby="lp-app-title">
    <div class="bo-wrap lp-app__grid">
        <div class="lp-app__txt lp-reveal">
            <h2 id="lp-app-title" class="lp-h2 lp-h2--light">@lang('site.home_app_title')</h2>
            <p class="lp-lead lp-lead--light">@lang('site.home_app_text')</p>
            <ul class="lp-checks lp-checks--light">
                @foreach (['site.home_app_b1', 'site.home_app_b2', 'site.home_app_b3', 'site.home_app_b4'] as $b)
                <li><i class="fas fa-check" aria-hidden="true"></i> @lang($b)</li>
                @endforeach
            </ul>
            <a href="{{ $signup }}" class="lp-btn lp-btn--primary" data-lp-cta="app">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="lp-phones lp-reveal lp-reveal--zoom" role="group" aria-label="@lang('site.home_app_title')">
            @foreach ([['cards', 'site.home_app_cap2', 'side'], ['home', 'site.home_app_cap1', 'main'], ['transfers', 'site.home_app_cap3', 'side']] as [$img, $cap, $kind])
            <figure class="lp-phone lp-phone--{{ $kind }}">
                <div class="lp-phone__frame">
                    <img src="{{ asset('assets/images/app/screen-' . $img . '.png') }}" width="390" height="844" alt="@lang($cap)" loading="lazy" decoding="async">
                </div>
                <figcaption>@lang($cap)</figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ═════════ 4. Fonctionnalités ═════════ --}}
<section class="lp-section" aria-labelledby="lp-feat-title">
    <div class="bo-wrap">
        <h2 id="lp-feat-title" class="lp-h2 lp-h2--center lp-reveal">@lang('site.home_feat_title')</h2>
        <div class="lp-feat">
            @foreach ($features as $f)
            <article class="lp-feat__card lp-reveal">
                <span class="lp-feat__ico" aria-hidden="true"><i class="fas {{ $f['icon'] }}"></i></span>
                <h3>@lang('site.home_feat_' . $f['n'] . '_t')</h3>
                <p>@lang('site.home_feat_' . $f['n'] . '_x', ['count' => $langCount])</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ═════════ 4b. Photo + réassurance ═════════ --}}
<section class="lp-section lp-trust" aria-labelledby="lp-trust-title">
    <div class="bo-wrap lp-trust__grid">
        <figure class="lp-trust__photo lp-reveal lp-reveal--left">
            <img src="{{ asset('assets/images/site/about-2.jpg') }}" width="1400" height="933" alt="" loading="lazy" decoding="async">
            <span class="lp-trust__badge"><i class="fas fa-shield-alt" aria-hidden="true"></i> @lang('site.lp_micro2')</span>
        </figure>
        <div class="lp-trust__txt lp-reveal lp-reveal--right">
            <h2 id="lp-trust-title" class="lp-h2">@lang('site.home_feat_5_t')</h2>
            <p class="lp-lead">@lang('site.home_feat_5_x')</p>
            <ul class="lp-checks">
                @foreach (['site.lp_micro1', 'site.lp_micro2', 'site.lp_micro3'] as $m)
                <li><i class="fas fa-check" aria-hidden="true"></i> @lang($m)</li>
                @endforeach
            </ul>
            <a href="{{ $signup }}" class="lp-btn lp-btn--primary" data-lp-cta="trust">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

{{-- ═════════ 5. Entonnoir : 4 étapes ═════════ --}}
<section class="lp-section lp-steps" aria-labelledby="lp-steps-title">
    <div class="bo-wrap">
        <h2 id="lp-steps-title" class="lp-h2 lp-h2--center lp-reveal">@lang('site.home_steps_title')</h2>
        <ol class="lp-steps__row">
            @foreach ($steps as $st)
            <li class="lp-step lp-reveal">
                <span class="lp-step__num" aria-hidden="true">{{ $st['n'] }}</span>
                <span class="lp-step__ico" aria-hidden="true"><i class="fas {{ $st['icon'] }}"></i></span>
                <h3>@lang('site.home_step_' . $st['n'] . '_t')</h3>
                <p>@lang('site.home_step_' . $st['n'] . '_x')</p>
            </li>
            @endforeach
        </ol>
        <div class="lp-center lp-reveal">
            <a href="{{ $signup }}" class="lp-btn lp-btn--primary lp-btn--lg" data-lp-cta="steps">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

{{-- ═════════ 6. Quelle carte choisir ? ═════════ --}}
<section class="lp-section" aria-labelledby="lp-cmp-title">
    <div class="bo-wrap">
        <h2 id="lp-cmp-title" class="lp-h2 lp-h2--center lp-reveal">@lang('site.lp_cmp_title')</h2>
        <div class="lp-cmp">
            <article class="lp-cmp__card lp-reveal">
                <img class="lp-cmp__img" src="{{ asset('assets/images/site/manage-phone.jpg') }}" width="1400" height="933" alt="" loading="lazy" decoding="async">
                <span class="lp-cmp__ico" aria-hidden="true"><i class="fas fa-mobile-alt"></i></span>
                <h3>@lang('site.lp_cmp_v_t')</h3>
                <p>@lang('site.lp_cmp_v_x')</p>
            </article>
            <article class="lp-cmp__card lp-reveal">
                <img class="lp-cmp__img" src="{{ asset('assets/images/site/promo-card.jpg') }}" width="1400" height="933" alt="" loading="lazy" decoding="async">
                <span class="lp-cmp__ico" aria-hidden="true"><i class="fas fa-credit-card"></i></span>
                <h3>@lang('site.lp_cmp_p_t')</h3>
                <p>@lang('site.lp_cmp_p_x')</p>
            </article>
        </div>
        <ul class="lp-cmp__common lp-reveal">
            @foreach (['site.lp_cmp_b1', 'site.lp_cmp_b2', 'site.lp_cmp_b3'] as $b)
            <li><i class="fas fa-check-circle" aria-hidden="true"></i> @lang($b)</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ═════════ 7. Questions fréquentes (objections) ═════════ --}}
<section class="lp-section lp-faq" aria-labelledby="lp-faq-title" x-data="{ open: 1 }">
    <div class="bo-wrap lp-faq__wrap">
        <h2 id="lp-faq-title" class="lp-h2 lp-h2--center lp-reveal">@lang('site.home_faq_title')</h2>
        @foreach ([1, 2, 3, 4, 5] as $q)
        <div class="lp-faq__item lp-reveal" :class="{ 'is-open': open === {{ $q }} }">
            <h3>
                <button type="button" class="lp-faq__q" id="lp-faq-q{{ $q }}" aria-controls="lp-faq-a{{ $q }}" :aria-expanded="open === {{ $q }}" @click="open = open === {{ $q }} ? 0 : {{ $q }}">
                    <span>@lang('site.home_faq_' . $q . '_q')</span>
                    <i class="fas" :class="open === {{ $q }} ? 'fa-minus' : 'fa-plus'" aria-hidden="true"></i>
                </button>
            </h3>
            <div class="lp-faq__a" id="lp-faq-a{{ $q }}" role="region" aria-labelledby="lp-faq-q{{ $q }}" x-show="open === {{ $q }}" x-cloak x-transition.opacity.duration.200ms>
                <p>@lang('site.home_faq_' . $q . '_a', ['count' => $langCount])</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ═════════ 8. Appel à l'action final ═════════ --}}
<section class="lp-final" aria-labelledby="lp-final-title" style="--lp-final-img:url('{{ asset('assets/images/site/about-1.jpg') }}')">
    <div class="bo-wrap lp-final__in lp-reveal">
        <div>
            <h2 id="lp-final-title">@lang('site.lp_final_t')</h2>
            <p>@lang('site.lp_final_x')</p>
        </div>
        <div class="lp-final__cta">
            <a href="{{ $signup }}" class="lp-btn lp-btn--primary lp-btn--lg" data-lp-cta="final">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('contact', ['locale' => $locale]) }}" class="lp-btn lp-btn--ghost lp-btn--lg">@lang('menu.contact')</a>
        </div>
    </div>
</section>

{{-- Barre d'action collante (mobile) : apparaît après le hero --}}
<div class="lp-sticky" id="lp-sticky" aria-hidden="true">
    <a href="{{ $signup }}" class="lp-btn lp-btn--primary" tabindex="-1" data-lp-cta="sticky">@lang('site.lp_cta') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.documentElement;

  /* Apparition progressive au défilement (état final immédiat si mouvement réduit) */
  var items = [].slice.call(document.querySelectorAll('.lp-reveal'));
  if (reduce || !('IntersectionObserver' in window)) {
    items.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target, i = items.indexOf(el);
        el.style.transitionDelay = Math.min((i % 6) * 70, 350) + 'ms';
        el.classList.add('is-in');
        io.unobserve(el);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    items.forEach(function (el) { io.observe(el); });
  }

  /* Compteurs : le texte final est déjà dans le HTML ; l'animation ne fait que l'accompagner */
  var counters = [].slice.call(document.querySelectorAll('[data-lp-count]'));
  if (!reduce && 'IntersectionObserver' in window) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target, to = parseInt(el.dataset.lpCount, 10), suf = el.dataset.suffix || '', t0 = null, d = 1100;
        co.unobserve(el);
        (function step(ts) {
          t0 = t0 || ts;
          var p = Math.min((ts - t0) / d, 1), eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(to * eased) + suf;
          if (p < 1) requestAnimationFrame(step);
        })(performance.now());
      });
    }, { threshold: 0.6 });
    counters.forEach(function (el) { co.observe(el); });
  }

  /* Carte Visa : légère inclinaison au pointeur (souris uniquement) */
  var card = document.querySelector('[data-lp-tilt]');
  if (card && !reduce && window.matchMedia('(pointer: fine)').matches) {
    var host = card.parentNode, raf = null;
    host.addEventListener('pointermove', function (ev) {
      var r = host.getBoundingClientRect(), x = (ev.clientX - r.left) / r.width - 0.5, y = (ev.clientY - r.top) / r.height - 0.5;
      if (raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function () {
        card.style.setProperty('--ry', (x * 14).toFixed(2) + 'deg');
        card.style.setProperty('--rx', (-y * 10).toFixed(2) + 'deg');
        card.style.setProperty('--mx', (50 + x * 60) + '%');
        card.style.setProperty('--my', (50 + y * 60) + '%');
      });
    });
    host.addEventListener('pointerleave', function () {
      card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg');
    });
  }

  /* Parallaxe douce (arrière-plan du hero, photo) + barre de progression de lecture */
  var par = [].slice.call(document.querySelectorAll('[data-lp-parallax]')), bar = document.getElementById('lp-progress'), tick = false;
  function onScroll() {
    tick = false;
    var y = window.pageYOffset, h = document.documentElement.scrollHeight - window.innerHeight;
    if (bar) bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(y / h, 1) : 0) + ')';
    if (reduce) return;
    par.forEach(function (el) {
      var r = el.parentNode.getBoundingClientRect();
      if (r.bottom < -200 || r.top > window.innerHeight + 200) return;
      el.style.setProperty('--py', (y * parseFloat(el.dataset.lpParallax)).toFixed(1) + 'px');
    });
  }
  window.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();

  /* Barre collante : visible une fois le hero dépassé */
  var sticky = document.getElementById('lp-sticky'), hero = document.getElementById('top');
  if (sticky && hero && 'IntersectionObserver' in window) {
    var so = new IntersectionObserver(function (entries) {
      var show = !entries[0].isIntersecting;
      sticky.classList.toggle('is-on', show);
      sticky.setAttribute('aria-hidden', show ? 'false' : 'true');
      var a = sticky.querySelector('a'); if (a) a.tabIndex = show ? 0 : -1;
    }, { threshold: 0 });
    so.observe(hero);
  }
})();
</script>
@endpush
