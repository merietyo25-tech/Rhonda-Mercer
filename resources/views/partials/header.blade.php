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

<header id="site-header" class="bo-header" x-data="{ drawer: false, scrolled: false }" :class="{ 'is-scrolled': scrolled }" @scroll.window.passive="scrolled = window.scrollY > 8" @keydown.escape.window="drawer = false" x-effect="document.documentElement.classList.toggle('bo-lock', drawer)">
    <div class="bo-header__bar">

        {{-- Menu + accès rapide --}}
        <div class="bo-header__left">
            <button type="button" class="bo-burger" @click="drawer = true" aria-label="@lang('menu.menu')" aria-haspopup="dialog" :aria-expanded="drawer">
                <span></span><span></span><span></span>
            </button>
            <nav class="bo-nav" aria-label="Navigation principale">
                @foreach ($links as $link)
                <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="bo-nav__link {{ $route === $link['route'] ? 'is-active' : '' }}" @if($route === $link['route']) aria-current="page" @endif><i class="fas {{ $link['icon'] }}" aria-hidden="true"></i><span>@lang($link['label'])</span></a>
                @endforeach
            </nav>
        </div>

        {{-- Logo centré --}}
        <a href="{{ route('home', ['locale' => $locale]) }}" class="bo-logo" aria-label="{{ $siteContact->name }}">
            <x-logo variant="full" theme="light" size="lg" :light="$siteContact->logo_light_path ? Storage::url($siteContact->logo_light_path) : null" :name="$siteContact->name" />
        </a>

        {{-- Langue --}}
        <div class="bo-header__right">
            <div class="bo-actions">
                <a href="{{ route('login') }}" class="bo-pill bo-pill--ghost"><i class="fas fa-lock" aria-hidden="true"></i> @lang('site.online_bank')</a>
                <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-pill bo-pill--solid">@lang('site.become_client') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="bo-lang" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="bo-lang__btn" @click="open = !open" :aria-expanded="open" aria-haspopup="true" aria-label="Language">
                    <span>{{ strtoupper($locale) }}</span>
                    @if($current)<img src="{{ asset('images/' . $locale . '.' . $current->flag_ext) }}" alt="{{ $locale }}">@endif
                </button>
                <div class="bo-lang__menu" x-show="open" x-cloak x-transition.opacity.scale.origin.top.right.duration.150ms>
                    @foreach ($languages as $lang)
                    @if ($lang->code !== $locale)
                    <a href="{{ request()->route('locale') ? route($route, ['locale' => $lang->code]) : route('lang.switch', $lang->code) }}">
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
    <div class="bo-drawer" x-show="drawer" x-cloak x-effect="if (drawer) $nextTick(() => $refs.close && $refs.close.focus())">
        <div class="bo-drawer__bg" @click="drawer = false" x-transition.opacity></div>
        <aside class="bo-drawer__panel" role="dialog" aria-modal="true" aria-label="@lang('menu.menu')" x-transition:enter="bo-slide-in" x-transition:enter-start="bo-slide-from" x-transition:enter-end="bo-slide-to" x-transition:leave="bo-slide-in" x-transition:leave-start="bo-slide-to" x-transition:leave-end="bo-slide-from">
            <div class="bo-drawer__head">
                <x-logo variant="full" theme="light" size="md" :light="$siteContact->logo_light_path ? Storage::url($siteContact->logo_light_path) : null" :name="$siteContact->name" />
                <button type="button" class="bo-drawer__close" x-ref="close" @click="drawer = false" aria-label="@lang('site.close')"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <nav class="bo-drawer__nav" aria-label="@lang('menu.menu')">
                @foreach ($links as $link)
                <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="{{ $route === $link['route'] ? 'is-active' : '' }}" @click="drawer = false"><span class="bo-drawer__ico"><i class="fas {{ $link['icon'] }}" aria-hidden="true"></i></span> @lang($link['label']) <i class="fas fa-chevron-right bo-drawer__go" aria-hidden="true"></i></a>
                @endforeach
                <a href="{{ route('signup', ['locale' => $locale]) }}"><span class="bo-drawer__ico"><i class="fas fa-user-plus" aria-hidden="true"></i></span> @lang('menu.open_account') <i class="fas fa-chevron-right bo-drawer__go" aria-hidden="true"></i></a>
                <div class="bo-drawer__sep" aria-hidden="true"></div>
                <a href="{{ route('terms', ['locale' => $locale]) }}" class="is-sub"><span class="bo-drawer__ico"><i class="fas fa-file-alt" aria-hidden="true"></i></span> @lang('menu.terms')</a>
                <a href="{{ route('privacy', ['locale' => $locale]) }}" class="is-sub"><span class="bo-drawer__ico"><i class="fas fa-shield-alt" aria-hidden="true"></i></span> @lang('menu.privacy')</a>
            </nav>
            <div class="bo-drawer__foot">
                @if (site_phone())<a href="tel:{{ preg_replace('/s+/', '', site_phone()) }}" class="bo-drawer__contact"><i class="fas fa-phone-alt" aria-hidden="true"></i> {{ site_phone() }}</a>@endif
                <div class="bo-drawer__actions">
                    <a href="{{ route('login') }}" class="bo-pill bo-pill--ghost-d"><i class="fas fa-lock" aria-hidden="true"></i> @lang('site.online_bank')</a>
                    <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-pill bo-pill--solid">@lang('site.become_client') <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </aside>
    </div>
</header>
