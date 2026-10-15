@php
    $locale = $user->locale ?? 'fr';
    $site   = site_name();
    // Couleurs alignées sur les pages d'authentification (/login, /email/verify)
    $bg = '#0F0D36'; $card = '#1B1757'; $inp = '#231E6B'; $gold = '#3FCB92'; $gold2 = '#008457';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="dark light">
<title>{{ __('onboarding.mail_title', [], $locale) }}</title>
</head>
<body style="margin:0;padding:0;background:{{ $bg }};font-family:'Outfit','Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="{{ $bg }}" style="background:{{ $bg }}">
<tr><td align="center" style="padding:32px 16px">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="{{ $card }}"
         style="max-width:520px;background:{{ $card }};border:1px solid rgba(63,203,146,.25);border-radius:20px">
    <tr><td align="center" style="padding:34px 32px 8px">
      <x-logo variant="full" theme="dark" size="md" />
    </td></tr>

    {{-- Pastille enveloppe, comme sur la page /email/verify --}}
    <tr><td align="center" style="padding:18px 32px 0">
      <table role="presentation" cellpadding="0" cellspacing="0"><tr>
        <td width="64" height="64" align="center" valign="middle" bgcolor="#231F5F"
            style="width:64px;height:64px;border-radius:32px;background:#231F5F;font-size:28px;line-height:64px;color:{{ $gold }}">&#9993;</td>
      </tr></table>
    </td></tr>

    <tr><td align="center" style="padding:18px 32px 0">
      <h1 style="margin:0;font-size:21px;line-height:1.3;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#FFFFFF">
        {{ __('onboarding.mail_title', [], $locale) }}
      </h1>
    </td></tr>

    <tr><td align="center" style="padding:14px 36px 0;font-size:14px;line-height:1.7;color:rgba(255,255,255,.72)">
      <strong style="color:#FFFFFF">{{ $user->name }}</strong>,<br>
      {{ __('onboarding.mail_intro', ['site' => $site], $locale) }}
    </td></tr>

    <tr><td align="center" style="padding:26px 32px 6px">
      <a href="{{ $activationUrl }}"
         style="display:inline-block;padding:15px 38px;border-radius:999px;background:{{ $gold2 }};background-image:linear-gradient(135deg,{{ $gold }},{{ $gold2 }});color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none">
        {{ __('onboarding.mail_btn', [], $locale) }}
      </a>
    </td></tr>

    <tr><td align="center" style="padding:6px 32px 0;font-size:12px;color:rgba(255,255,255,.45)">
      {{ __('onboarding.mail_footer', [], $locale) }}
    </td></tr>

    <tr><td style="padding:24px 32px 0">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="{{ $inp }}"
             style="background:{{ $inp }};border:1px solid rgba(63,203,146,.18);border-radius:12px">
        <tr><td style="padding:14px 16px;font-size:12px;line-height:1.7;color:rgba(255,255,255,.6);word-break:break-all">
          {{ __('onboarding.mail_fallback', [], $locale) }}<br>
          <a href="{{ $activationUrl }}" style="color:{{ $gold }};text-decoration:none">{{ $activationUrl }}</a>
        </td></tr>
      </table>
    </td></tr>

    <tr><td style="padding:18px 32px 0">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td style="border-left:3px solid {{ $gold }};padding:6px 0 6px 14px;font-size:12px;line-height:1.7;color:rgba(255,255,255,.6)">
          {{ __('onboarding.mail_notice', [], $locale) }}
        </td></tr>
      </table>
    </td></tr>

    <tr><td align="center" style="padding:28px 32px 30px;font-size:11px;color:rgba(255,255,255,.38)">
      &copy; {{ date('Y') }} {{ $site }}
    </td></tr>
  </table>

</td></tr>
</table>
</body>
</html>
