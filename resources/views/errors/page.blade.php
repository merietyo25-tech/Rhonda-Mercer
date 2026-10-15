@php
    /*
     * Page d'erreur autonome (aucune dépendance au layout, à la session ni à la base) : elle doit s'afficher même si la
     * connexion MySQL est tombée. La langue est déterminée ici car, sur un 404, le middleware de langue n'a pas tourné.
     */
    $supported = ['fr', 'en', 'pl', 'es', 'bg', 'hu', 'it', 'de', 'lt', 'ro', 'lv', 'nl', 'pt', 'hr', 'sk', 'sl', 'mt'];
    $pick = function ($code) use ($supported) {
        $code = strtolower(substr((string) $code, 0, 2));
        return in_array($code, $supported, true) ? $code : null;
    };

    $loc = null;
    try { $loc = $pick(optional(request()->user())->locale); } catch (\Throwable $e) {}
    if (! $loc) { try { $loc = $pick(session('locale')); } catch (\Throwable $e) {} }
    if (! $loc && preg_match('#^/([a-z]{2})(/|$)#', request()->getPathInfo(), $m)) { $loc = $pick($m[1]); }
    if (! $loc) {
        foreach (explode(',', (string) request()->header('Accept-Language', '')) as $part) {
            if ($loc = $pick(trim($part))) { break; }
        }
    }
    app()->setLocale($loc ?: 'en');

    $code = (int) ($code ?? 500);
    // Texte propre à certains codes ; les autres retombent sur le message générique de leur famille.
    $key = match (true) {
        in_array($code, [401, 403, 404, 419, 423, 429, 500, 503], true) => (string) $code,
        in_array($code, [502, 504], true)                              => '503',
        $code >= 500                                                   => '500',
        default                                                        => '4xx',
    };

    $name = function_exists('site_name') ? site_name() : config('app.name');
    $home = url('/');
    $retry = in_array($code, [419, 429, 500, 503, 502, 504], true);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ $code }} — {{ __('errors.t' . $key) }} — {{ $name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&family=Fraunces:wght@700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{background:radial-gradient(1200px 600px at 50% -10%,#312B81 0%,#1A1750 55%,#100D3A 100%);color:#e9eef5;font-family:'Outfit',system-ui,-apple-system,'Segoe UI',sans-serif;display:flex;flex-direction:column;min-height:100vh}
.brand{display:inline-flex;align-items:center;padding:1.25rem 1.5rem;text-decoration:none;align-self:flex-start}
.wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:1.5rem}
.card{width:100%;max-width:520px;text-align:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:24px;padding:2.5rem 2rem 2.1rem;box-shadow:0 30px 80px rgba(0,0,0,.35);backdrop-filter:blur(6px)}
.code{font-family:'Space Grotesk',sans-serif;font-size:clamp(4.5rem,18vw,7rem);font-weight:700;line-height:1;letter-spacing:-.04em;background:linear-gradient(135deg,#D9F2E7,#3FCB92 55%,#008457);-webkit-background-clip:text;background-clip:text;color:transparent}
h1{font-size:1.45rem;font-weight:700;margin:.9rem 0 .6rem;color:#fff}
p{font-size:.95rem;line-height:1.65;color:rgba(233,238,245,.72);max-width:400px;margin:0 auto}
.actions{display:flex;flex-wrap:wrap;gap:.7rem;justify-content:center;margin-top:1.8rem}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1.4rem;border-radius:999px;font-family:inherit;font-size:.9rem;font-weight:600;text-decoration:none;cursor:pointer;border:1px solid transparent;transition:transform .12s,opacity .15s}
.btn:active{transform:scale(.98)}
.btn--primary{background:linear-gradient(135deg,#3FCB92,#008457);color:#1a2b3c}
.btn--ghost{background:transparent;color:#e9eef5;border-color:rgba(255,255,255,.22)}
.btn:hover{opacity:.9}
.foot{padding:1rem 1.5rem 1.5rem;text-align:center;font-size:.75rem;color:rgba(233,238,245,.4)}
</style>
</head>
<body>
<a href="{{ $home }}" class="brand" aria-label="{{ $name }}"><x-logo variant="full" theme="dark" size="md" /></a>

<main class="wrap">
  <div class="card" role="alert">
    <div class="code">{{ $code }}</div>
    <h1>{{ __('errors.t' . $key) }}</h1>
    <p>{{ __('errors.x' . $key) }}</p>
    <div class="actions">
      <a href="{{ $home }}" class="btn btn--primary">{{ __('errors.back_home') }}</a>
      @if($retry)
        <a href="{{ url()->current() }}" class="btn btn--ghost" onclick="location.reload();return false;">{{ __('errors.try_again') }}</a>
      @else
        <a href="javascript:history.back()" class="btn btn--ghost">{{ __('errors.go_back') }}</a>
      @endif
    </div>
  </div>
</main>

<div class="foot">&copy; {{ date('Y') }} {{ $name }}</div>
</body>
</html>
