{{-- Compte connecté : avatar + prénom, visible à partir de la tablette (mène au profil) --}}
@auth
<a href="{{ route('client.app.profile') }}" class="ca-chip" aria-label="{{ __('app.nav_profile') }}">
  <span class="ca-chip__avatar" style="overflow:hidden;padding:0">@include('partials.client-avatar', ['uid' => 'c'])</span>
  <span class="ca-chip__name">{{ \Illuminate\Support\Str::words(auth()->user()->name, 2, '') }}</span>
  <i class="fas fa-chevron-down"></i>
</a>
@endauth
