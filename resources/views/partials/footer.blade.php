@php
    $locale = app()->getLocale();
    $siteContact = \App\Models\SiteContact::current();
    $socialLinks = \App\Models\SocialLink::where('is_visible', true)->orderBy('sort_order')->get();
    $addresses = array_values(array_filter([$siteContact->address_1, $siteContact->address_2, $siteContact->address_3]));
    $phones    = array_values(array_filter([$siteContact->phone_1, $siteContact->phone_2]));
@endphp

{{-- Bandeau « pas encore client » : repris sur toutes les pages publiques, juste avant le pied de page --}}
<section class="bo-band">
    <div class="bo-wrap bo-band__inner">
        <div>
            <h2>@lang('site.not_client_title')</h2>
            <p>@lang('site.not_client_text', ['name' => site_name()])</p>
        </div>
        <a href="{{ route('signup', ['locale' => $locale]) }}" class="bo-btn bo-btn--outline-light">@lang('site.become_client') <i class="fas fa-chevron-right"></i></a>
    </div>
</section>

<footer class="bo-footer site-footer">
    <div class="bo-wrap bo-footer__grid">

        <div class="bo-footer__brand">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="bo-footer__logo">
                <x-logo variant="full" theme="dark" size="lg" :dark="$siteContact->logo_dark_path ? Storage::url($siteContact->logo_dark_path) : null" :name="$siteContact->name" />
            </a>
            <p>@lang('menu.footer_desc')</p>
            @if($socialLinks->isNotEmpty())
            <div class="bo-footer__social">
                @foreach ($socialLinks as $link)
                <a href="{{ $link->url }}" aria-label="{{ $link->label }}" @if(str_starts_with($link->url, 'http')) target="_blank" rel="noopener" @endif><i class="{{ $link->icon_class }}"></i></a>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <h3>@lang('menu.quick_links')</h3>
            <ul>
                <li><a href="{{ route('home', ['locale' => $locale]) }}">@lang('menu.home')</a></li>
                <li><a href="{{ route('about', ['locale' => $locale]) }}">@lang('menu.about')</a></li>
                <li><a href="{{ route('signup', ['locale' => $locale]) }}">@lang('menu.open_account')</a></li>
                <li><a href="{{ route('contact', ['locale' => $locale]) }}">@lang('menu.contact')</a></li>
                <li><a href="{{ route('login') }}">@lang('site.online_bank')</a></li>
            </ul>
        </div>

        <div>
            <h3>@lang('menu.services')</h3>
            <ul>
                <li><a href="{{ route('signup', ['locale' => $locale]) }}">@lang('menu.banking_account')</a></li>
                <li><a href="{{ route('signup', ['locale' => $locale]) }}">@lang('menu.banking_card')</a></li>
                <li><a href="{{ route('signup', ['locale' => $locale]) }}">@lang('menu.banking_transfer')</a></li>
            </ul>
        </div>

        {{-- Contact : une seule adresse → elle prend place ici, plusieurs → liste complète --}}
        <div class="bo-footer__contact">
            <h3>@lang('menu.contact')</h3>
            <ul>
                @foreach ($addresses as $address)
                <li><i class="fas fa-map-marker-alt"></i><span>{{ $address }}</span></li>
                @endforeach
                @foreach ($phones as $phone)
                <li><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a></li>
                @endforeach
                @if($siteContact->email)
                <li><i class="fas fa-envelope"></i><a href="mailto:{{ $siteContact->email }}">{{ $siteContact->email }}</a></li>
                @endif
            </ul>
            <form action="{{ route('subscribe.send') }}" method="POST" class="bo-footer__news">
                @csrf
                <label>@lang('menu.newsletter_title')</label>
                <div>
                    <input type="email" name="email" placeholder="@lang('menu.email_placeholder')" required>
                    <button type="submit" aria-label="@lang('menu.subscribe')"><i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="bo-footer__bottom">
        <div class="bo-wrap">
            <p>&copy; {{ date('Y') }} {{ $siteContact->name }}. @lang('menu.rights_reserved')</p>
            <p>
                <a href="{{ route('terms', ['locale' => $locale]) }}">@lang('menu.terms')</a>
                <span>·</span>
                <a href="{{ route('privacy', ['locale' => $locale]) }}">@lang('menu.privacy')</a>
            </p>
        </div>
    </div>
</footer>
