@props([
    'title'      => site_name(),
    'subtitle'   => 'Espace Client Sécurisé',
    'accent'     => 'teal',   // teal | green | orange | red
    'footerNote' => null,
    'locale'     => 'fr',
])

@php
$palettes = [
    'teal'   => ['btn'=>'#312B81', 'code'=>'#312B81'],
    'green'  => ['btn'=>'#15803D', 'code'=>'#15803D'],
    'orange' => ['btn'=>'#B45309', 'code'=>'#B45309'],
    'red'    => ['btn'=>'#B91C1C', 'code'=>'#B91C1C'],
];
$p = $palettes[$accent] ?? $palettes['teal'];

$noReplyTexts = [
    'fr' => 'Ceci est un e-mail généré automatiquement. Merci de ne pas y répondre.',
    'en' => 'This is an automatically generated email. Please do not reply.',
    'pl' => 'To jest automatycznie wygenerowana wiadomość e-mail. Prosimy na nią nie odpowiadać.',
    'es' => 'Este es un correo electrónico generado automáticamente. Por favor, no responda a este mensaje.',
    'bg' => 'Това е автоматично генерирано съобщение. Моля, не отговаряйте на него.',
    'hu' => 'Ez egy automatikusan generált e-mail. Kérjük, ne válaszoljon rá.',
    'it' => "Questa è un'email generata automaticamente. Si prega di non rispondere.",
    'de' => 'Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht darauf.',
    'lt' => 'Tai automatiškai sugeneruotas el. laiškas. Prašome į jį neatsakyti.',
    'ro' => 'Acesta este un e-mail generat automat. Vă rugăm să nu răspundeți la acest mesaj.',
    'lv' => 'Šī ir automātiski ģenerēta vēstule. Lūdzu, uz to neatbildiet.',
    'nl' => 'Dit is een automatisch gegenereerde e-mail. Gelieve hier niet op te reageren.',
    'pt' => 'Esta é uma mensagem gerada automaticamente. Por favor, não responda a este e-mail.',
];
$noReplyText = $noReplyTexts[$locale] ?? $noReplyTexts['fr'];
$siteContact = \App\Models\SiteContact::current();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }}</title>
<style>
body{margin:0;padding:0;background:#F1F3F6;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
img{border:0;outline:0}
table{border-collapse:collapse}
.wrap{max-width:580px;margin:32px auto;background:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E5E7EB}
/* ── Header ── */
.hdr{background:#312B81;padding:28px 44px;text-align:center}
.logo-outer{margin:0 auto 14px;display:inline-flex}
.hdr-title{font-size:1.2rem;font-weight:700;color:#FFFFFF;margin:0 0 4px;letter-spacing:-.01em}
.hdr-sub{font-size:.74rem;color:rgba(255,255,255,.55);margin:0;letter-spacing:.03em;text-transform:uppercase}
/* ── Body ── */
.body{padding:32px 44px}
p.greeting{font-size:.95rem;color:#1F2937;margin:0 0 1.25rem;line-height:1.7}
p.body-text{font-size:.88rem;color:#4B5563;line-height:1.75;margin:0 0 1.25rem}
/* ── Contenu libre (modèles de notification rédigés par l'admin) ── */
.body p,.body div{font-size:.88rem;color:#4B5563;line-height:1.75;margin:0 0 1.1rem}
.body ul,.body ol{margin:0 0 1.1rem;padding-left:1.3rem;font-size:.88rem;color:#4B5563;line-height:1.75}
.body strong,.body b{color:#1F2937}
.body a{color:#008457}
.body h1,.body h2,.body h3{color:#312B81;margin:0 0 .75rem}
/* ── Info panel ── */
.panel{background:#F8F9FB;border:1px solid #E5E7EB;border-radius:10px;overflow:hidden;margin-bottom:1.5rem}
.panel-row{display:flex;justify-content:space-between;align-items:baseline;padding:9px 16px;border-bottom:1px solid #ECEEF1;font-size:.84rem}
.panel-row:last-child{border-bottom:none}
.panel-lbl{color:#6B7280;font-size:.79rem;padding-right:10px;white-space:nowrap}
.panel-val{color:#111827;font-weight:600;text-align:right}
.panel-val.accent{color:{{ $p['btn'] }};font-weight:700}
/* ── Alert boxes (teintes pastel, sobres) ── */
.alert{border-radius:9px;padding:14px 18px;font-size:.83rem;line-height:1.7;margin-bottom:1.4rem}
.alert p{margin:0}
.alert strong{display:block;margin-bottom:3px;font-size:.85rem}
.alert-info   {background:#FBF7EE;border:1px solid #EEE1C3;border-left:3px solid #008457;color:#5A4A25}
.alert-warn   {background:#FEF9E7;border:1px solid #FBE9B0;border-left:3px solid #D97706;color:#7A4E06}
.alert-danger {background:#FDECEC;border:1px solid #F5C6C6;border-left:3px solid #B91C1C;color:#7A1F1F}
.alert-success{background:#EEF7EF;border:1px solid #C6E5CA;border-left:3px solid #15803D;color:#1E4A29}
/* ── Button ── */
.btn-wrap{text-align:center;margin:1.6rem 0 1.2rem}
.btn{display:inline-block;padding:12px 34px;border-radius:8px;font-size:.92rem;font-weight:700;text-decoration:none;letter-spacing:.01em;color:#ffffff;background:{{ $p['btn'] }}}
/* ── Code box ── */
.code-box{background:#F8F9FB;border:1px solid #E5E7EB;border-radius:12px;padding:24px 20px;text-align:center;margin-bottom:1.5rem}
.code-digits{font-family:'Courier New',Courier,monospace;font-size:2.4rem;font-weight:900;letter-spacing:.3em;color:{{ $p['code'] }}}
.code-expiry{font-size:.77rem;color:#6B7280;margin-top:9px}
/* ── URL fallback ── */
.url-fallback{font-size:.74rem;color:#9CA3AF;text-align:center;line-height:1.75;margin-bottom:1.25rem;word-break:break-all}
.url-fallback a{color:#008457;text-decoration:none}
/* ── Closing ── */
p.closing{font-size:.88rem;color:#4B5563;margin:1.5rem 0 0;line-height:1.65}
p.closing strong{color:#111827;font-size:.92rem}
/* ── Divider ── */
.divider{height:1px;background:#E5E7EB}
/* ── Footer ── */
.footer{padding:18px 44px;text-align:center}
.footer p{font-size:.72rem;color:#9CA3AF;line-height:1.7;margin:0}
.footer a{color:#008457;text-decoration:none}
.footer-signature{max-height:56px;margin-bottom:10px}
.footer-address{font-size:.74rem;color:#6B7280;line-height:1.6;margin-bottom:10px}
/* ── Responsive ── */
@media only screen and (max-width:600px){
  .wrap{margin:0;border-radius:0;border-left:none;border-right:none}
  .body,.hdr,.footer{padding:24px 22px!important}
  .code-digits{font-size:1.9rem;letter-spacing:.2em}
  .btn{padding:11px 24px;font-size:.86rem}
  .panel-row{flex-direction:column;gap:2px}
  .panel-val{text-align:left}
}
</style>
</head>
<body>
<div class="wrap">

  {{-- ── Header ── --}}
  <div class="hdr">
    <div class="logo-outer">
      <x-logo variant="icon" theme="dark" size="lg" />
    </div>
    <h1 class="hdr-title">{{ $title }}</h1>
    @if($subtitle)
    <p class="hdr-sub">{{ $subtitle }}</p>
    @endif
  </div>

  {{-- ── Content ── --}}
  <div class="body">
    {{ $slot }}
  </div>

  <div class="divider"></div>

  {{-- ── Footer ── --}}
  <div class="footer">
    @if($siteContact->email_signature_path)
    <img src="{{ Storage::url($siteContact->email_signature_path) }}" alt="Signature" class="footer-signature">
    @endif
    @if($siteContact->address_1 || $siteContact->address_2 || $siteContact->address_3 || $siteContact->email)
    <p class="footer-address">
      @if($siteContact->address_1){{ $siteContact->address_1 }}@endif
      @if($siteContact->address_2)<br>{{ $siteContact->address_2 }}@endif
      @if($siteContact->address_3)<br>{{ $siteContact->address_3 }}@endif
      @if($siteContact->email)<br>{{ $siteContact->email }}@endif
    </p>
    @endif
    <p style="font-weight:700;color:#6B7280;margin-bottom:6px">{{ $noReplyText }}</p>
    <p>
      &copy; {{ date('Y') }} {{ site_name() }}
      @if($footerNote)
      &nbsp;·&nbsp; {{ $footerNote }}
      @endif
    </p>
  </div>

</div>
</body>
</html>
