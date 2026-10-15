@extends('layouts.app')
@section('title', __('menu.contact'))

@push('styles')
<style>[x-cloak]{display:none !important}</style>
@endpush

@section('content')
@php
    $locale = app()->getLocale();
    $siteContact = \App\Models\SiteContact::current();
    $socialLinks = \App\Models\SocialLink::where('is_visible', true)->orderBy('sort_order')->get();
    $addresses = collect([$siteContact->address_1, $siteContact->address_2, $siteContact->address_3])->filter();
    $phones    = collect([$siteContact->phone_1, $siteContact->phone_2])->filter();
    $subjects  = [
        'Ouverture de compte' => 'menu.banking_account',
        'Carte bancaire'      => 'menu.banking_card',
        'Virements'           => 'menu.banking_transfer',
        'Autre'               => 'site.contact_other',
    ];
@endphp

{{-- ── Bandeau ─────────────────────────────────────────────────────── --}}
<section class="ab-hero ct-hero" style="background-image:url('{{ asset('assets/images/site/contact-hero.jpg') }}')">
    <div class="ab-hero__shade"></div>
    <div class="bo-wrap ab-hero__in">
        <nav class="ab-crumb" aria-label="breadcrumb">
            <a href="{{ route('home', ['locale' => $locale]) }}"><i class="fas fa-home"></i> @lang('menu.home')</a>
            <span>/</span> <strong>@lang('menu.contact')</strong>
        </nav>
        <h1>@lang('contact.banner_title')</h1>
        <p class="ct-hero__sub">@lang('contact.banner_description')</p>
    </div>
</section>

{{-- ── Formulaire + coordonnées ────────────────────────────────────── --}}
<section class="ct-main">
    <div class="bo-wrap ct-grid">

        <div class="ct-form" x-data="{ submitting: false }">
            <span class="ab-kicker">@lang('contact.form_title')</span>
            <h2 class="ab-h2">@lang('contact.detail_title')</h2>
            <p class="ct-form__lead">@lang('contact.form_description')</p>

            @if (session('success'))
                <div class="ct-alert ct-alert--ok" role="status"><i class="fas fa-check-circle"></i> {{ __('message.success_contact') }}</div>
            @endif
            @if (session('error'))
                <div class="ct-alert ct-alert--err" role="alert"><i class="fas fa-exclamation-triangle"></i> {{ __('message.error') }}</div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}" @submit="submitting = true">
                @csrf
                <div class="ct-row">
                    <div class="ct-field">
                        <label for="ct-name">{{ __('contact.label_name') }} *</label>
                        <input id="ct-name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('contact.placeholder_name') }}" autocomplete="name" required>
                        @error('name')<span class="ct-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ct-field">
                        <label for="ct-email">{{ __('contact.label_email') }} *</label>
                        <input id="ct-email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('contact.placeholder_email') }}" autocomplete="email" required>
                        @error('email')<span class="ct-err">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="ct-field">
                    <label for="ct-subject">{{ __('contact.subject') }} *</label>
                    <select id="ct-subject" name="subject" required>
                        <option value="">— {{ __('contact.subject') }} —</option>
                        @foreach ($subjects as $value => $label)
                        <option value="{{ $value }}" {{ old('subject') == $value ? 'selected' : '' }}>@lang($label)</option>
                        @endforeach
                    </select>
                    @error('subject')<span class="ct-err">{{ $message }}</span>@enderror
                </div>
                <div class="ct-field">
                    <label for="ct-message">{{ __('contact.label_message') }} *</label>
                    <textarea id="ct-message" name="message" rows="6" placeholder="{{ __('contact.placeholder_message') }}" required>{{ old('message') }}</textarea>
                    @error('message')<span class="ct-err">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="ct-submit" :disabled="submitting">
                    <i class="fas fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                    <i class="fas fa-paper-plane" x-show="!submitting"></i>
                    <span x-text="submitting ? @js(__('contact.sending')) : @js(__('contact.button'))"></span>
                </button>
            </form>
        </div>

        <aside class="ct-side">
            @if ($addresses->isNotEmpty())
            <div class="ct-card">
                <span class="ct-card__ico"><i class="fas fa-map-marker-alt"></i></span>
                <div>
                    <h3>@lang('contact.address_title')</h3>
                    @foreach ($addresses as $adresse)<p>{{ $adresse }}</p>@endforeach
                </div>
            </div>
            @endif

            @if ($phones->isNotEmpty())
            <div class="ct-card">
                <span class="ct-card__ico"><i class="fas fa-phone-alt"></i></span>
                <div>
                    <h3>@lang('contact.phone_title')</h3>
                    @foreach ($phones as $telephone)
                    <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $telephone) }}">{{ $telephone }}</a></p>
                    @endforeach
                </div>
            </div>
            @endif

            @if ($siteContact->email)
            <div class="ct-card">
                <span class="ct-card__ico"><i class="fas fa-envelope"></i></span>
                <div>
                    <h3>@lang('contact.mail_title')</h3>
                    <p><a href="mailto:{{ $siteContact->email }}">{{ $siteContact->email }}</a></p>
                </div>
            </div>
            @endif

            <div class="ct-help">
                <i class="fas fa-headset"></i>
                <div>
                    <strong>{{ $siteContact->name }}</strong>
                    <p>@lang('contact.detail_desc')</p>
                </div>
            </div>

            @if ($socialLinks->isNotEmpty())
            <div class="ct-social">
                @foreach ($socialLinks as $link)
                <a href="{{ $link->url }}" aria-label="{{ $link->label }}" @if(str_starts_with($link->url, 'http')) target="_blank" rel="noopener" @endif><i class="{{ $link->icon_class }}"></i></a>
                @endforeach
            </div>
            @endif
        </aside>
    </div>
</section>

{{-- ── Localisation ────────────────────────────────────────────────── --}}
@if ($addresses->isNotEmpty())
<section class="ct-map">
    <iframe title="{{ $siteContact->name }}" loading="lazy" referrerpolicy="no-referrer"
            src="https://www.openstreetmap.org/export/embed.html?bbox=13.3836%2C52.5113%2C13.3956%2C52.5173&amp;layer=mapnik&amp;marker=52.5143%2C13.3896"></iframe>
    <div class="ct-map__tab">@lang('site.about_where')</div>
</section>
@endif
@endsection
