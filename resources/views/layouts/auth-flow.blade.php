@include('partials.head')

<style>
/* ── Pages d'authentification : même univers que le site public (en-tête, bandeau, pied de page) ── */
.au{
  --bg:#F3F3F8; --card:#FFFFFF; --inp:#FFFFFF;
  --navy:#312B81; --navy2:#4D47AA;
  --accent:#008457; --accent-2:#2B94F7;
  --text:#212121; --sub:#4a4a5e; --muted:#7a7a90;
  --bdr:#dcdce8; --ok:#0a7d52; --danger:#c62828;
  font-family:'Outfit',system-ui,sans-serif; color:var(--text); font-size:15px;
}
.au *,.au *::before,.au *::after{box-sizing:border-box}
.au-body{background:#F3F3F8}

/* Bandeau compact + bloc formulaire qui le chevauche */
.au-hero{position:relative;color:#fff;background:linear-gradient(135deg,rgba(35,31,95,.93),rgba(26,23,80,.9)),url("{{ asset('assets/images/site/contact-hero.jpg') }}") center/cover no-repeat;padding:2.6rem 0 7.5rem;overflow:hidden}
.au-hero::after{content:"";position:absolute;width:380px;height:380px;right:-90px;top:-130px;border-radius:50%;background:radial-gradient(circle,rgba(0,132,87,.55),transparent 65%);animation:au-orb 14s ease-in-out infinite alternate}
@keyframes au-orb{to{transform:translate(-40px,36px) scale(1.15)}}
.au-hero .bo-wrap{position:relative;z-index:1}
.au-crumb{display:flex;gap:.55rem;align-items:center;font-size:.82rem;color:rgba(255,255,255,.85);margin-bottom:.8rem}
.au-crumb a{color:#7BE0AE;font-weight:700}
.au-hero h1{font-family:'Outfit',sans-serif;font-size:clamp(1.7rem,4vw,2.6rem);font-weight:800;letter-spacing:-.01em;color:#fff;margin:0;text-transform:uppercase}
.au-hero h1::after{content:"";display:block;width:56px;height:4px;border-radius:2px;background:#2BC47D;margin-top:.8rem}

.au-main{position:relative;z-index:2;margin-top:-5rem;padding-bottom:4rem}
.au-shell{display:grid;grid-template-columns:minmax(0,1fr);max-width:560px;margin:0 auto;background:#fff;border-radius:22px;box-shadow:0 30px 70px rgba(20,20,60,.18);overflow:hidden;animation:au-in .6s cubic-bezier(.22,.8,.24,1) both}
.au-shell--wide{max-width:820px}
@keyframes au-in{from{opacity:0;transform:translateY(24px)}}
.au-aside{display:none;position:relative;color:#fff;padding:2.5rem 2rem;background:linear-gradient(180deg,rgba(49,43,129,.9),rgba(26,23,80,.97)),url("{{ asset('images/auth-side.jpg') }}") 70% center/cover no-repeat;flex-direction:column;justify-content:flex-end}
.au-card{padding:2.25rem 2rem;min-width:0}
@media (min-width:600px){.au-card{padding:2.5rem 2.75rem}}
@media (min-width:992px){
  .au-shell--split{max-width:1000px;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr)}
  .au-shell--split .au-aside{display:flex}
}
@media (max-width:420px){.au-card{padding:1.6rem 1.15rem}.au-hero{padding:1.8rem 0 6.5rem}.au-main{margin-top:-4.5rem}.au-main .bo-wrap{padding-left:.75rem;padding-right:.75rem}}

.au .aside__title{font-family:'Outfit',sans-serif;font-size:1.6rem;line-height:1.25;font-weight:800;margin:0 0 1.2rem;color:#fff}
.au .aside__text{color:rgba(255,255,255,.88);line-height:1.7;margin-bottom:1.5rem}
.au .aside__list{list-style:none;display:grid;gap:.9rem;margin:0;padding:0}
.au .aside__list li{display:flex;align-items:center;gap:.8rem;color:rgba(255,255,255,.92);font-size:.92rem;line-height:1.45}
.au .aside__list i{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,.14);color:#7BE0AE;font-size:.85rem}

.au .card-head{text-align:center;margin-bottom:1.6rem}
.au .card-title{font-family:'Outfit',sans-serif;font-size:1.45rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;margin:0 0 .5rem;color:var(--navy)}
.au .card-sub{font-size:.88rem;color:var(--sub);line-height:1.6;overflow-wrap:anywhere;margin:0}

.au .msg{display:flex;align-items:flex-start;gap:.55rem;border-radius:10px;padding:.7rem .9rem;font-size:.82rem;margin-bottom:1.1rem;line-height:1.5;overflow-wrap:anywhere}
.au .msg i{margin-top:.15rem;flex-shrink:0}
.au .msg--err{background:#fdecec;border:1px solid #f5c2c2;border-left:3px solid #d32f2f;color:#8e1b1b}
.au .msg--ok{background:#e7f6ef;border:1px solid #b6e3cd;border-left:3px solid #008457;color:#0b5d3f}
.au .msg--info{background:#eef0fb;border:1px solid #d5d9f3;border-left:3px solid var(--navy);color:var(--sub)}

.au .fgrid{display:grid;grid-template-columns:1fr;gap:0 1rem}
.au .fgrp{margin-bottom:.95rem;min-width:0}
.au .flabel{display:block;font-size:.78rem;font-weight:700;color:var(--sub);margin-bottom:.4rem}
.au .flabel em{font-style:normal;font-weight:400;color:var(--muted)}
.au .frel{position:relative}
.au .ficon{position:absolute;left:.95rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.78rem;pointer-events:none;z-index:1}
.au .finput{width:100%;min-height:48px;padding:.8rem 1rem .8rem 2.6rem;background:#fafafd;border:1.5px solid var(--bdr);border-radius:12px;font-size:16px;font-family:inherit;color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s,background-color .2s;appearance:none;-webkit-appearance:none}
.au .finput::placeholder{color:var(--muted)}
.au .finput:focus{border-color:var(--accent);background:#fff;box-shadow:0 0 0 3.5px rgba(0,132,87,.14)}
.au .frel:focus-within .ficon{color:var(--accent)}
.au .finput.err{border-color:#d32f2f;box-shadow:0 0 0 3px rgba(211,47,47,.12)}
.au select.finput{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none' stroke='%23008457' stroke-width='2'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 1rem center;padding-right:2.4rem}
.au select.finput option{background:#fff;color:var(--text)}
.au input[type=date].finput{color-scheme:light}
.au .feye{position:absolute;right:.4rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;width:44px;height:44px;display:flex;align-items:center;justify-content:center;font-size:.85rem}
.au .feye:hover{color:var(--accent)}
.au .has-eye .finput{padding-right:3rem}
.au .ferr-txt{display:block;font-size:.75rem;color:var(--danger);margin-top:.35rem}
.au .fhint{display:block;font-size:.74rem;color:var(--muted);margin-top:.35rem}
.au .note{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--sub);background:#eef8f3;border:1px dashed rgba(0,132,87,.35);border-radius:10px;padding:.65rem .85rem;margin:.25rem 0 1.15rem}
.au .note i{color:var(--accent)}

.au .fbtn{width:100%;min-height:52px;padding:.9rem 1.5rem;border:none;border-radius:999px;font-size:.97rem;font-weight:800;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.6rem;background:var(--accent);color:#fff;box-shadow:0 10px 26px rgba(0,132,87,.3);transition:background-color .2s,transform .15s,box-shadow .2s}
.au .fbtn:hover{background:#006A45;color:#fff;transform:translateY(-1px);box-shadow:0 14px 30px rgba(0,132,87,.38)}
.au .fbtn:active{transform:scale(.98)}
.au .fbtn:disabled{opacity:.6;cursor:not-allowed}
.au .fbtn--ghost{background:#fff;color:var(--navy);border:1.5px solid var(--bdr);box-shadow:none}
.au .fbtn--ghost:hover{border-color:var(--navy);background:#fff;color:var(--navy);transform:none;box-shadow:none}
.au .alt-link{text-align:center;margin-top:1.4rem;font-size:.84rem;color:var(--muted)}
.au .alt-link a{color:var(--accent);font-weight:700}
.au .alt-link a:hover{text-decoration:underline}
@keyframes spin{to{transform:rotate(360deg)}}
.au .spin{display:inline-block;width:18px;height:18px;border-radius:50%;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;animation:spin .65s linear infinite}
@media (min-width:600px){.au .fgrid--2{grid-template-columns:1fr 1fr}.au .fgrid--2 .span-2{grid-column:1 / -1}}
@media (prefers-reduced-motion:reduce){.au *,.au-hero::after{animation:none !important;transition:none !important}}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@stack('styles')

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@section('no_band', '1')
<a href="#main-content" class="skip-link">@lang('site.skip')</a>
<div id="page-wrapper" class="au-body flex flex-col min-h-screen">
    @include('partials.header')
    <main id="main-content" tabindex="-1" class="flex-1 au">
        @php $isWide = trim($__env->yieldContent('card_class')) !== ''; $hasAside = $__env->hasSection('aside'); @endphp
        <section class="au-hero">
            <div class="bo-wrap">
                <nav class="au-crumb" aria-label="breadcrumb">
                    <a href="{{ url('/' . app()->getLocale()) }}"><i class="fas fa-home" aria-hidden="true"></i> @lang('menu.home')</a>
                    <span aria-hidden="true">/</span> <span>@yield('title')</span>
                </nav>
                <h1>@yield('title')</h1>
            </div>
        </section>

        <div class="au-main">
            <div class="bo-wrap">
                <div class="au-shell {{ $isWide ? 'au-shell--wide' : '' }} {{ $hasAside && ! $isWide ? 'au-shell--split' : '' }}">
                    @if ($hasAside && ! $isWide)<aside class="au-aside">@yield('aside')</aside>@endif
                    <div class="au-card">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('partials.footer')
</div>

<script>
function tglPwd(btn){
  var inp = btn.closest('.frel').querySelector('.finput'), ico = btn.querySelector('i');
  var show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  ico.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
@include('partials.pwa-install', ['bottom' => '6.5rem'])
@stack('scripts')
</body>
</html>
