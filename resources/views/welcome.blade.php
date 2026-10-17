@extends('layouts.app')
@section('title', __('menu.home'))

@section('content')
@php
    $locale  = app()->getLocale();
    $contact = \App\Models\SiteContact::current();
    $phone   = $contact->phone_1;
    $address = site_address();

    $slides = [
        ['img' => 'hero-1.jpg',       'lines' => ['site.hero1_a', 'site.hero1_b', 'site.hero1_c']],
        ['img' => 'hero-2.jpg',  'lines' => ['site.hero2_a', 'site.hero2_b', 'site.hero2_c']],
    ];

    $langCount = \App\Models\Language::enabledList()->count();
    $features = [
        ['icon' => 'fa-university',     'n' => 1],
        ['icon' => 'fa-credit-card',    'n' => 2],
        ['icon' => 'fa-exchange-alt',   'n' => 3],
        ['icon' => 'fa-file-invoice',   'n' => 4],
        ['icon' => 'fa-shield-alt',     'n' => 5],
        ['icon' => 'fa-language',       'n' => 6],
    ];
    $steps = [
        ['icon' => 'fa-user-plus',      'n' => 1],
        ['icon' => 'fa-id-card',        'n' => 2],
        ['icon' => 'fa-credit-card',    'n' => 3],
        ['icon' => 'fa-paper-plane',    'n' => 4],
    ];

    // Les cinq accès rapides « Quel est votre besoin ? »
    $needs = [
        ['icon' => 'fa-id-card',          'label' => 'site.need_open',     'route' => 'signup'],
        ['icon' => 'fa-university', 'label' => 'site.need_account',  'route' => 'signup'],
        ['icon' => 'fa-credit-card',      'label' => 'site.need_card',     'route' => 'signup'],
        ['icon' => 'fa-exchange-alt', 'label' => 'site.need_transfer', 'route' => 'signup'],
        ['icon' => 'fa-headset',          'label' => 'site.need_contact',  'route' => 'contact'],
    ];
@endphp

{{-- ── Bannière à diapositives ─────────────────────────────────────────── --}}
<section class="bo-hero" x-data="{ i: 0, n: {{ count($slides) }}, t: null,
        go(k){ this.i = (k + this.n) % this.n; this.restart(); },
        restart(){ clearInterval(this.t); if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; this.t = setInterval(() => this.i = (this.i + 1) % this.n, 6500); } }"
        x-init="restart()" @mouseenter="clearInterval(t)" @mouseleave="restart()" @focusin="clearInterval(t)" @focusout="restart()"
        role="region" aria-roledescription="carousel" aria-label="{{ site_name() }}">
    @foreach ($slides as $k => $slide)
    <div class="bo-hero__slide {{ $k === 0 ? 'is-on' : '' }}" :class="{ 'is-on': i === {{ $k }} }"
         style="background-image:url('{{ asset('assets/images/site/' . $slide['img']) }}')">
        <div class="bo-hero__shade"></div>
        <div class="bo-wrap bo-hero__text">
            @foreach ($slide['lines'] as $n => $line)
            <h2 class="bo-hero__line bo-hero__line--{{ $n % 2 === 0 ? 'indigo' : 'grey' }}">@lang($line)</h2>
            @endforeach
        </div>
    </div>
    @endforeach

    <button type="button" class="bo-hero__arrow bo-hero__arrow--prev" @click="go(i - 1)" aria-label="@lang('site.prev')"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="bo-hero__arrow bo-hero__arrow--next" @click="go(i + 1)" aria-label="@lang('site.next')"><i class="fas fa-chevron-right"></i></button>
    <div class="bo-hero__dots">
        @foreach ($slides as $k => $slide)
        <button type="button" :class="{ 'is-on': i === {{ $k }} }" @click="go({{ $k }})" aria-label="{{ $k + 1 }}"></button>
        @endforeach
    </div>
</section>

{{-- Bandeau d'accès rapide fixé au bord droit --}}
<aside class="bo-side" aria-label="@lang('menu.contact')">
    @if($phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="bo-side__a bo-side__a--green" aria-label="{{ $phone }}"><i class="fas fa-phone-volume"></i></a>@endif
    <a href="#agence" class="bo-side__a bo-side__a--black" aria-label="@lang('site.find_title')"><i class="fas fa-map-marker-alt"></i></a>
    @if($contact->email)<a href="mailto:{{ $contact->email }}" class="bo-side__a bo-side__a--indigo" aria-label="{{ $contact->email }}"><i class="fas fa-envelope"></i></a>@endif
</aside>

{{-- ── Quel est votre besoin ? ─────────────────────────────────────────── --}}
<section class="bo-section bo-needs">
    <div class="bo-wrap">
        <h2 class="bo-title">@lang('site.need_title')</h2>
        <div class="bo-needs__row">
            @foreach ($needs as $need)
            <a href="{{ route($need['route'], ['locale' => $locale]) }}" class="bo-need">
                <span class="bo-need__ico"><i class="fas {{ $need['icon'] }}"></i></span>
                <span class="bo-need__lbl">@lang($need['label'])</span>
            </a>
            @endforeach
        </div>

        <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-cta-banner">
            <span class="bo-cta-banner__txt"><strong>@lang('site.cta_title')</strong> <em>@lang('site.cta_light')</em></span>
            <span class="bo-cta-banner__ico">
                <i class="fas fa-id-card"></i><i class="fas fa-credit-card"></i><i class="fas fa-mobile-alt"></i><i class="fas fa-bolt"></i><i class="fas fa-shield-alt"></i>
            </span>
        </a>
    </div>
</section>

{{-- ── L'application en images ─────────────────────────────────────────── --}}
<section class="hm-app">
    <div class="bo-wrap hm-app__grid">
        <div class="hm-app__txt">
            <h2 class="ab-h2 ab-h2--light">@lang('site.home_app_title')</h2>
            <p class="hm-app__lead">@lang('site.home_app_text')</p>
            <ul class="hm-app__list">
                @foreach (['site.home_app_b1', 'site.home_app_b2', 'site.home_app_b3', 'site.home_app_b4'] as $b)
                <li><i class="fas fa-check"></i> @lang($b)</li>
                @endforeach
            </ul>
            <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-btn bo-btn--green">@lang('site.become_client') <i class="fas fa-chevron-right"></i></a>
        </div>
        <div class="hm-phones" aria-label="@lang('site.home_app_title')">
            <figure class="hm-phone hm-phone--side">
                <div class="hm-phone__frame"><img src="{{ asset('assets/images/app/screen-cards.png') }}" alt="@lang('site.home_app_cap2')" loading="lazy"></div>
                <figcaption>@lang('site.home_app_cap2')</figcaption>
            </figure>
            <figure class="hm-phone hm-phone--main">
                <div class="hm-phone__frame"><img src="{{ asset('assets/images/app/screen-home.png') }}" alt="@lang('site.home_app_cap1')" loading="lazy"></div>
                <figcaption>@lang('site.home_app_cap1')</figcaption>
            </figure>
            <figure class="hm-phone hm-phone--side">
                <div class="hm-phone__frame"><img src="{{ asset('assets/images/app/screen-transfers.png') }}" alt="@lang('site.home_app_cap3')" loading="lazy"></div>
                <figcaption>@lang('site.home_app_cap3')</figcaption>
            </figure>
        </div>
    </div>
</section>

{{-- ── Fonctionnalités détaillées ──────────────────────────────────────── --}}
<section class="bo-section hm-feat">
    <div class="bo-wrap">
        <h2 class="bo-title">@lang('site.home_feat_title')</h2>
        <div class="hm-feat__grid">
            @foreach ($features as $f)
            <article class="hm-feat__card">
                <span class="hm-feat__ico"><i class="fas {{ $f['icon'] }}"></i></span>
                <h3>@lang('site.home_feat_' . $f['n'] . '_t')</h3>
                <p>@lang('site.home_feat_' . $f['n'] . '_x', ['count' => $langCount])</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Deux cartes promotionnelles ─────────────────────────────────────── --}}
<section class="bo-section bo-promos">
    <div class="bo-wrap bo-promos__grid">
        <article class="bo-promo">
            <div class="bo-promo__img" style="background-image:url('{{ asset('assets/images/site/promo-card.jpg') }}')"></div>
            <div class="bo-promo__body">
                <span class="bo-promo__label">@lang('site.promo1_label')</span>
                <h3>@lang('site.promo1_title')</h3>
                <p>@lang('site.promo1_text')</p>
                <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-link">@lang('site.discover') <i class="fas fa-chevron-right"></i></a>
            </div>
        </article>
        <article class="bo-promo">
            <div class="bo-promo__img bo-promo__img--phone"><i class="fas fa-mobile-alt"></i></div>
            <div class="bo-promo__body">
                <span class="bo-promo__label">@lang('site.promo2_label')</span>
                <h3>@lang('site.promo2_title')</h3>
                <p>@lang('site.promo2_text')</p>
                <a href="{{ route('login') }}" class="bo-link">@lang('site.discover') <i class="fas fa-chevron-right"></i></a>
            </div>
        </article>
    </div>
</section>

{{-- ── Comment ça marche ───────────────────────────────────────────────── --}}
<section class="bo-section hm-steps">
    <div class="bo-wrap">
        <h2 class="bo-title">@lang('site.home_steps_title')</h2>
        <ol class="hm-steps__row">
            @foreach ($steps as $st)
            <li class="hm-step">
                <span class="hm-step__num">{{ $st['n'] }}</span>
                <span class="hm-step__ico"><i class="fas {{ $st['icon'] }}"></i></span>
                <h3>@lang('site.home_step_' . $st['n'] . '_t')</h3>
                <p>@lang('site.home_step_' . $st['n'] . '_x')</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ── Trouver mon agence ──────────────────────────────────────────────── --}}
@if($address !== '')
<section class="bo-agency" id="agence">
    <iframe title="{{ site_name() }}" loading="lazy" referrerpolicy="no-referrer"
            src="https://www.openstreetmap.org/export/embed.html?bbox=13.3836%2C52.5113%2C13.3956%2C52.5173&amp;layer=mapnik&amp;marker=52.5143%2C13.3896"></iframe>
    <div class="bo-agency__tab">@lang('site.find_title')</div>
    <div class="bo-agency__panel">
        <strong>{{ site_name() }}</strong>
        <p><i class="fas fa-map-marker-alt"></i> {{ $address }}</p>
        @if($phone)<p><i class="fas fa-phone"></i> <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a></p>@endif
        @if($contact->email)<p><i class="fas fa-envelope"></i> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>@endif
        <a href="{{ route('contact', ['locale' => $locale]) }}" class="bo-link bo-link--dark">@lang('menu.contact') <i class="fas fa-chevron-right"></i></a>
    </div>
</section>
@endif

{{-- ── Gérez vos comptes avec votre téléphone ──────────────────────────── --}}
<section class="bo-section bo-manage">
    <div class="bo-wrap bo-manage__grid">
        <div>
            <h2 class="bo-title bo-title--left">@lang('site.manage_title')</h2>
            <ul class="bo-checks">
                @foreach (['site.manage_1', 'site.manage_2', 'site.manage_3', 'site.manage_4'] as $m)
                <li><i class="fas fa-check"></i> @lang($m)</li>
                @endforeach
            </ul>
            <a href="{{ route('login') }}" class="bo-btn bo-btn--green">@lang('site.manage_btn') <i class="fas fa-chevron-right"></i></a>
        </div>
        <div class="bo-manage__img" style="background-image:url('{{ asset('assets/images/site/manage-phone.jpg') }}')"></div>
    </div>
</section>
{{-- ── Questions fréquentes ────────────────────────────────────────────── --}}
<section class="bo-section hm-faq" x-data="{ open: 1 }">
    <div class="bo-wrap hm-faq__wrap">
        <h2 class="bo-title">@lang('site.home_faq_title')</h2>
        @foreach ([1, 2, 3, 4, 5] as $q)
        <div class="hm-faq__item" :class="{ 'is-open': open === {{ $q }} }">
            <button type="button" class="hm-faq__q" @click="open = open === {{ $q }} ? 0 : {{ $q }}" :aria-expanded="open === {{ $q }}">
                <span>@lang('site.home_faq_' . $q . '_q')</span>
                <i class="fas" :class="open === {{ $q }} ? 'fa-minus' : 'fa-plus'"></i>
            </button>
            <div class="hm-faq__a" x-show="open === {{ $q }}" x-cloak x-transition>
                <p>@lang('site.home_faq_' . $q . '_a', ['count' => $langCount])</p>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection
