@extends('layouts.app')
@section('title', __('menu.terms'))

@section('content')
@php $locale = app()->getLocale(); @endphp

@include('partials.page-hero', ['title' => __('menu.terms'), 'image' => 'about-1.jpg'])

<section class="lg-section">
    <div class="bo-wrap lg-grid">
        <div class="legal-content lg-content">
            @foreach (__('terms.sections') as $section)
            <h2>{{ $section['title'] }}</h2>
            <p>{!! $section['content'] !!}</p>
            @endforeach
        </div>
            <aside class="lg-aside">
                <figure class="lg-aside__photo"><img src="{{ asset('assets/images/site/contact-hero.jpg') }}" alt="" width="640" height="420" loading="lazy" decoding="async"></figure>
                <div class="lg-aside__card">
                    <h2>@lang('site.about_contact')</h2>
                    <p>@lang('site.about_contact_text')</p>
                    <a href="{{ route('contact', ['locale' => $locale]) }}" class="bo-btn bo-btn--green">@lang('menu.contact') <i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                </div>
            </aside>
    </div>
</section>

@endsection
