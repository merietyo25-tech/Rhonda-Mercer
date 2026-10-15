@php
$locale = $user->locale ?? 'fr';
$gender = $user->gender ?? 'N';

$texts = [
    'fr' => [
        'title'       => 'Activation de compte — ' . site_name(),
        'greeting'    => ['M' => 'Cher Monsieur', 'F' => 'Chère Madame', 'N' => 'Bonjour'],
        'subtitle'    => 'Définissez votre mot de passe pour activer votre accès',
        'info_title'  => 'Votre compte ' . site_name(),
        'info_body'   => 'a été créé par votre conseiller. Choisissez un mot de passe sécurisé pour accéder à votre espace personnel.',
        'email_label' => 'Adresse email',
        'pw_label'    => 'Nouveau mot de passe',
        'pw_ph'       => 'Minimum 8 caractères',
        'cpw_label'   => 'Confirmer le mot de passe',
        'cpw_ph'      => 'Répéter le mot de passe',
        'btn'         => 'Activer mon compte',
        'login_text'  => 'Vous avez déjà un compte ?',
        'login_link'  => 'Se connecter',
        'str_ph'      => 'Saisissez un mot de passe',
        'strengths'   => ['', 'Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'],
    ],
    'en' => [
        'title'       => 'Account Activation — ' . site_name(),
        'greeting'    => ['M' => 'Dear Mr.', 'F' => 'Dear Ms.', 'N' => 'Hello'],
        'subtitle'    => 'Set your password to activate your account',
        'info_title'  => 'Your ' . site_name() . ' account',
        'info_body'   => 'was created by your advisor. Choose a secure password to access your personal space.',
        'email_label' => 'Email address',
        'pw_label'    => 'New password',
        'pw_ph'       => 'At least 8 characters',
        'cpw_label'   => 'Confirm password',
        'cpw_ph'      => 'Repeat password',
        'btn'         => 'Activate my account',
        'login_text'  => 'Already have an account?',
        'login_link'  => 'Sign in',
        'str_ph'      => 'Enter a password',
        'strengths'   => ['', 'Very weak', 'Weak', 'Fair', 'Strong', 'Very strong'],
    ],
    'es' => [
        'title'       => 'Activación de cuenta — ' . site_name(),
        'greeting'    => ['M' => 'Estimado Sr.', 'F' => 'Estimada Sra.', 'N' => 'Hola'],
        'subtitle'    => 'Establezca su contraseña para activar su cuenta',
        'info_title'  => 'Su cuenta de ' . site_name(),
        'info_body'   => 'fue creada por su asesor. Elija una contraseña segura para acceder a su espacio personal.',
        'email_label' => 'Correo electrónico',
        'pw_label'    => 'Nueva contraseña',
        'pw_ph'       => 'Mínimo 8 caracteres',
        'cpw_label'   => 'Confirmar contraseña',
        'cpw_ph'      => 'Repetir contraseña',
        'btn'         => 'Activar mi cuenta',
        'login_text'  => '¿Ya tiene una cuenta?',
        'login_link'  => 'Iniciar sesión',
        'str_ph'      => 'Introduzca una contraseña',
        'strengths'   => ['', 'Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'],
    ],
    'pl' => [
        'title'       => 'Aktywacja konta — ' . site_name(),
        'greeting'    => ['M' => 'Szanowny Panie', 'F' => 'Szanowna Pani', 'N' => 'Witaj'],
        'subtitle'    => 'Ustaw hasło, aby aktywować dostęp do konta',
        'info_title'  => 'Twoje konto ' . site_name(),
        'info_body'   => 'zostało utworzone przez Twojego doradcę. Wybierz bezpieczne hasło, aby uzyskać dostęp do swojego osobistego obszaru.',
        'email_label' => 'Adres e-mail',
        'pw_label'    => 'Nowe hasło',
        'pw_ph'       => 'Minimum 8 znaków',
        'cpw_label'   => 'Potwierdź hasło',
        'cpw_ph'      => 'Powtórz hasło',
        'btn'         => 'Aktywuj moje konto',
        'login_text'  => 'Masz już konto?',
        'login_link'  => 'Zaloguj się',
        'str_ph'      => 'Wpisz hasło',
        'strengths'   => ['', 'Bardzo słabe', 'Słabe', 'Średnie', 'Silne', 'Bardzo silne'],
    ],
];

$t              = $texts[$locale]         ?? $texts['fr'];
$greeting       = $t['greeting'][$gender] ?? $t['greeting']['N'];
$salutationName = ($gender === 'N') ? explode(' ', $user->name)[0] : $user->name;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#008457">
<link rel="icon" type="image/png" sizes="32x32" href="/site-icon-32.png">
<title>{{ $t['title'] }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
  --bg:   #0F0D36;
  --inp:  #231E6B;
  --navy: #312B81;
  --navy2:#3C3694;
  --accent: #3FCB92;
  --accent-2:#2B94F7;
  --text: #FFFFFF;
  --sub:  rgba(255,255,255,.52);
  --muted:rgba(255,255,255,.28);
  --bdr:  rgba(63,203,146,.16);
  /* Compat: quelques accents ponctuels référencent encore --cyan/--cyan2 */
  --cyan: #2B94F7;
  --cyan2:#3FCB92;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{
  min-height:100vh;background:var(--bg);color:var(--text);
  font-family:'Outfit', system-ui, sans-serif;font-size:15px;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
}
body{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:1.5rem 1.25rem;
  padding-top:calc(1.5rem + env(safe-area-inset-top,0px));
  padding-bottom:calc(1.5rem + env(safe-area-inset-bottom,0px));
  overflow-x:hidden;
}
a{text-decoration:none;color:inherit}

/* Background orbs */
.bg-orbs{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.orb{position:absolute;border-radius:50%;filter:blur(90px)}
.orb-1{
  width:520px;height:520px;top:-15%;right:-10%;
  background:radial-gradient(circle,rgba(63,203,146,.11) 0%,transparent 65%);
  animation:orbf 10s ease-in-out infinite alternate;
}
.orb-2{
  width:380px;height:380px;bottom:-15%;left:-8%;
  background:radial-gradient(circle,rgba(63,203,146,.06) 0%,transparent 65%);
  animation:orbf 14s ease-in-out infinite alternate-reverse;
}
@keyframes orbf{from{transform:scale(1)}to{transform:scale(1.1) translate(2%,3%)}}

/* Card */
.card{
  position:relative;z-index:1;
  width:100%;max-width:420px;
  animation:fadeUp .45s ease .05s both;
}

/* Logo box */
.logo-box{
  display:flex;align-items:center;justify-content:center;
  width:fit-content;max-width:100%;
  border-radius:20px;overflow:hidden;
  background:linear-gradient(135deg,var(--navy2),var(--navy));
  border:1px solid rgba(63,203,146,.18);
  margin:0 auto 1.375rem;
  box-shadow:0 12px 34px rgba(15,13,54,.55),0 0 44px rgba(0,132,87,.22);
}
/* Sans rembourrage : un logo carre opaque remplit la tuile, un PNG transparent
   laisse voir le degrade. Le !important neutralise la hauteur posee en inline. */
.logo-box img{height:auto !important;max-height:88px;max-width:248px;width:auto;object-fit:contain;display:block}

/* Avatar */
.avatar{
  width:60px;height:60px;border-radius:50%;
  background:linear-gradient(135deg,var(--navy2),var(--navy));
  display:flex;align-items:center;justify-content:center;
  font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;
  color:var(--accent);margin:0 auto 1rem;
  box-shadow:0 0 24px rgba(49,43,129,.3);
}

/* Heading */
.card-head{text-align:center;margin-bottom:1.75rem}
.card-title{
  font-family:'Outfit',sans-serif;
  font-size:1.5rem;font-weight:800;color:var(--text);margin-bottom:.35rem;
}
.card-sub{font-size:.8rem;color:var(--sub);line-height:1.6}

/* Info box */
.info-box{
  display:flex;gap:.75rem;align-items:flex-start;
  background:rgba(63,203,146,.07);
  border:1px solid rgba(63,203,146,.18);
  border-radius:12px;padding:.875rem 1rem;margin-bottom:1.5rem;
}
.info-box i{color:var(--accent);font-size:.88rem;flex-shrink:0;margin-top:.15rem}
.info-box p{font-size:.77rem;color:rgba(255,255,255,.65);line-height:1.6}
.info-box strong{color:var(--accent);font-weight:600}

/* Error */
.ferr{
  display:flex;align-items:flex-start;gap:.55rem;
  background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);
  border-left:3px solid #ef4444;border-radius:10px;
  padding:.7rem .9rem;font-size:.79rem;color:#FCA5A5;margin-bottom:1.125rem;
}
.ferr i{margin-top:.1rem;flex-shrink:0}

/* Field */
.fgrp{margin-bottom:.875rem}
.flabel{display:block;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.5);margin-bottom:.4rem}
.frel{position:relative}
.ficon{
  position:absolute;left:.95rem;top:50%;transform:translateY(-50%);
  color:rgba(255,255,255,.3);font-size:.75rem;pointer-events:none;transition:color .18s;
}
.finput{
  width:100%;padding:.85rem 2.6rem .85rem 2.6rem;
  background:var(--inp);border:1.5px solid rgba(255,255,255,.08);border-radius:12px;
  font-size:.875rem;font-family:'Outfit',sans-serif;color:var(--text);
  outline:none;transition:border-color .2s,box-shadow .2s,background .2s;
}
.finput::placeholder{color:rgba(255,255,255,.2)}
.finput:focus{border-color:var(--navy);background:#161E30;box-shadow:0 0 0 3.5px rgba(49,43,129,.25)}
.finput:focus ~ .ficon,.frel:focus-within .ficon{color:var(--accent)}
.finput.err{border-color:#ef4444}
.finput[readonly]{
  color:rgba(255,255,255,.4);cursor:not-allowed;
  background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.05);
}
.feye{
  position:absolute;right:.9rem;top:50%;transform:translateY(-50%);
  background:none;border:none;color:rgba(255,255,255,.28);cursor:pointer;
  font-size:.78rem;padding:.3rem;display:flex;align-items:center;transition:color .18s;
}
.feye:hover{color:rgba(255,255,255,.7)}

/* Password strength */
.strength-bar{height:3px;border-radius:2px;background:rgba(255,255,255,.08);margin-top:.5rem;overflow:hidden}
.strength-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;width:0}
.strength-txt{font-size:.68rem;color:var(--muted);margin-top:.3rem;min-height:1em;transition:color .2s}

/* Bouton pilule accent */
.fbtn{
  width:100%;padding:.92rem 1.5rem;border:none;border-radius:999px;
  font-size:.97rem;font-weight:700;font-family:'Outfit',sans-serif;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.625rem;
  background:linear-gradient(135deg,#3FCB92 0%,#008457 100%);
  color:#fff;letter-spacing:.01em;
  box-shadow:0 6px 28px rgba(63,203,146,.35),0 2px 8px rgba(0,0,0,.3);
  transition:filter .2s,box-shadow .2s,transform .1s;margin-top:1.25rem;
}
.fbtn:hover{filter:brightness(1.08);box-shadow:0 8px 36px rgba(63,203,146,.5)}
.fbtn:active{transform:scale(.975)}
.fbtn:disabled{opacity:.6;cursor:not-allowed;filter:none}

/* Footer link */
.foot{text-align:center;font-size:.76rem;color:var(--muted);margin-top:1.25rem}
.foot a{color:var(--accent);font-weight:600;transition:opacity .18s}
.foot a:hover{opacity:.75}

/* Copyright */
.copy{
  position:relative;z-index:1;text-align:center;
  font-size:.65rem;color:rgba(255,255,255,.2);margin-top:1.5rem;
}

@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<div class="bg-orbs" aria-hidden="true">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
</div>

<div class="card">

  {{-- Logo --}}
  <div class="logo-box">
    <x-logo variant="full" theme="dark" size="md" />
  </div>

  {{-- Avatar + Heading --}}
  <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>

  <div class="card-head">
    <h1 class="card-title">{{ $greeting }}, {{ $salutationName }} !</h1>
    <p class="card-sub">{{ $t['subtitle'] }}</p>
  </div>

  {{-- Info box --}}
  <div class="info-box">
    <i class="fas fa-envelope-open-text"></i>
    <p><strong>{{ $t['info_title'] }}</strong> {{ $t['info_body'] }}</p>
  </div>

  {{-- Errors --}}
  @if($errors->any())
  <div class="ferr">
    <i class="fas fa-circle-exclamation"></i>
    <span>{{ $errors->first() }}</span>
  </div>
  @endif

  {{-- Form --}}
  <form method="POST" action="{{ route('invitation.activate', $token) }}"
        onsubmit="this.querySelector('button[type=submit]').disabled=true">
    @csrf

    {{-- Email (readonly) --}}
    <div class="fgrp">
      <label class="flabel">{{ $t['email_label'] }}</label>
      <div class="frel">
        <i class="fas fa-envelope ficon"></i>
        <input type="email" class="finput" value="{{ $user->email }}" readonly>
        <span class="feye" style="cursor:default"><i class="fas fa-lock"></i></span>
      </div>
    </div>

    {{-- Password --}}
    <div class="fgrp">
      <label class="flabel" for="password">{{ $t['pw_label'] }}</label>
      <div class="frel">
        <i class="fas fa-lock ficon"></i>
        <input type="password" id="password" name="password" required
               class="finput {{ $errors->has('password') ? 'err' : '' }}"
               placeholder="{{ $t['pw_ph'] }}"
               autocomplete="new-password"
               oninput="checkStrength(this.value)">
        <button type="button" class="feye" onclick="tglPwd('password',this)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
      <div class="strength-bar"><div class="strength-fill" id="sFill"></div></div>
      <div class="strength-txt" id="sTxt">{{ $t['str_ph'] }}</div>
    </div>

    {{-- Confirm password --}}
    <div class="fgrp">
      <label class="flabel" for="password_confirmation">{{ $t['cpw_label'] }}</label>
      <div class="frel">
        <i class="fas fa-lock ficon"></i>
        <input type="password" id="password_confirmation" name="password_confirmation"
               required class="finput"
               placeholder="{{ $t['cpw_ph'] }}"
               autocomplete="new-password">
        <button type="button" class="feye" onclick="tglPwd('password_confirmation',this)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="fbtn">
      <i class="fas fa-unlock-alt"></i>
      {{ $t['btn'] }}
    </button>
  </form>

  <div class="foot">
    {{ $t['login_text'] }}
    <a href="{{ $user->type === 'staff' ? route('staff.login') : route('login') }}">
      {{ $t['login_link'] }}
    </a>
  </div>

</div>

<div class="copy">&copy; {{ date('Y') }} {{ site_name() }}</div>

<script>
const strengths = @json($t['strengths']);
const strPh     = @json($t['str_ph']);
const colors    = ['','#ef4444','#f97316','#eab308','#22c55e','#3FCB92'];
const widths    = ['0%','25%','50%','75%','90%','100%'];

function tglPwd(id, btn) {
  var el   = document.getElementById(id);
  var icon = btn.querySelector('i');
  if (el.type === 'password') { el.type='text';     icon.className='fas fa-eye-slash'; }
  else                        { el.type='password'; icon.className='fas fa-eye'; }
}

function checkStrength(pw) {
  var fill = document.getElementById('sFill');
  var txt  = document.getElementById('sTxt');
  if (!pw) { fill.style.width='0%'; txt.textContent=strPh; txt.style.color='rgba(255,255,255,.28)'; return; }
  var s = 0;
  if (pw.length >= 8)          s++;
  if (pw.length >= 12)         s++;
  if (/[A-Z]/.test(pw))        s++;
  if (/[0-9]/.test(pw))        s++;
  if (/[^A-Za-z0-9]/.test(pw)) s++;
  s = Math.min(s, 5);
  fill.style.width      = widths[s];
  fill.style.background = colors[s] || 'transparent';
  txt.textContent       = strengths[s] || strPh;
  txt.style.color       = s > 0 ? colors[s] : 'rgba(255,255,255,.28)';
}

/* Si l'utilisateur ouvre l'email et laisse l'onglet inactif un moment avant de
   soumettre, le jeton CSRF rendu au chargement peut expirer avec la session
   ("Page Expired" / lien perçu comme invalide). On le rafraîchit silencieusement
   en arrière-plan toutes les 100s pour que le champ caché reste toujours valide. */
(function () {
  var REFRESH_URL = '{{ route("csrf.refresh") }}';
  setInterval(function () {
    fetch(REFRESH_URL, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j || !j.token) return;
        var field = document.querySelector('input[name="_token"]');
        if (field) field.value = j.token;
      })
      .catch(function () { /* tant pis, on réessaiera au prochain intervalle */ });
  }, 100000);
})();
</script>
</body>
</html>
