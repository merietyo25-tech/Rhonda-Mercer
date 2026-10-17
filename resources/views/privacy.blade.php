@extends('layouts.app')
@section('title', __('menu.privacy'))

@section('content')
@php $locale = app()->getLocale(); @endphp

@include('partials.page-hero', ['title' => __('menu.privacy'), 'image' => 'contact-hero.jpg'])

<section class="lg-section">
    <div class="bo-wrap lg-grid">
        <div class="legal-content lg-content">
                <h2>{{ __('privacy.introduction_title') }}</h2>
                <p>{!! __('privacy.introduction_text') !!}</p>

                <h2>{{ __('privacy.information_collection_title') }}</h2>
                <p>{!! __('privacy.information_collection_text') !!}</p>
                <ul>
                    @foreach (__('privacy.information_collection_list') as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <h2>{{ __('privacy.information_use_title') }}</h2>
                <p>{!! __('privacy.information_use_text') !!}</p>
                <ul>
                    @foreach (__('privacy.information_use_list') as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <h2>{{ __('privacy.information_sharing_title') }}</h2>
                <p>{!! __('privacy.information_sharing_text') !!}</p>
                <ul>
                    @foreach (__('privacy.information_sharing_list') as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <h2>{{ __('privacy.information_security_title') }}</h2>
                <p>{!! __('privacy.information_security_text') !!}</p>

                <h2>{{ __('privacy.your_rights_title') }}</h2>
                <p>{!! __('privacy.your_rights_text') !!}</p>
                <ul>
                    @foreach (__('privacy.your_rights_list') as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <p>{!! __('privacy.your_rights_contact') !!}</p>

                <h2>{{ __('privacy.policy_updates_title') }}</h2>
                <p>{!! __('privacy.policy_updates_text') !!}</p>

                <h2>{{ __('privacy.contact_title') }}</h2>
                <p>{!! __('privacy.contact_text') !!}</p>
                <p>
                    @foreach (__('privacy.contact_details') as $line)
                    {{ $line }}<br>
                    @endforeach
                </p>

        </div>
            <aside class="lg-aside">
                <figure class="lg-aside__photo"><img src="{{ asset('assets/images/site/about-1.jpg') }}" alt="" width="640" height="420" loading="lazy" decoding="async"></figure>
                <div class="lg-aside__card">
                    <h2>@lang('site.about_contact')</h2>
                    <p>@lang('site.about_contact_text')</p>
                    <a href="{{ route('contact', ['locale' => $locale]) }}" class="bo-btn bo-btn--green">@lang('menu.contact') <i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                </div>
            </aside>
    </div>
</section>

@endsection
