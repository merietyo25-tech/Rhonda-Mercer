@php $locale = $locale ?? app()->getLocale(); @endphp
{{-- Bandeau de page commun : photo, voile clair, titre vert et fil d'Ariane (pages internes du site public) --}}
<section class="ab-hero" style="background-image:url('{{ asset('assets/images/site/' . ($image ?? 'about-1.jpg')) }}')">
    <div class="ab-hero__shade"></div>
    <div class="bo-wrap ab-hero__in">
        <nav class="ab-crumb" aria-label="breadcrumb">
            <a href="{{ route('home', ['locale' => $locale]) }}"><i class="fas fa-home" aria-hidden="true"></i> @lang('menu.home')</a>
            <span aria-hidden="true">/</span> <strong>{{ $title }}</strong>
        </nav>
        <h1>{{ $title }}</h1>
    </div>
</section>
