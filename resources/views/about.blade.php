@extends('layouts.app')
@section('title', __('site.about_title'))

@section('content')
@php
    $locale  = app()->getLocale();
    $name    = site_name();
    $contact = \App\Models\SiteContact::current();
    $address = site_address();
    $members = $members ?? collect();
    $leaders = $members->where('is_leader', true)->values();
    $others  = $members->where('is_leader', false)->values();

    $values = [
        ['icon' => 'fa-eye',        't' => 'site.about_v1_t', 'x' => 'site.about_v1_x'],
        ['icon' => 'fa-shield-alt', 't' => 'site.about_v2_t', 'x' => 'site.about_v2_x'],
        ['icon' => 'fa-mobile-alt', 't' => 'site.about_v3_t', 'x' => 'site.about_v3_x'],
        ['icon' => 'fa-handshake',  't' => 'site.about_v4_t', 'x' => 'site.about_v4_x'],
    ];
    $highlights = [
        ['icon' => 'fa-university',   'label' => 'menu.banking_account'],
        ['icon' => 'fa-credit-card',  'label' => 'menu.banking_card'],
        ['icon' => 'fa-exchange-alt', 'label' => 'menu.banking_transfer'],
    ];
@endphp

{{-- ── Bandeau ─────────────────────────────────────────────────────── --}}
<section class="ab-hero" style="background-image:url('{{ asset('assets/images/site/about-1.jpg') }}')">
    <div class="ab-hero__shade"></div>
    <div class="bo-wrap ab-hero__in">
        <nav class="ab-crumb" aria-label="breadcrumb">
            <a href="{{ route('home', ['locale' => $locale]) }}"><i class="fas fa-home"></i> @lang('menu.home')</a>
            <span>/</span> <strong>@lang('site.about_title')</strong>
        </nav>
        <h1>@lang('site.about_banner')</h1>
    </div>
</section>

{{-- ── Introduction ────────────────────────────────────────────────── --}}
<section class="ab-intro">
    <div class="bo-wrap">
        <span class="ab-kicker">@lang('site.about_title')</span>
        <p class="ab-lead">@lang('site.about_intro_1', ['name' => $name])</p>
        <p class="ab-sub">@lang('site.about_intro_2')</p>
        <div class="ab-pills">
            @foreach ($highlights as $h)
            <span class="ab-pill"><i class="fas {{ $h['icon'] }}"></i> @lang($h['label'])</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Découvrez la banque ─────────────────────────────────────────── --}}
<section class="ab-section">
    <div class="bo-wrap ab-split">
        <div class="ab-text">
            <h2 class="ab-h2">@lang('site.about_discover', ['name' => $name])</h2>
            <p class="ab-p ab-p--first">@lang('site.about_p1', ['name' => $name])</p>
            <p class="ab-p">@lang('site.about_p2')</p>
            <ul class="ab-checks">
                <li><i class="fas fa-check"></i> @lang('site.about_li1')</li>
                <li><i class="fas fa-check"></i> @lang('site.about_li2')</li>
                <li><i class="fas fa-check"></i> @lang('site.about_li3')</li>
            </ul>
            <p class="ab-p">@lang('site.about_p3')</p>
        </div>
        <figure class="ab-photo">
            <img src="{{ asset('assets/images/site/about-2.jpg') }}" alt="{{ $name }}" loading="lazy">
            <figcaption>@lang('site.about_caption', ['name' => $name])</figcaption>
        </figure>
    </div>
</section>

{{-- ── Engagements ─────────────────────────────────────────────────── --}}
<section class="ab-values">
    <div class="bo-wrap">
        <h2 class="ab-h2 ab-h2--light">@lang('site.about_values')</h2>
        <div class="ab-values__grid">
            @foreach ($values as $n => $v)
            <article class="ab-value">
                <span class="ab-value__num">0{{ $n + 1 }}</span>
                <span class="ab-value__ico"><i class="fas {{ $v['icon'] }}"></i></span>
                <h3>@lang($v['t'])</h3>
                <p>@lang($v['x'])</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Direction ───────────────────────────────────────────────────── --}}
@foreach ($leaders as $i => $l)
<section class="ab-section">
    <div class="bo-wrap">
        @if($i === 0)<h2 class="ab-h2">@lang('site.about_leader')</h2>@endif
        <div class="ab-leader">
            <div class="ab-leader__pic">
                @if($l->photoUrl())
                    <img src="{{ $l->photoUrl() }}" alt="{{ $l->name }}" loading="lazy">
                @else
                    <span class="ab-leader__ph">{{ $l->initials() }}</span>
                @endif
            </div>
            <div class="ab-leader__txt">
                <i class="fas fa-quote-left ab-quote"></i>
                @foreach (preg_split('/\R+/', trim((string) $l->displayBio())) as $para)
                    @if($para !== '')<p>{{ $para }}</p>@endif
                @endforeach
                <div class="ab-leader__sign">
                    <strong>{{ $l->name }}</strong>
                    <span>{{ $l->displayTitle() }}</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endforeach

{{-- ── Membres acteurs ─────────────────────────────────────────────── --}}
@if($others->isNotEmpty())
<section class="ab-section ab-team">
    <div class="bo-wrap">
        <h2 class="ab-h2">@lang('site.about_team', ['name' => $name])</h2>
        <p class="ab-team__lead">@lang('site.about_gov_text', ['name' => $name, 'count' => $members->count()])</p>
        <div class="ab-team__grid">
            @foreach ($others as $m)
            <article class="ab-member">
                <div class="ab-member__av" @if($m->photoUrl()) style="background-image:url('{{ $m->photoUrl() }}')" @endif>
                    @unless($m->photoUrl()){{ $m->initials() }}@endunless
                </div>
                <h3>{{ $m->name }}</h3>
                <span class="ab-member__role">{{ $m->displayTitle() }}</span>
                @if($m->displayBio())<p>{{ \Illuminate\Support\Str::limit($m->displayBio(), 190) }}</p>@endif
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── Où nous trouver ─────────────────────────────────────────────── --}}
@if($address !== '')
<section class="ab-where">
    <div class="ab-where__card">
        <h2 class="ab-h2">@lang('site.about_where')</h2>
        <p><i class="fas fa-map-marker-alt"></i> <span>{{ $address }}</span></p>
        @if($contact->phone_1)<p><i class="fas fa-phone"></i> <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact->phone_1) }}">{{ $contact->phone_1 }}</a></p>@endif
        @if($contact->email)<p><i class="fas fa-envelope"></i> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>@endif
        <a href="{{ route('contact', ['locale' => $locale]) }}" class="bo-btn bo-btn--green">@lang('menu.contact') <i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="ab-where__map">
        <iframe title="{{ $name }}" loading="lazy" referrerpolicy="no-referrer"
                src="https://www.openstreetmap.org/export/embed.html?bbox=13.3836%2C52.5113%2C13.3956%2C52.5173&amp;layer=mapnik&amp;marker=52.5143%2C13.3896"></iframe>
    </div>
</section>
@endif

{{-- ── Découvrez aussi ─────────────────────────────────────────────── --}}
<section class="ab-section ab-also">
    <div class="bo-wrap">
        <h2 class="ab-h2 ab-h2--center">@lang('site.about_also')</h2>
        <div class="bo-promos__grid">
            <article class="bo-promo">
                <div class="bo-promo__img" style="background-image:url('{{ asset('assets/images/site/promo-card.jpg') }}')"></div>
                <div class="bo-promo__body">
                    <span class="bo-promo__label">@lang('site.about_c1_label')</span>
                    <h3>@lang('site.about_c1_title')</h3>
                    <p>@lang('site.about_c1_text')</p>
                    <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-link">@lang('site.discover') <i class="fas fa-chevron-right"></i></a>
                </div>
            </article>
            <article class="bo-promo">
                <div class="bo-promo__img" style="background-image:url('{{ asset('assets/images/site/manage-phone.jpg') }}')"></div>
                <div class="bo-promo__body">
                    <span class="bo-promo__label">@lang('site.about_c2_label')</span>
                    <h3>@lang('site.about_c2_title')</h3>
                    <p>@lang('site.about_c2_text')</p>
                    <a href="{{ route('login') }}" class="bo-link">@lang('site.discover') <i class="fas fa-chevron-right"></i></a>
                </div>
            </article>
        </div>
    </div>
</section>

{{-- ── Contact ─────────────────────────────────────────────────────── --}}
<section class="bo-contactband">
    <div class="bo-wrap">
        <h2>@lang('site.about_contact')</h2>
        <p>@lang('site.about_contact_text')</p>
        <a href="{{ route('contact', ['locale' => $locale]) }}" class="bo-btn bo-btn--green">@lang('menu.contact') <i class="fas fa-chevron-right"></i></a>
    </div>
</section>
@endsection
