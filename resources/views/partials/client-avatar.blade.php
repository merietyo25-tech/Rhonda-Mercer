{{-- Avatar du client (illustration neutre : buste sur fond dégradé aux couleurs de la marque) --}}
<svg class="ca-avatar-svg" width="100%" height="100%" viewBox="0 0 48 48" role="img" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="avbg{{ $uid ?? '' }}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#4D47AA"/>
      <stop offset="1" stop-color="#231F5F"/>
    </linearGradient>
  </defs>
  <rect width="48" height="48" fill="url(#avbg{{ $uid ?? '' }})"/>
  <circle cx="24" cy="19" r="8.5" fill="#E8E6FA"/>
  <path d="M7 48c0-10 7.6-16.5 17-16.5S41 38 41 48z" fill="#E8E6FA"/>
  <circle cx="24" cy="19" r="8.5" fill="none" stroke="#2BC47D" stroke-width="1.2" opacity=".0"/>
</svg>
