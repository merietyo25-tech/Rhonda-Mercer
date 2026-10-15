@php
    $locale = app()->getLocale(); $route = Route::currentRouteName() ?? 'home';
    $siteContact = \App\Models\SiteContact::current();
    $languages = \App\Models\Language::enabledList();
    $current   = $languages->firstWhere('code', $locale);
    $links = [
        ['route' => 'home',    'label' => 'menu.home',    'icon' => 'fa-home'],
        ['route' => 'about',   'label' => 'menu.about',   'icon' => 'fa-university'],
        ['route' => 'contact', 'label' => 'menu.contact', 'icon' => 'fa-envelope'],
    ];
@endphp

<header id="site-header" class="bo-header" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">
    <div class="bo-header__bar">

        {{-- Menu + accès rapide --}}
        <div class="bo-header__left">
            <button type="button" class="bo-burger" @click="drawer = true" aria-label="@lang('menu.menu')">
                <span></span><span></span><span></span>
            </button>
            <nav class="bo-nav" aria-label="Navigation principale">
                @foreach ($links as $link)
                <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="bo-nav__link {{ $route === $link['route'] ? 'is-active' : '' }}">@lang($link['label'])</a>
                @endforeach
            </nav>
        </div>

        {{-- Logo centré --}}
        <a href="{{ route('home', ['locale' => $locale]) }}" class="bo-logo" aria-label="{{ $siteContact->name }}">
            <x-logo variant="full" theme="light" size="lg" :light="$siteContact->logo_light_path ? Storage::url($siteContact->logo_light_path) : null" :name="$siteContact->name" />
        </a>

        {{-- Langue --}}
        <div class="bo-header__right">
            <div class="bo-lang" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="bo-lang__btn" @click="open = !open" :aria-expanded="open">
                    <span>{{ strtoupper($locale) }}</span>
                    @if($current)<img src="{{ asset('images/' . $locale . '.' . $current->flag_ext) }}" alt="{{ $locale }}">@endif
                </button>
                <div class="bo-lang__menu" x-show="open" x-cloak x-transition.opacity>
                    @foreach ($languages as $lang)
                    @if ($lang->code !== $locale)
                    <a href="{{ route($route, ['locale' => $lang->code]) }}">
                        <img src="{{ asset('images/' . $lang->code . '.' . $lang->flag_ext) }}" alt="{{ $lang->code }}">
                        {{ $lang->native_name ?? strtoupper($lang->code) }}
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons accolés au bord droit --}}
    <div class="bo-cta">
        <a href="{{ route('login') }}" class="bo-cta__btn bo-cta__btn--green">@lang('site.online_bank') <i class="fas fa-chevron-right"></i></a>
        <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-cta__btn bo-cta__btn--indigo">@lang('site.become_client') <i class="fas fa-chevron-right"></i></a>
    </div>

    {{-- Tiroir de navigation --}}
    <div class="bo-drawer" x-show="drawer" x-cloak>
        <div class="bo-drawer__bg" @click="drawer = false" x-transition.opacity></div>
        <aside class="bo-drawer__panel" x-transition:enter="bo-slide-in" x-transition:enter-start="bo-slide-from" x-transition:enter-end="bo-slide-to">
            <div class="bo-drawer__head">
                <x-logo variant="full" theme="light" size="md" :light="$siteContact->logo_light_path ? Storage::url($siteContact->logo_light_path) : null" :name="$siteContact->name" />
                <button type="button" class="bo-drawer__close" @click="drawer = false" aria-label="Fermer"><i class="fas fa-times"></i></button>
            </div>
            <nav class="bo-drawer__nav">
                @foreach ($links as $link)
                <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="{{ $route === $link['route'] ? 'is-active' : '' }}"><i class="fas {{ $link['icon'] }}"></i> @lang($link['label'])</a>
                @endforeach
                <a href="{{ route('signup', ['locale' => $locale]) }}"><i class="fas fa-user-plus"></i> @lang('menu.open_account')</a>
                <a href="{{ route('terms', ['locale' => $locale]) }}"><i class="fas fa-file-alt"></i> @lang('menu.terms')</a>
                <a href="{{ route('privacy', ['locale' => $locale]) }}"><i class="fas fa-shield-alt"></i> @lang('menu.privacy')</a>
            </nav>
            <div class="bo-drawer__actions">
                <a href="{{ route('login') }}" class="bo-cta__btn bo-cta__btn--green">@lang('site.online_bank') <i class="fas fa-chevron-right"></i></a>
                <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-cta__btn bo-cta__btn--indigo">@lang('site.become_client') <i class="fas fa-chevron-right"></i></a>
            </div>
        </aside>
    </div>
</header>
