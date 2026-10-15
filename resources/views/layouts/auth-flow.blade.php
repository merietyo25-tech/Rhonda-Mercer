<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0F0D36">
<link rel="manifest" href="{{ request()->is('staff/*') ? route('pwa.admin-manifest') : route('pwa.manifest') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ site_name() }}">
<link rel="icon" type="image/png" sizes="192x192" href="/site-icon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/site-icon-180.png">
<title>@yield('title') — {{ site_name() }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
:root{
  --bg:#0F0D36; --card:#1B1757; --inp:#231E6B;
  --navy:#312B81; --navy2:#3C3694;
  --accent:#3FCB92; --accent-2:#2B94F7;
  --text:#FFFFFF; --sub:rgba(255,255,255,.62); --muted:rgba(255,255,255,.34);
  --bdr:rgba(63,203,146,.16);
  --ok:#34d399; --danger:#f87171;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--bg);color:var(--text);font-family:'Outfit',system-ui,sans-serif;font-size:15px;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
body{min-height:100vh;min-height:100dvh;overflow-x:hidden}
a{text-decoration:none;color:inherit}
img{max-width:100%}

.bg-orbs{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.orb{position:absolute;border-radius:50%;filter:blur(90px)}
.orb-1{width:480px;height:480px;top:-10%;right:-8%;background:radial-gradient(circle,rgba(43,148,247,.16) 0%,transparent 65%)}
.orb-2{width:360px;height:360px;bottom:-15%;left:-8%;background:radial-gradient(circle,rgba(0,132,87,.20) 0%,transparent 65%)}

.shell{position:relative;z-index:1;min-height:100vh;min-height:100dvh;display:flex;flex-direction:column}

/* Top bar */
.topbar{position:relative;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:1rem;
  padding:.9rem 1.5rem;padding-top:calc(.9rem + env(safe-area-inset-top,0px))}
.topbar__back{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:500;color:var(--sub);transition:color .18s}
.topbar__back:hover{color:var(--accent)}
.topbar__back i{font-size:.65rem}
.ls{position:relative}
.ls__btn{display:flex;align-items:center;gap:.5rem;cursor:pointer;min-height:38px;background:var(--inp);border:1.5px solid var(--bdr);
  border-radius:999px;padding:.3rem .8rem;font-size:.76rem;font-weight:700;color:var(--text);font-family:inherit;transition:border-color .18s}
.ls__btn:hover,.ls__btn[aria-expanded=true]{border-color:var(--accent)}
.ls__btn img{width:18px;height:12px;object-fit:cover;border-radius:2px}
.ls__globe{color:var(--accent);font-size:.8rem}
.ls__chev{font-size:.5rem;color:var(--muted);transition:transform .2s}
.ls__chev.is-open{transform:rotate(180deg)}
.ls__menu{position:absolute;right:0;top:calc(100% + .5rem);background:var(--card);border:1.5px solid var(--bdr);
  border-radius:14px;box-shadow:0 16px 48px rgba(0,0,0,.5);padding:.35rem;width:220px;max-width:calc(100vw - 2rem);max-height:min(60vh,380px);overflow:auto;z-index:1000}
.ls__opt{display:flex;align-items:center;gap:.65rem;padding:.6rem .75rem;border-radius:10px;font-size:.82rem;font-weight:600;color:var(--sub)}
.ls__opt span{flex:1;min-width:0}
.ls__opt:hover{background:var(--inp);color:var(--text)}
.ls__opt img{width:20px;height:14px;object-fit:cover;border-radius:2px;flex-shrink:0}
.ls__opt.cur{background:rgba(63,203,146,.12);color:var(--accent)}
.ls__opt .fa-check{font-size:.65rem}
/* Layout principal : carte seule (mobile/tablette) ou panneau + carte (desktop) */
.main{flex:1;display:flex;align-items:center;justify-content:center;padding:1rem 1.25rem 2rem}
.split{width:100%;max-width:1080px;display:grid;grid-template-columns:minmax(0,1fr);gap:2.5rem;align-items:center;justify-items:center}
.visual{display:none}
.visual{position:sticky;top:0;height:100vh;flex-direction:column;justify-content:flex-end;padding:3.5rem;overflow:hidden;
  background:linear-gradient(180deg,#03213b 0%,var(--bg) 100%)}
.visual::before{content:"";position:absolute;left:0;right:0;top:0;height:62%;
  background:url("{{ asset('images/auth-side.jpg') }}") 70% center/cover no-repeat;
  -webkit-mask-image:linear-gradient(180deg,#000 55%,transparent 100%);mask-image:linear-gradient(180deg,#000 55%,transparent 100%)}
.visual > *{position:relative}
.banner{width:100%;height:130px;margin:0 auto 1rem;border-radius:16px;border:1px solid var(--bdr);
  background:linear-gradient(180deg,rgba(15,13,54,.1),rgba(15,13,54,.55)),url("{{ asset('images/auth-side.jpg') }}") center 30%/cover no-repeat}
.pane{display:flex;flex-direction:column;min-height:100vh;min-height:100dvh}
.col{width:100%;max-width:440px;display:flex;flex-direction:column;align-items:stretch}
.col--wide{max-width:560px}
.col .topbar{padding:0 0 .9rem}
.card{width:100%;min-width:0;background:var(--card);border:1px solid var(--bdr);border-radius:20px;
  padding:2.25rem 2rem;box-shadow:0 4px 24px rgba(0,0,0,.25),0 16px 48px rgba(0,0,0,.3)}


.logo-box{display:flex;align-items:center;justify-content:center;width:fit-content;max-width:100%;border-radius:20px;overflow:hidden;
  background:linear-gradient(135deg,var(--navy2),var(--navy));border:1px solid rgba(63,203,146,.18);margin:0 auto 1.5rem;
  box-shadow:0 12px 34px rgba(15,13,54,.55),0 0 44px rgba(0,132,87,.22)}
.logo-box img{height:auto !important;max-height:80px;max-width:230px;width:auto;object-fit:contain;display:block}

.card-head{text-align:center;margin-bottom:1.75rem}
.card-title{font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.card-sub{font-size:.84rem;color:var(--sub);line-height:1.6;overflow-wrap:anywhere}

.msg{display:flex;align-items:flex-start;gap:.55rem;border-radius:10px;padding:.7rem .9rem;font-size:.8rem;margin-bottom:1.1rem;line-height:1.5;overflow-wrap:anywhere}
.msg i{margin-top:.15rem;flex-shrink:0}
.msg--err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-left:3px solid #ef4444;color:#fecaca}
.msg--ok{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-left:3px solid #10b981;color:#a7f3d0}
.msg--info{background:rgba(63,203,146,.08);border:1px solid rgba(63,203,146,.22);border-left:3px solid var(--accent);color:var(--sub)}

/* Champs */
.fgrid{display:grid;grid-template-columns:1fr;gap:0 1rem}
.fgrp{margin-bottom:.95rem;min-width:0}
.flabel{display:block;font-size:.76rem;font-weight:600;color:var(--sub);margin-bottom:.4rem}
.flabel em{font-style:normal;font-weight:400;color:var(--muted)}
.frel{position:relative}
.ficon{position:absolute;left:.95rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.78rem;pointer-events:none;z-index:1}
.finput{width:100%;min-height:48px;padding:.8rem 1rem .8rem 2.6rem;background:var(--inp);border:1.5px solid var(--bdr);border-radius:12px;
  font-size:16px;font-family:inherit;color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s;appearance:none;-webkit-appearance:none}
.finput::placeholder{color:var(--muted)}
.finput:focus{border-color:var(--accent);box-shadow:0 0 0 3.5px rgba(63,203,146,.14)}
.frel:focus-within .ficon{color:var(--accent)}
.finput.err{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.13)}
select.finput{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none' stroke='%233FCB92' stroke-width='2'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 1rem center;padding-right:2.4rem}
select.finput option{background:var(--card);color:var(--text)}
input[type=date].finput{color-scheme:dark}
.feye{position:absolute;right:.4rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;
  width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:.85rem}
.feye:hover{color:var(--accent)}
.has-eye .finput{padding-right:3rem}
.ferr-txt{display:block;font-size:.73rem;color:var(--danger);margin-top:.35rem}
.fhint{font-size:.72rem;color:var(--muted);margin-top:.35rem}
.note{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--sub);background:rgba(63,203,146,.07);
  border:1px dashed rgba(63,203,146,.3);border-radius:10px;padding:.65rem .85rem;margin:.25rem 0 1.15rem}
.note i{color:var(--accent)}

/* Boutons */
.fbtn{width:100%;min-height:50px;padding:.9rem 1.5rem;border:none;border-radius:999px;font-size:.97rem;font-weight:700;font-family:inherit;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.6rem;
  background:linear-gradient(135deg,#3FCB92 0%,#008457 100%);color:#fff;
  box-shadow:0 6px 28px rgba(0,132,87,.4),0 2px 8px rgba(15,13,54,.35);transition:filter .2s,transform .1s}
.fbtn:hover{filter:brightness(1.12)}
.fbtn:active{transform:scale(.98)}
.fbtn:disabled{opacity:.6;cursor:not-allowed}
.fbtn--ghost{background:transparent;color:var(--text);border:1.5px solid var(--bdr);box-shadow:none}
.fbtn--ghost:hover{border-color:var(--accent);filter:none}
.alt-link{text-align:center;margin-top:1.4rem;font-size:.8rem;color:var(--muted)}
.alt-link a{color:var(--accent);font-weight:600}
.alt-link a:hover{text-decoration:underline}

/* Panneau d'accueil (desktop) */
.aside__title{font-family:'Space Grotesk',sans-serif;font-size:2rem;line-height:1.2;font-weight:700;margin:1.5rem 0 1rem}
.aside__text{color:var(--sub);line-height:1.7;max-width:420px;margin-bottom:1.75rem}
.aside__list{list-style:none;display:grid;gap:.9rem}
.aside__list li{display:flex;align-items:center;gap:.8rem;color:var(--sub);font-size:.92rem}
.aside__list i{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  background:rgba(63,203,146,.12);color:var(--accent);font-size:.85rem}

.pg-foot{padding:.75rem 1.5rem 1.25rem;padding-bottom:calc(1.25rem + env(safe-area-inset-bottom,0px));text-align:center;font-size:.7rem;color:var(--muted)}
.pg-foot a{color:var(--sub)}.pg-foot a:hover{color:var(--accent)}

@keyframes spin{to{transform:rotate(360deg)}}
.spin{display:inline-block;width:18px;height:18px;border-radius:50%;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;animation:spin .65s linear infinite}

/* Tablette */
@media (min-width:600px){
  .card{padding:2.5rem}
  .fgrid--2{grid-template-columns:1fr 1fr}
  .fgrid--2 .span-2{grid-column:1 / -1}
}
/* Desktop : écran partagé image + formulaire */
@media (min-width:992px){
  .shell--split{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1fr)}
  .shell--split .visual{display:flex}
  .shell--split .banner{display:none}
  .shell--split .pane{min-width:0;display:flex;flex-direction:column;min-height:100vh}

}
/* Petits mobiles */
@media (max-width:420px){
  .pg-foot{padding-bottom:calc(4.5rem + env(safe-area-inset-bottom,0px))}
  body{font-size:14px}
  .main{padding:.5rem .75rem 1.5rem}
  .card{padding:1.6rem 1.15rem;border-radius:16px}
  .card-title{font-size:1.2rem}

}
@media (prefers-reduced-motion:reduce){*{animation:none !important;transition:none !important}}
</style>
@stack('styles')
</head>
<body>

<div class="bg-orbs" aria-hidden="true"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>

<div class="shell @hasSection('aside') shell--split @endif">
  @hasSection('aside')<aside class="visual">@yield('aside')</aside>@endif
  <div class="pane">
  <div class="main">
    <div class="split">
      <div class="col @if(trim($__env->yieldContent('card_class'))) col--wide @endif">
        @include('partials.auth-topbar')
        @hasSection('aside')<div class="banner" aria-hidden="true"></div>@endif
        <div class="card @yield('card_class')">
          <div class="logo-box"><x-logo variant="full" theme="dark" size="md" /></div>
          @yield('content')
        </div>
      </div>
    </div>
  </div>

  <div class="pg-foot">
    &copy; {{ date('Y') }} {{ site_name() }} &nbsp;·&nbsp;
    <a href="{{ url('/'.app()->getLocale().'/terms') }}">{{ __('menu.terms') }}</a> &nbsp;·&nbsp;
    <a href="{{ url('/'.app()->getLocale().'/privacy') }}">{{ __('menu.privacy') }}</a>
  </div>
  </div>{{-- /pane --}}
</div>

<script>
function tglPwd(btn){
  var inp = btn.closest('.frel').querySelector('.finput'), ico = btn.querySelector('i');
  var show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  ico.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
@include('partials.pwa-install')
@stack('scripts')
</body>
</html>
