@include('partials.head')

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<a href="#main-content" class="skip-link">@lang('site.skip')</a>
<div id="page-wrapper" class="relative flex flex-col min-h-screen">
    @include('partials.header')
    <main id="main-content" tabindex="-1" class="flex-1">
        @yield('content')
    </main>
    @include('partials.footer')
</div>

<!-- jQuery -->
<script src="{{ asset('assets/vendors/jquery/jquery-3.7.0.min.js') }}"></script>
<!-- Bootstrap JS -->
<script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- jQuery Appear (count-box) -->
<script src="{{ asset('assets/vendors/jquery-appear/jquery.appear.min.js') }}"></script>
<!-- WOW.js -->
<script src="{{ asset('assets/vendors/wow/wow.js') }}"></script>
<!-- easilon.js (compteurs et animations) -->
<script src="{{ asset('assets/js/easilon.js') }}"></script>

<script>
  new WOW({ offset: 60, mobile: false }).init();

  // Sticky header behaviour
  const header = document.getElementById('site-header');
  const isHome = document.body.classList.contains('is-home');
  const updateHeader = () => {
    if (window.scrollY > 80) {
      header.classList.add('header--scrolled');
      header.classList.remove('header--transparent');
    } else {
      header.classList.remove('header--scrolled');
      if (isHome) header.classList.add('header--transparent');
    }
  };
  window.addEventListener('scroll', updateHeader, { passive: true });
  updateHeader();

  // Scroll-to-top
  const scrollBtn = document.getElementById('scroll-top');
  if (scrollBtn) {
    window.addEventListener('scroll', () => {
      const show = window.scrollY > 500;
      scrollBtn.style.opacity = show ? '1' : '0';
      scrollBtn.style.pointerEvents = show ? 'auto' : 'none';
    }, { passive: true });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top:0, behavior:'smooth' }));
  }
</script>

<!-- Scroll-to-top button -->
<button id="scroll-top" aria-label="Retour en haut"
    style="opacity:0;pointer-events:none;transition:opacity .3s ease"
    class="fixed bottom-8 right-8 z-50 w-12 h-12 rounded-full bg-accent text-white flex items-center justify-center shadow-lg hover:bg-navy hover:text-white transition-colors duration-300">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
</button>

@include('partials.whatsapp-bubble')
@include('partials.pwa-install', ['bottom' => '6.5rem'])

@stack('scripts')
</body>
</html>
