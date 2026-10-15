<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#008457">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ site_name() }} Admin">
<meta name="mobile-web-app-capable" content="yes">
@auth
  @if(Auth::user()->hasAnyRole(['admin','super-admin']))
<link rel="manifest" href="/admin-manifest.json">
  @else
<link rel="manifest" href="/manifest.json">
  @endif
@else
<link rel="manifest" href="/admin-manifest.json">
@endauth
<link rel="apple-touch-icon" sizes="180x180" href="/site-icon-180.png">
<link rel="icon" type="image/png" sizes="192x192" href="/site-icon-192.png">
<title>@yield('title','Dashboard') — {{ site_name() }}</title>
<link rel="icon" type="image/png" sizes="32x32" href="/site-icon-32.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700;9..144,800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/fontawesome/css/all.min.css') }}">
<style>
/* ═══════════════════════════════════════════════
  Mellenthin Financial — DESIGN SYSTEM v2
   ═══════════════════════════════════════════════ */
:root {
  --c-navy:       #312B81;
  --c-navy-2:     #3C3694;
  --c-navy-3:     #006A45;
  --c-accent:       #008457;
  --c-on-accent:  #FFFFFF;  /* texte sur aplat d accent */
  --c-accent-d:     #006A45;
  --c-accent-l:     #3FCB92;
  --c-bg:         #F7F8F9;
  --c-surface:    #FFFFFF;
  --c-border:     #DBDDDE;
  --c-text:       #312B81;
  --c-muted:      #95A3B1;
  --c-green:      #059669;
  --c-green-l:    #D1FAE5;
  --c-red:        #DC2626;
  --c-red-l:      #FEE2E2;
  --c-amber:      #D97706;
  --c-amber-l:    #FEF3C7;
  --c-blue:       #2563EB;
  --c-blue-l:     #DBEAFE;
  --c-violet:     #7C3AED;
  --c-violet-l:   #EDE9FE;
  --sidebar-w:    260px;
  --topbar-h:     64px;
  --radius:       12px;
  --radius-sm:    8px;
  --shadow:       0 1px 3px rgba(49,43,129,.06), 0 4px 16px rgba(49,43,129,.07);
  --shadow-sm:    0 1px 2px rgba(49,43,129,.05);
  --transition:   all .2s ease;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Outfit',sans-serif; font-weight:400; background:var(--c-bg); color:var(--c-text); font-size:.875rem; line-height:1.6; min-height:100vh; -webkit-font-smoothing:antialiased; }
a { text-decoration:none; }

/* ─── SCROLLBAR ─── */
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:rgba(0,0,0,.15); border-radius:99px; }

/* ══════════════════
   SIDEBAR
   ══════════════════ */
.sidebar {
  /* Fond bleu profond : l accent doit s eclaircir pour rester lisible. */
  --c-accent:#3FCB92;
  position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh;
  background:var(--c-navy);
  display:flex; flex-direction:column; z-index:300;
  overflow-y:auto; transition:transform .3s cubic-bezier(.4,0,.2,1);
}
.sidebar-brand {
  padding:1.25rem 1.5rem;
  border-bottom:1px solid rgba(255,255,255,.06);
  flex-shrink:0;
}
.sidebar-brand img { height:34px; width:auto; }

.sidebar-user {
  margin:1rem 1rem .25rem;
  padding:.875rem 1rem;
  background:rgba(255,255,255,.05);
  border-radius:var(--radius-sm);
  display:flex; align-items:center; gap:.75rem;
}
.sidebar-user__avatar {
  width:36px; height:36px; border-radius:50%;
  background:linear-gradient(135deg, var(--c-accent), var(--c-accent-d));
  display:flex; align-items:center; justify-content:center;
  font-weight:800; font-size:.8125rem; color:var(--c-navy); flex-shrink:0;
}
.sidebar-user__name { font-size:.8125rem; font-weight:600; color:#fff; line-height:1.3; }
.sidebar-user__role { font-size:.7rem; color:rgba(255,255,255,.4); margin-top:.1rem; }

.sidebar-nav { padding:.5rem 0; flex:1; }
.sidebar-label {
  display:block;
  padding:.625rem 1.5rem .3rem;
  font-size:.6rem; font-weight:700; letter-spacing:.1em;
  text-transform:uppercase; color:rgba(255,255,255,.22);
  margin-top:.25rem;
}
.sidebar-link {
  display:flex; align-items:center; gap:.75rem;
  padding:.6rem 1.25rem .6rem 1.5rem;
  color:rgba(255,255,255,.55);
  font-size:.8375rem; font-weight:500;
  border-left:3px solid transparent;
  transition:var(--transition);
  position:relative;
}
.sidebar-link .icon { width:18px; text-align:center; font-size:.8rem; flex-shrink:0; }
.sidebar-link:hover { color:#fff; background:rgba(255,255,255,.04); border-left-color:rgba(255,255,255,.15); }
.sidebar-link.active { color:var(--c-accent); background:rgba(0,132,87,.1); border-left-color:var(--c-accent); font-weight:600; }
.sidebar-link.active .icon { color:var(--c-accent); }

.sidebar-footer {
  padding:1rem 1.5rem;
  border-top:1px solid rgba(255,255,255,.06);
  flex-shrink:0;
}
.sidebar-logout {
  display:flex; align-items:center; gap:.75rem;
  width:100%; background:none; border:none;
  color:rgba(255,255,255,.4); font-size:.8125rem; font-weight:500;
  cursor:pointer; padding:.5rem 0; transition:color .2s;
  font-family:inherit;
}
.sidebar-logout:hover { color:#f87171; }
.sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:299; }
.sidebar-overlay.show { display:block; }

/* ══════════════════
   MAIN WRAPPER
   ══════════════════ */
.main-wrap { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }

/* ══════════════════
   TOPBAR
   ══════════════════ */
.topbar {
  height:var(--topbar-h);
  background:var(--c-surface);
  border-bottom:1px solid var(--c-border);
  display:flex; align-items:center; justify-content:space-between;
  padding:0 1.75rem;
  position:sticky; top:0; z-index:200;
  box-shadow:0 1px 0 rgba(0,0,0,.04);
}
.topbar-left { display:flex; align-items:center; gap:1rem; }
.topbar-toggle { background:none; border:none; color:var(--c-muted); font-size:1rem; cursor:pointer; display:none; padding:.25rem; }
.topbar-title { font-size:.9375rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--c-navy); }
.topbar-right { display:flex; align-items:center; gap:1rem; }
.topbar-badge {
  width:36px; height:36px; border-radius:var(--radius-sm);
  border:1.5px solid var(--c-border); background:var(--c-surface);
  display:flex; align-items:center; justify-content:center;
  color:var(--c-muted); font-size:.875rem; cursor:pointer; transition:var(--transition);
}
.topbar-badge:hover { background:var(--c-bg); color:var(--c-navy); }
.topbar-avatar {
  width:36px; height:36px; border-radius:50%;
  background:linear-gradient(135deg,var(--c-navy),var(--c-navy-3));
  display:flex; align-items:center; justify-content:center;
  color:var(--c-accent); font-weight:800; font-size:.8125rem;
}

/* ══════════════════
   CONTENT AREA
   ══════════════════ */
.content-area { padding:1.75rem; flex:1; }

/* ══════════════════
   PAGE HEADER
   ══════════════════ */
.page-hdr { margin-bottom:1.75rem; }
.page-hdr h1, .page-hdr h2, .page-hdr h3, .page-hdr h4 {
  font-size:1.125rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--c-navy); margin:0 0 .25rem;
}
.page-hdr p { font-size:.8125rem; color:var(--c-muted); margin:0; }

/* ══════════════════
   CARDS
   ══════════════════ */
.card-pro {
  background:var(--c-surface);
  border-radius:var(--radius);
  border:1px solid var(--c-border);
  box-shadow:var(--shadow-sm);
  overflow:hidden;
}
.card-pro-hdr {
  padding:.9375rem 1.25rem;
  border-bottom:1px solid var(--c-border);
  display:flex; align-items:center; justify-content:space-between;
}
.card-pro-title {
  font-size:.8125rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--c-navy);
  display:flex; align-items:center; gap:.5rem;
}
.card-pro-title .icon-dot {
  width:6px; height:6px; border-radius:50%; background:var(--c-accent); flex-shrink:0;
}
.card-pro-body { padding:1.25rem; }

/* ══════════════════
   METRIC CARDS
   ══════════════════ */
.metric-card {
  background:var(--c-surface);
  border-radius:var(--radius);
  border:1px solid var(--c-border);
  padding:1.25rem 1.375rem;
  box-shadow:var(--shadow-sm);
  position:relative; overflow:hidden;
  transition:var(--transition);
}
.metric-card:hover { box-shadow:var(--shadow); transform:translateY(-1px); }
.metric-card__icon {
  width:44px; height:44px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size:1rem; margin-bottom:.875rem;
}
.metric-card__val { font-size:1.75rem; font-weight:800; color:var(--c-navy); line-height:1; margin-bottom:.25rem; }
.metric-card__lbl { font-size:.75rem; color:var(--c-muted); font-weight:500; }
.metric-card__accent {
  position:absolute; top:0; right:0;
  width:80px; height:80px; border-radius:0 var(--radius) 0 80px;
  opacity:.06;
}
/* Icon color variants */
.mi-navy  { background:#EEF2FF; color:var(--c-navy); }
.mi-accent  { background:#E9F6F0; color:var(--c-accent-d); }
.mi-green { background:var(--c-green-l); color:var(--c-green); }
.mi-red   { background:var(--c-red-l); color:var(--c-red); }
.mi-blue  { background:var(--c-blue-l); color:var(--c-blue); }
.mi-violet{ background:var(--c-violet-l); color:var(--c-violet); }
.mi-amber { background:var(--c-amber-l); color:var(--c-amber); }
.mi-gray  { background:#F3F4F6; color:#6B7280; }

/* ══════════════════
   STATUS BADGES
   ══════════════════ */
.badge-status {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.25rem .7rem;
  border-radius:999px; font-size:.7rem; font-weight:600;
  white-space:nowrap;
}
.badge-status::before {
  content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0;
  background:currentColor;
}
.bs-gray    { background:#F3F4F6; color:#6B7280; }
.bs-amber   { background:var(--c-amber-l); color:var(--c-amber); }
.bs-blue    { background:var(--c-blue-l); color:var(--c-blue); }
.bs-violet  { background:var(--c-violet-l); color:var(--c-violet); }
.bs-green   { background:var(--c-green-l); color:var(--c-green); }
.bs-emerald { background:#D1FAE5; color:#065F46; }
.bs-red     { background:var(--c-red-l); color:var(--c-red); }
/* Map de statusColor() → classe */
.bs-secondary { background:#F3F4F6; color:#6B7280; }
.bs-warning   { background:var(--c-amber-l); color:var(--c-amber); }
.bs-info      { background:var(--c-blue-l); color:var(--c-blue); }
.bs-primary   { background:var(--c-violet-l); color:var(--c-violet); }
.bs-success   { background:var(--c-green-l); color:var(--c-green); }
.bs-dark      { background:#1F2937; color:#F9FAFB; }
.bs-danger    { background:var(--c-red-l); color:var(--c-red); }
/* Backward compat sb- classes */
.sb-secondary,.sb--default { background:#F3F4F6; color:#6B7280; }
.sb-warning,.sb--pending   { background:var(--c-amber-l); color:var(--c-amber); }
.sb-info,.sb--review       { background:var(--c-blue-l); color:var(--c-blue); }
.sb-primary                { background:var(--c-violet-l); color:var(--c-violet); }
.sb-success,.sb--approved  { background:var(--c-green-l); color:var(--c-green); }
.sb-dark                   { background:#1F2937; color:#F9FAFB; }
.sb-danger,.sb--rejected   { background:var(--c-red-l); color:var(--c-red); }
.sb { display:inline-block; padding:.25rem .65rem; border-radius:999px; font-size:.7rem; font-weight:600; white-space:nowrap; }

/* ══════════════════
   PAGINATION
   ══════════════════ */
.pg-pro {
  display:flex; align-items:center; justify-content:space-between;
  flex-wrap:wrap; gap:.75rem;
}
.pg-pro__summary { font-size:.78rem; color:var(--c-muted); margin:0; }
.pg-pro__summary strong { color:var(--c-navy); font-weight:700; }
.pg-pro__list {
  display:flex; align-items:center; gap:.3rem;
  list-style:none; margin:0; padding:0;
}
.pg-pro__item { display:flex; }
.pg-pro__link {
  display:flex; align-items:center; justify-content:center;
  min-width:32px; height:32px; padding:0 .5rem;
  border-radius:var(--radius-sm); border:1.5px solid var(--c-border);
  background:var(--c-surface); color:var(--c-text);
  font-size:.78rem; font-weight:600;
  text-decoration:none; cursor:pointer;
  transition:var(--transition);
}
a.pg-pro__link:hover { background:var(--c-bg); border-color:#94A3B8; color:var(--c-navy); }
.pg-pro__item--active .pg-pro__link {
  background:var(--c-navy); border-color:var(--c-navy); color:#fff;
}
.pg-pro__item--disabled .pg-pro__link { color:#C4CADC; cursor:not-allowed; }
.pg-pro__item--dots .pg-pro__link { border:none; background:none; color:var(--c-muted); }
@media(max-width:640px) {
  .pg-pro { flex-direction:column; align-items:stretch; text-align:center; }
  .pg-pro__list { justify-content:center; flex-wrap:wrap; }
}

/* ══════════════════
   PROFESSIONAL TABLE
   ══════════════════ */
.pro-table { width:100%; border-collapse:collapse; }
.pro-table thead th {
  padding:.75rem 1rem;
  font-size:.7rem; font-weight:700; color:var(--c-muted);
  text-transform:uppercase; letter-spacing:.06em;
  background:#FAFBFC;
  border-bottom:1px solid var(--c-border);
  text-align:left; white-space:nowrap;
}
.pro-table tbody td {
  padding:.875rem 1rem;
  font-size:.8375rem; color:var(--c-text);
  border-bottom:1px solid #F3F4F6;
  vertical-align:middle;
}
.pro-table tbody tr:last-child td { border-bottom:none; }
.pro-table tbody tr:hover td { background:#F8FAFF; }
.pro-table .cell-mono { font-family:'Courier New',monospace; font-weight:700; font-size:.78rem; color:var(--c-navy); }
.pro-table .cell-name { font-weight:600; color:var(--c-navy); }
.pro-table .cell-sub  { font-size:.75rem; color:var(--c-muted); margin-top:.15rem; }
.pro-table .cell-amount { font-weight:700; font-size:.9rem; }

/* ══════════════════
   BUTTONS
   ══════════════════ */
.btn-navy {
  display:inline-flex; align-items:center; gap:.4rem;
  background:var(--c-navy); color:#fff;
  border:none; border-radius:var(--radius-sm);
  padding:.525rem 1.125rem; font-size:.8375rem; font-weight:600;
  cursor:pointer; transition:var(--transition); font-family:inherit;
  text-decoration:none;
}
.btn-navy:hover { background:var(--c-navy-2); color:#fff; }
.btn-navy:active { transform:scale(.98); }

.btn-accent {
  display:inline-flex; align-items:center; gap:.4rem;
  background:var(--c-accent); color:var(--c-on-accent);
  border:none; border-radius:var(--radius-sm);
  padding:.525rem 1.125rem; font-size:.8375rem; font-weight:700;
  cursor:pointer; transition:var(--transition); font-family:inherit;
  text-decoration:none;
}
.btn-accent:hover { background:var(--c-accent-d); color:#fff; }

.btn-ghost {
  display:inline-flex; align-items:center; gap:.4rem;
  background:transparent; color:var(--c-text);
  border:1.5px solid var(--c-border); border-radius:var(--radius-sm);
  padding:.5rem 1rem; font-size:.8375rem; font-weight:500;
  cursor:pointer; transition:var(--transition); font-family:inherit;
  text-decoration:none;
}
.btn-ghost:hover { background:var(--c-bg); border-color:#CBD5E1; color:var(--c-navy); }

.btn-icon {
  display:inline-flex; align-items:center; justify-content:center;
  width:32px; height:32px; border-radius:var(--radius-sm);
  border:1.5px solid var(--c-border); background:var(--c-surface);
  color:var(--c-muted); font-size:.8rem;
  cursor:pointer; transition:var(--transition); text-decoration:none;
}
.btn-icon:hover { background:var(--c-bg); color:var(--c-navy); border-color:#94A3B8; }
.btn-icon-danger:hover { background:var(--c-red-l); color:var(--c-red); border-color:var(--c-red); }
.btn-icon-primary:hover { background:var(--c-blue-l); color:var(--c-blue); border-color:var(--c-blue); }
.btn-icon-success:hover { background:var(--c-green-l); color:var(--c-green); border-color:var(--c-green); }

.btn-sm-pro { padding:.375rem .75rem; font-size:.78rem; }
/* Bootstrap compat */
.b-navy { background:var(--c-navy); color:#fff; border:none; border-radius:var(--radius-sm); padding:.525rem 1.125rem; font-size:.8375rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; transition:var(--transition); }
.b-navy:hover { background:var(--c-navy-2); color:#fff; }
.b-accent { background:var(--c-accent); color:var(--c-on-accent); border:none; border-radius:var(--radius-sm); padding:.525rem 1.125rem; font-size:.8375rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; transition:var(--transition); }
.btn-xs { padding:.2rem .5rem; font-size:.75rem; border-radius:6px; }

/* ══════════════════
   FORM CONTROLS
   ══════════════════ */
.form-label-pro { font-size:.78rem; font-weight:600; color:var(--c-navy); display:block; margin-bottom:.375rem; }
.form-control-pro {
  width:100%; padding:.6rem .875rem;
  background:var(--c-surface); border:1.5px solid var(--c-border);
  border-radius:var(--radius-sm); font-size:.8375rem; color:var(--c-text);
  font-family:inherit; transition:var(--transition);
  appearance:none;
}
.form-control-pro:focus { outline:none; border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(0,132,87,.12); }
.form-control-pro::placeholder { color:#C4CADC; }
.form-help { font-size:.73rem; color:var(--c-muted); margin-top:.3rem; }

.form-section { margin-bottom:1.5rem; }
.form-section-title {
  font-size:.7rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.08em; color:var(--c-muted);
  padding:.5rem 0 .75rem; border-bottom:1px solid var(--c-border);
  margin-bottom:1rem;
}

/* ══════════════════
   SUMMARY BOX
   ══════════════════ */
.summary-box {
  background:linear-gradient(135deg, var(--c-navy) 0%, var(--c-navy-3) 100%);
  border-radius:var(--radius); padding:1.5rem;
  color:#fff;
}
.summary-box__label { font-size:.72rem; color:rgba(255,255,255,.55); font-weight:500; margin-bottom:.25rem; }
.summary-box__val { font-size:1.375rem; font-weight:800; color:var(--c-accent); }
.summary-box__sub { font-size:.73rem; color:rgba(255,255,255,.45); margin-top:.2rem; }

/* ══════════════════
   TIMELINE / STEPS
   ══════════════════ */
.steps-bar { display:flex; align-items:center; gap:0; }
.step-item { flex:1; display:flex; flex-direction:column; align-items:center; position:relative; }
.step-item::before {
  content:''; position:absolute; top:14px; left:50%; right:-50%;
  height:2px; background:var(--c-border); z-index:0;
}
.step-item:last-child::before { display:none; }
.step-dot {
  width:28px; height:28px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:.72rem; font-weight:700; position:relative; z-index:1;
  background:var(--c-border); color:var(--c-muted);
  border:2px solid var(--c-border);
  transition:var(--transition);
}
.step-dot.done { background:var(--c-accent); color:var(--c-on-accent); border-color:var(--c-accent); }
.step-dot.current { background:var(--c-navy); color:#3FCB92; border-color:#3FCB92; }
.step-label { font-size:.62rem; text-align:center; color:var(--c-muted); margin-top:.4rem; max-width:65px; line-height:1.3; }
.step-label.done,.step-label.current { color:var(--c-navy); font-weight:600; }

/* ══════════════════
   FLASH MESSAGES
   ══════════════════ */
.flash { border-radius:var(--radius-sm); padding:.875rem 1.125rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.75rem; font-size:.8375rem; font-weight:500; }
.flash-ok  { background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; }
.flash-err { background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }
.flash-warn{ background:#FFFBEB; color:#92400E; border:1px solid #FDE68A; }
/* backward compat */
.da.da--ok  { background:#ECFDF5; color:#065F46; }
.da.da--err { background:#FEF2F2; color:#991B1B; }
.da { padding:.875rem 1.125rem; border-radius:var(--radius-sm); font-size:.8375rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.75rem; }

/* ══════════════════
   FILTER BAR
   ══════════════════ */
.filter-bar {
  background:var(--c-surface); border-radius:var(--radius);
  border:1px solid var(--c-border); padding:.875rem 1.125rem;
  margin-bottom:1.25rem;
  display:flex; flex-wrap:wrap; gap:.625rem; align-items:center;
}
.filter-bar input, .filter-bar select {
  height:36px; border:1.5px solid var(--c-border);
  border-radius:var(--radius-sm); padding:0 .75rem;
  font-size:.8125rem; color:var(--c-text);
  background:var(--c-bg); font-family:inherit;
  transition:var(--transition); min-width:0;
}
.filter-bar input:focus, .filter-bar select:focus {
  outline:none; border-color:var(--c-accent); background:var(--c-surface);
  box-shadow:0 0 0 3px rgba(0,132,87,.1);
}
.filter-bar input::placeholder { color:#C4CADC; }

/* ══════════════════
   TABS
   ══════════════════ */
.tabs-pro { display:flex; border-bottom:2px solid var(--c-border); margin-bottom:1.5rem; gap:.25rem; }
.tab-btn {
  padding:.625rem 1.125rem; font-size:.8125rem; font-weight:600;
  color:var(--c-muted); border:none; background:none;
  border-bottom:2px solid transparent; margin-bottom:-2px;
  cursor:pointer; transition:var(--transition); border-radius:var(--radius-sm) var(--radius-sm) 0 0;
}
.tab-btn:hover { color:var(--c-navy); background:rgba(0,0,0,.02); }
.tab-btn.active { color:var(--c-navy); border-bottom-color:var(--c-accent); }

/* ══════════════════
   STAT CARD (legacy)
   ══════════════════ */
.stat-c { background:var(--c-surface); border-radius:var(--radius); border:1px solid var(--c-border); padding:1.25rem; box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:1rem; }
.stat-c__icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.125rem; flex-shrink:0; }
.ic--blue   { background:#EFF6FF; color:var(--c-blue); }
.ic--accent   { background:#E9F6F0; color:var(--c-accent-d); }
.ic--green  { background:var(--c-green-l); color:var(--c-green); }
.ic--red    { background:var(--c-red-l); color:var(--c-red); }
.ic--navy   { background:#EEF2FF; color:var(--c-navy); }
.ic--purple { background:var(--c-violet-l); color:var(--c-violet); }
.ic--gray   { background:#F3F4F6; color:#6B7280; }
.ic--teal   { background:#F0FDFA; color:#0D9488; }
.stat-c__val { font-size:1.5rem; font-weight:800; color:var(--c-navy); line-height:1; }
.stat-c__lbl { font-size:.75rem; color:var(--c-muted); margin-top:.2rem; }

/* ══════════════════════════════════════
   RESPONSIVE GRID SYSTEM
   ══════════════════════════════════════ */
.metrics-grid {
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:1rem;
  margin-bottom:1.5rem;
}
.metrics-grid-3 {
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:1rem;
  margin-bottom:1.5rem;
}
.metrics-grid-2 {
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:1rem;
  margin-bottom:1.5rem;
}

/* Responsive table wrapper */
.table-responsive-pro {
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
  border-radius:0 0 var(--radius) var(--radius);
}
.table-responsive-pro::-webkit-scrollbar { height:4px; }
.table-responsive-pro::-webkit-scrollbar-thumb { background:var(--c-border); border-radius:99px; }

/* Page header flex */
.page-hdr-row {
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:1rem;
  flex-wrap:wrap;
  margin-bottom:1.75rem;
}
.page-hdr-row .page-hdr { margin-bottom:0; }
.page-hdr-actions { display:flex; align-items:center; gap:.625rem; flex-wrap:wrap; flex-shrink:0; }

/* Card header responsive */
.card-pro-hdr { flex-wrap:wrap; gap:.5rem; }

/* ══════════════════════════════════════
   MOBILE ≤ 991px (tablet + phone)
   ══════════════════════════════════════ */
@media(max-width:991px) {
  .sidebar { transform:translateX(-100%); }
  .sidebar.open { transform:translateX(0); }
  .main-wrap { margin-left:0; }
  .topbar-toggle { display:flex !important; }
  .content-area { padding:1.25rem; }

  /* Metric grids → 2 colonnes */
  .metrics-grid   { grid-template-columns:repeat(2,1fr); }
  .metrics-grid-3 { grid-template-columns:repeat(2,1fr); }

  /* Notification panel pleine largeur */
  #notifPanel { width:calc(100vw - 2rem) !important; right:-1rem !important; }

  /* Tables avec scroll horizontal */
  .pro-table { min-width:600px; }

  /* Page header actions sur nouvelle ligne */
  .page-hdr-row { flex-direction:column; }
  .page-hdr-actions { width:100%; }
}

/* ══════════════════════════════════════
   MOBILE ≤ 640px (téléphone)
   ══════════════════════════════════════ */
@media(max-width:640px) {
  :root { --topbar-h:56px; }

  .content-area { padding:.875rem; }

  /* Metric grids → 2 colonnes compactes */
  .metrics-grid,
  .metrics-grid-3,
  .metrics-grid-2 { grid-template-columns:repeat(2,1fr); gap:.625rem; }

  .metric-card { padding:1rem 1.125rem; }
  .metric-card__val { font-size:1.375rem; }
  .metric-card__icon { width:36px; height:36px; font-size:.875rem; margin-bottom:.625rem; }

  /* Topbar compact */
  .topbar { padding:0 1rem; }
  .topbar-title { font-size:.875rem; }
  .topbar-right { gap:.5rem; }

  /* Boutons pleine largeur dans les actions de page */
  .page-hdr-actions { width:100%; }
  .page-hdr-actions .btn-accent,
  .page-hdr-actions .btn-navy { width:100%; justify-content:center; }

  /* Filter bar: colonne */
  .filter-bar { flex-direction:column; align-items:stretch; }
  .filter-bar input,
  .filter-bar select { width:100%; }

  /* Card header: colonne */
  .card-pro-hdr { flex-direction:column; align-items:flex-start; }
  .card-pro-hdr > * { width:100%; }

  /* Tables : carte sur mobile — annule le min-width du breakpoint tablet */
  .pro-table { min-width:0 !important; width:100%; display:block; }
  .pro-table tbody { display:block; width:100%; }
  .pro-table thead { display:none; }
  .pro-table tbody tr {
    display:block;
    border:1px solid var(--c-border);
    border-radius:var(--radius-sm);
    margin-bottom:.625rem;
    padding:.75rem;
    background:var(--c-surface);
  }
  .pro-table tbody td {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:.3rem 0;
    border-bottom:1px solid #F3F4F6;
    font-size:.8rem;
  }
  .pro-table tbody td:last-child { border-bottom:none; }
  .pro-table tbody td[data-label]::before {
    content:attr(data-label);
    font-size:.7rem; font-weight:700; color:var(--c-muted);
    text-transform:uppercase; letter-spacing:.05em;
    flex-shrink:0; margin-right:.75rem;
  }
  .pro-table tbody tr:hover td { background:transparent; }
  .table-responsive-pro { overflow-x:visible; border-radius:0; }

  /* Cellule actions : sans label, boutons touch-friendly */
  .pro-table tbody td:last-child {
    justify-content:flex-end;
    gap:.5rem;
    flex-wrap:wrap;
    padding-top:.5rem;
    padding-bottom:.375rem;
  }
  .pro-table tbody td:last-child::before { display:none; }
  .pro-table tbody td:last-child > div,
  .pro-table tbody td:last-child > a,
  .pro-table tbody td:last-child > form { flex-shrink:0; }
  .pro-table tbody td:last-child .btn-icon { width:40px; height:40px; font-size:.9rem; }
  .pro-table tbody td:last-child .btn-accent.btn-sm-pro,
  .pro-table tbody td:last-child .btn-navy.btn-sm-pro { min-height:40px; padding:.55rem 1rem; }

  /* Transfer card header: empilé sur mobile */
  .trf-head { grid-template-columns:auto 1fr !important; }
  .trf-head > div:nth-child(3),
  .trf-head > div:nth-child(4) { grid-column:1/-1; display:flex; justify-content:space-between; align-items:center; }

  /* Sidebar: légèrement rétrécie pour confort */
  :root { --sidebar-w:280px; }

  /* Summary box compact */
  .summary-box { padding:1rem; }
  .summary-box__val { font-size:1.125rem; }

  /* Flash messages */
  .flash { flex-direction:column; align-items:flex-start; gap:.375rem; font-size:.8rem; }

  /* Tabs scroll horizontal */
  .tabs-pro { overflow-x:auto; overflow-y:hidden; white-space:nowrap; flex-wrap:nowrap; padding-bottom:2px; }
  .tab-btn { flex-shrink:0; }

  /* Steps bar compact */
  .step-label { font-size:.55rem; max-width:50px; }
}

/* ══════════════════════════════════════
   MOBILE ≤ 400px (petits écrans)
   ══════════════════════════════════════ */
@media(max-width:400px) {
  .metrics-grid,
  .metrics-grid-3 { grid-template-columns:1fr 1fr; gap:.5rem; }
  .metrics-grid-2 { grid-template-columns:1fr; }
  .metric-card__val { font-size:1.2rem; }
  .content-area { padding:.75rem; }
}

</style>
@auth
  @if(Auth::user()->hasRole('client'))
    @vite(['resources/css/client.css','resources/js/client.js'])
  @endif
@endauth
@stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="sidebar-brand">
    <x-logo variant="full" theme="dark" size="sm" href="{{ route('home',['locale'=>app()->getLocale()]) }}" />
  </div>

  @auth
  <div class="sidebar-user">
    <div class="sidebar-user__avatar">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
    <div>
      <div class="sidebar-user__name">{{ Str::limit(Auth::user()->name,20) }}</div>
      <div class="sidebar-user__role">{{ ucfirst(str_replace('-',' ',Auth::user()->getRoleNames()->first()??'')) }}</div>
    </div>
  </div>
  @endauth

  <nav class="sidebar-nav">
  @auth

    {{-- ══ CLIENT ══ --}}
    @role('client')
      <span class="sidebar-label">Navigation</span>
      <a href="{{ route('client.dashboard') }}"
         class="sidebar-link {{ request()->routeIs('client.dashboard') ? 'active':'' }}">
        <i class="fas fa-th-large icon"></i> Tableau de bord
      </a>
      <span class="sidebar-label">Compte</span>
      <a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="sidebar-link">
        <i class="fas fa-globe icon"></i> Retour au site
      </a>
    @endrole

    {{-- ══ SUPER-ADMIN ══ --}}
    @role('super-admin')
      <span class="sidebar-label">Tableau de bord</span>
      <a href="{{ route('super-admin.dashboard') }}"
         class="sidebar-link {{ request()->routeIs('super-admin.dashboard') ? 'active':'' }}">
        <i class="fas fa-chart-pie icon"></i> Vue d'ensemble
      </a>

      <span class="sidebar-label">Site</span>
      <a href="{{ route('admin.site-contacts.edit') }}"
         class="sidebar-link {{ request()->routeIs('admin.site-contacts*') ? 'active':'' }}">
        <i class="fas fa-map-marker-alt icon"></i> Coordonnées
      </a>
      <a href="{{ route('admin.team.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.team*') ? 'active':'' }}">
        <i class="fas fa-users icon"></i> Équipe (À propos)
      </a>
      <a href="{{ route('admin.social-links.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.social-links*') ? 'active':'' }}">
        <i class="fas fa-share-alt icon"></i> Réseaux sociaux
      </a>
      <a href="{{ route('admin.languages.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.languages*') ? 'active':'' }}">
        <i class="fas fa-language icon"></i> Langues
      </a>
      <a href="{{ route('admin.currencies.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.currencies*') ? 'active':'' }}">
        <i class="fas fa-money-bill-wave icon"></i> Devises
      </a>

      @hasanyrole(['super-admin'])
      <span class="sidebar-label">Administration</span>
      <a href="{{ route('super-admin.roles') }}"
         class="sidebar-link {{ request()->routeIs('super-admin.roles') ? 'active':'' }}">
        <i class="fas fa-shield-alt icon"></i> Rôles &amp; Permissions
      </a>
      <a href="{{ route('admin.users') }}"
         class="sidebar-link {{ request()->routeIs('admin.users') ? 'active':'' }}">
        <i class="fas fa-users icon"></i> Utilisateurs
      </a>
      @php $saKycPendingCount = \App\Models\KycVerification::where('status', \App\Models\KycVerification::STATUS_EN_ATTENTE)->count(); @endphp
      <a href="{{ route('admin.kyc.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.kyc.index','admin.kyc.show') ? 'active':'' }}">
        <i class="fas fa-id-card icon"></i> Vérifications KYC
        @if($saKycPendingCount > 0)
        <span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $saKycPendingCount }}</span>
        @endif
      </a>
      <a href="{{ route('admin.kyc.fields') }}"
         class="sidebar-link {{ request()->routeIs('admin.kyc.fields*') ? 'active':'' }}">
        <i class="fas fa-sliders icon"></i> Champs KYC
      </a>
      @endhasanyrole

      <span class="sidebar-label">Gestion financière</span>
      @hasanyrole(['admin', 'super-admin'])
      <a href="{{ route('admin.accounts.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.accounts*') ? 'active':'' }}">
        <i class="fas fa-wallet icon"></i> Comptes clients
      </a>
      <a href="{{ route('admin.transfers.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.transfers*') ? 'active':'' }}">
        <i class="fas fa-exchange-alt icon"></i> Transferts
      </a>
      <a href="{{ route('admin.card-requests.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.card-requests*') ? 'active':'' }}">
        <i class="fas fa-credit-card icon"></i> Demandes de carte
        @php $cardReqPending = \App\Models\CardRequest::where('status','pending')->count(); @endphp
        @if($cardReqPending)<span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $cardReqPending }}</span>@endif
      </a>
      <a href="{{ route('admin.invoices.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.invoices*') ? 'active':'' }}">
        <i class="fas fa-file-invoice icon"></i> Factures
      </a>
      @endhasanyrole

      @hasanyrole(['admin', 'super-admin'])
      @php $saSupUnread = \App\Models\SupportMessage::where('sender_type','client')->whereNull('read_at')->count(); @endphp
      <a href="{{ route('admin.support.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.support*') ? 'active':'' }}">
        <i class="fas fa-comments icon"></i> Support
        @if($saSupUnread > 0)
        <span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $saSupUnread }}</span>
        @endif
      </a>
      @endhasanyrole

      <span class="sidebar-label">Compte</span>
      @role('super-admin')
      <a href="{{ route('super-admin.profile') }}"
         class="sidebar-link {{ request()->routeIs('super-admin.profile*') ? 'active':'' }}">
        <i class="fas fa-user-circle icon"></i> Mon profil
      </a>
      @endrole
      <a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="sidebar-link">
        <i class="fas fa-globe icon"></i> Retour au site
      </a>
    @endrole

    {{-- ══ ADMIN ══ --}}
    @role('admin')
      <span class="sidebar-label">Tableau de bord</span>
      <a href="{{ route('admin.dashboard') }}"
         class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active':'' }}">
        <i class="fas fa-chart-pie icon"></i> Vue d'ensemble
      </a>

      <span class="sidebar-label">Gestion</span>
      @hasanyrole(['admin', 'super-admin'])
      <a href="{{ route('admin.users') }}"
         class="sidebar-link {{ request()->routeIs('admin.users') ? 'active':'' }}">
        <i class="fas fa-users icon"></i> Clients &amp; Utilisateurs
      </a>
      @php $kycPendingCount = \App\Models\KycVerification::where('status', \App\Models\KycVerification::STATUS_EN_ATTENTE)->count(); @endphp
      <a href="{{ route('admin.kyc.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.kyc.index','admin.kyc.show') ? 'active':'' }}">
        <i class="fas fa-id-card icon"></i> Vérifications KYC
        @if($kycPendingCount > 0)
        <span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $kycPendingCount }}</span>
        @endif
      </a>
      <a href="{{ route('admin.kyc.fields') }}"
         class="sidebar-link {{ request()->routeIs('admin.kyc.fields*') ? 'active':'' }}">
        <i class="fas fa-sliders icon"></i> Champs KYC
      </a>
      <a href="{{ route('admin.accounts.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.accounts*') ? 'active':'' }}">
        <i class="fas fa-wallet icon"></i> Comptes clients
      </a>
      <a href="{{ route('admin.transfers.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.transfers*') ? 'active':'' }}">
        <i class="fas fa-exchange-alt icon"></i> Transferts
      </a>
      <a href="{{ route('admin.card-requests.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.card-requests*') ? 'active':'' }}">
        <i class="fas fa-credit-card icon"></i> Demandes de carte
        @php $cardReqPending = \App\Models\CardRequest::where('status','pending')->count(); @endphp
        @if($cardReqPending)<span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $cardReqPending }}</span>@endif
      </a>
      <a href="{{ route('admin.invoices.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.invoices*') ? 'active':'' }}">
        <i class="fas fa-file-invoice icon"></i> Factures
      </a>
      @endhasanyrole
      @can('manage-site-contacts')
      <a href="{{ route('admin.site-contacts.edit') }}"
         class="sidebar-link {{ request()->routeIs('admin.site-contacts*') ? 'active':'' }}">
        <i class="fas fa-map-marker-alt icon"></i> Coordonnées
      </a>
      @endcan
      @can('manage-social-links')
      <a href="{{ route('admin.social-links.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.social-links*') ? 'active':'' }}">
        <i class="fas fa-share-alt icon"></i> Réseaux sociaux
      </a>
      @endcan
      @can('manage-languages')
      <a href="{{ route('admin.languages.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.languages*') ? 'active':'' }}">
        <i class="fas fa-language icon"></i> Langues
      </a>
      @endcan
      @can('manage-currencies')
      <a href="{{ route('admin.currencies.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.currencies*') ? 'active':'' }}">
        <i class="fas fa-money-bill-wave icon"></i> Devises
      </a>
      @endcan
      @hasanyrole(['admin', 'super-admin'])
      @php $admSupUnread = \App\Models\SupportMessage::where('sender_type','client')->whereNull('read_at')->count(); @endphp
      <a href="{{ route('admin.support.index') }}"
         class="sidebar-link {{ request()->routeIs('admin.support*') ? 'active':'' }}">
        <i class="fas fa-comments icon"></i> Support
        @if($admSupUnread > 0)
        <span style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.62rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">{{ $admSupUnread }}</span>
        @endif
      </a>
      @endhasanyrole

      <span class="sidebar-label">Compte</span>
      @role('admin')
      <a href="{{ route('admin.profile') }}"
         class="sidebar-link {{ request()->routeIs('admin.profile*') ? 'active':'' }}">
        <i class="fas fa-user-circle icon"></i> Mon profil
      </a>
      @endrole
      <a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="sidebar-link">
        <i class="fas fa-globe icon"></i> Retour au site
      </a>
    @endrole

  @endauth
  </nav>

  <div class="sidebar-footer">
    @if(Auth::check() && Auth::user()->type === 'staff')
    <form action="{{ route('staff.logout') }}" method="POST">
    @else
    <form action="{{ route('logout') }}" method="POST">
    @endif
      @csrf
      <button type="submit" class="sidebar-logout">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
      </button>
    </form>
  </div>
</aside>

<!-- ═══════════════════ MAIN ═══════════════════ -->
<div class="main-wrap">

  <header class="topbar">
    <div class="topbar-left">
      <button class="topbar-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
      <span class="topbar-title">@yield('page_title','Dashboard')</span>
    </div>
    <div class="topbar-right">
      @auth
      @if(Auth::user()->hasAnyRole(['admin','super-admin']))
      {{-- Bouton d'installation PWA (visible uniquement si installable) --}}
      <button id="pwa-install-btn" onclick="doInstallPwa()"
        title="Installer l'application"
        style="display:none;align-items:center;gap:.4rem;
          background:var(--c-accent);color:var(--c-on-accent);
          border:none;border-radius:var(--radius-sm);
          padding:.4rem .875rem;font-size:.78rem;font-weight:700;
          cursor:pointer;font-family:inherit;transition:background .2s;flex-shrink:0">
        <i class="fas fa-download"></i>
        <span class="d-none d-sm-inline">Installer l'app</span>
      </button>
      @endif
      @if(Auth::user()->hasAnyRole(['admin','super-admin']))
      @php $adminUnread = \App\Models\AdminNotification::where('admin_id', Auth::id())->whereNull('read_at')->count(); @endphp
      <div style="position:relative" id="notifWrap">
        <button class="topbar-badge" id="notifBell" onclick="toggleNotifPanel()"
                style="border:none;cursor:pointer;background:var(--c-surface)" title="Notifications">
          <i class="fas fa-bell"></i>
          <span id="adminNotifBadge" style="position:absolute;top:-4px;right:-4px;min-width:16px;height:16px;padding:0 4px;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);font-size:.58rem;font-weight:800;display:{{ $adminUnread > 0 ? 'flex' : 'none' }};align-items:center;justify-content:center;border:2px solid var(--c-surface)">{{ $adminUnread > 9 ? '9+' : $adminUnread }}</span>
        </button>
        {{-- Dropdown panel --}}
        <div id="notifPanel" style="display:none;position:absolute;top:calc(100% + 10px);right:0;
          width:340px;background:var(--c-surface);border:1px solid var(--c-border);
          border-radius:var(--radius);box-shadow:0 8px 30px rgba(0,0,0,.12);z-index:500;overflow:hidden">
          <div style="display:flex;align-items:center;justify-content:space-between;
            padding:.8125rem 1.125rem;border-bottom:1px solid var(--c-border)">
            <span style="font-size:.875rem;font-weight:700;color:var(--c-navy)">Notifications</span>
            <button onclick="markAllReadPanel()" style="background:none;border:none;color:var(--c-accent);
              font-size:.72rem;font-weight:700;cursor:pointer;padding:.2rem .4rem;border-radius:4px;transition:.15s"
              onmouseover="this.style.background='var(--c-amber-l)'" onmouseout="this.style.background='none'">
              Tout lire
            </button>
          </div>
          <div id="notifList" style="max-height:360px;overflow-y:auto"></div>
          <div style="padding:.625rem 1.125rem;border-top:1px solid var(--c-border);text-align:center">
            <a href="{{ Auth::user()->hasRole('super-admin') ? route('super-admin.profile') : route('admin.profile') }}"
               style="font-size:.75rem;color:var(--c-muted);display:inline-flex;align-items:center;gap:.35rem">
              <i class="fas fa-user-circle"></i> Mon profil
            </a>
          </div>
        </div>
      </div>
      @endif
      @endauth
      <div class="topbar-avatar" title="{{ Auth::user()->name ?? '' }}">
        {{ strtoupper(substr(Auth::user()->name??'U',0,1)) }}
      </div>
    </div>
  </header>

  <main class="content-area">

    @if(session('success'))
    <div class="flash flash-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash flash-err"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif
    {{-- .flash-warn etait style mais jamais rendu : les avertissements (email parti
         sans une piece jointe, par exemple) n arrivaient jamais jusqu a l admin. --}}
    @if(session('warning'))
    <div class="flash flash-warn"><i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}</div>
    @endif

    @yield('content')
  </main>
</div>

<script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function openSidebar()  { document.getElementById('sidebar').classList.add('open'); document.getElementById('sidebarOverlay').classList.add('show'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show'); }
</script>
@auth
@if(Auth::user()->hasAnyRole(['admin','super-admin']))
<script>
/* ── Notification panel ── */
let _notifOpen = false;

function toggleNotifPanel() {
  _notifOpen = !_notifOpen;
  const panel = document.getElementById('notifPanel');
  panel.style.display = _notifOpen ? 'block' : 'none';
  if (_notifOpen) _fetchNotifs();
}

document.addEventListener('click', function(e) {
  const wrap = document.getElementById('notifWrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('notifPanel').style.display = 'none';
    _notifOpen = false;
  }
});

async function _fetchNotifs() {
  try {
    const r = await fetch('{{ route("admin.notifications.list") }}', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const d = await r.json();
    _renderNotifs(d.notifications || []);
  } catch(e) {}
}

function _renderNotifs(list) {
  const el = document.getElementById('notifList');
  if (!list.length) {
    el.innerHTML = '<div style="padding:2.25rem 1rem;text-align:center;color:var(--c-muted);font-size:.8rem"><i class="fas fa-bell-slash" style="font-size:1.75rem;display:block;margin-bottom:.625rem;opacity:.35"></i>Aucune notification</div>';
    return;
  }
  const colorMap = { support:'var(--c-violet)', transfer:'var(--c-blue)', system:'var(--c-amber)' };
  const bgMap    = { support:'rgba(124,58,237,.1)', transfer:'rgba(37,99,235,.1)', system:'rgba(217,119,6,.1)' };
  el.innerHTML = list.map(n => {
    const col = colorMap[n.type] || 'var(--c-accent)';
    const bg  = bgMap[n.type]   || 'rgba(0,132,87,.1)';
    const unreadDot = n.read ? '' : `<div style="width:6px;height:6px;border-radius:50%;background:var(--c-accent);flex-shrink:0;margin-top:.4rem"></div>`;
    return `<div onclick="${n.url ? `window.location='${n.url}'` : ''}"
      style="display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 1.125rem;
        border-bottom:1px solid var(--c-border);cursor:${n.url ? 'pointer' : 'default'};
        background:${n.read ? 'transparent' : 'rgba(0,132,87,.04)'};transition:.15s"
      onmouseover="this.style.background='var(--c-bg)'"
      onmouseout="this.style.background='${n.read ? 'transparent' : 'rgba(0,132,87,.04)'}'">
      <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;display:flex;
        align-items:center;justify-content:center;font-size:.8rem;background:${bg};color:${col}">
        <i class="fas fa-${n.icon}"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:.8rem;font-weight:${n.read ? '500' : '700'};color:var(--c-navy);
          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${n.title}</div>
        <div style="font-size:.73rem;color:var(--c-muted);margin-top:.1rem;
          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${n.body}</div>
        <div style="font-size:.65rem;color:var(--c-muted);margin-top:.2rem">${n.time}</div>
      </div>
      ${unreadDot}
    </div>`;
  }).join('');
}

async function markAllReadPanel() {
  try {
    await fetch('{{ route("admin.notifications.read-all") }}', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      }
    });
    const badge = document.getElementById('adminNotifBadge');
    badge.style.display = 'none';
    _fetchNotifs();
  } catch(e) {}
}

/* ── Badge polling (15s) ── */
(function() {
  const badge = document.getElementById('adminNotifBadge');
  if (!badge) return;
  setInterval(async () => {
    try {
      const r = await fetch('{{ route("admin.notifications.count") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const d = await r.json();
      const n = d.count || 0;
      badge.textContent = n > 9 ? '9+' : n;
      badge.style.display = n > 0 ? 'flex' : 'none';
    } catch(e) {}
  }, 15000);
})();
</script>
@endif
@endauth
@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}
</script>
@auth
@if(Auth::user()->hasAnyRole(['admin','super-admin']))
<script>
/* ── PWA Install — portail admin ── */
let _pwaPrompt = null;

window.addEventListener('beforeinstallprompt', function(e) {
  e.preventDefault();
  _pwaPrompt = e;
  const btn = document.getElementById('pwa-install-btn');
  if (btn && !localStorage.getItem('cxa_admin_pwa_installed')) {
    btn.style.display = 'flex';
  }
});

window.addEventListener('appinstalled', function() {
  localStorage.setItem('cxa_admin_pwa_installed', '1');
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'none';
  _pwaPrompt = null;
});

function doInstallPwa() {
  if (!_pwaPrompt) return;
  _pwaPrompt.prompt();
  _pwaPrompt.userChoice.then(function(r) {
    if (r.outcome === 'accepted') {
      localStorage.setItem('cxa_admin_pwa_installed', '1');
      const btn = document.getElementById('pwa-install-btn');
      if (btn) btn.style.display = 'none';
    }
    _pwaPrompt = null;
  });
}

/* iOS Safari : afficher les instructions si non installé */
(function() {
  const isIos    = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
  const standalone = window.navigator.standalone === true;
  if (isIos && isSafari && !standalone && !localStorage.getItem('cxa_admin_pwa_installed')) {
    const banner = document.getElementById('pwa-ios-banner');
    if (banner) banner.style.display = 'flex';
  }
})();
</script>

{{-- Bannière iOS (Safari) --}}
<div id="pwa-ios-banner"
  style="display:none;position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);
    width:calc(100% - 2rem);max-width:380px;
    background:var(--c-navy);color:#fff;
    border-radius:var(--radius);padding:1rem 1.125rem;
    box-shadow:0 8px 32px rgba(0,0,0,.25);z-index:9999;
    flex-direction:column;gap:.625rem;
    border:1px solid rgba(0,132,87,.3)">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:.625rem">
      <img src="/site-icon-192.png" style="width:36px;height:36px;border-radius:8px" alt="">
      <div>
        <div style="font-size:.8rem;font-weight:700;color:#fff">' . site_name() . ' Admin</div>
        <div style="font-size:.68rem;color:rgba(255,255,255,.5)">Installer comme application</div>
      </div>
    </div>
    <button onclick="document.getElementById('pwa-ios-banner').style.display='none';localStorage.setItem('cxa_admin_pwa_installed','1')"
      style="background:rgba(255,255,255,.1);border:none;color:#fff;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center">
      &times;
    </button>
  </div>
  <div style="font-size:.73rem;color:rgba(255,255,255,.65);line-height:1.6">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem">
      <span style="background:rgba(0,132,87,.15);border-radius:4px;padding:.1rem .4rem;font-size:.7rem;color:var(--c-accent);font-weight:700">1</span>
      Appuyez sur <strong style="color:#fff">Partager</strong> <i class="fas fa-share-square" style="color:var(--c-accent)"></i>
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem">
      <span style="background:rgba(0,132,87,.15);border-radius:4px;padding:.1rem .4rem;font-size:.7rem;color:var(--c-accent);font-weight:700">2</span>
      Puis <strong style="color:#fff">Sur l'écran d'accueil</strong> <i class="fas fa-plus-square" style="color:var(--c-accent)"></i>
    </div>
    <div style="display:flex;align-items:center;gap:.5rem">
      <span style="background:rgba(0,132,87,.15);border-radius:4px;padding:.1rem .4rem;font-size:.7rem;color:var(--c-accent);font-weight:700">3</span>
      Appuyez sur <strong style="color:#fff">Ajouter</strong>
    </div>
  </div>
</div>
@endif
@endauth

{{-- ──────────────────────────────────────────────────────────────────────────
     Modal de confirmation reutilisable.

     Remplace les confirm() natifs du navigateur, qui n etaient ni traduisibles,
     ni coherents avec la charte, et que certains navigateurs bloquent.

     Usage : poser data-confirm="Question ?" sur un <form> ou un <button>.
     Options : data-confirm-title, data-confirm-ok, data-confirm-danger="1".

     UNIQUE boite de confirmation du layout. Il en existait une seconde
     (#cfModalOverlay) qui s auto-cablait elle aussi sur form[data-confirm] :
     les deux s ouvraient l une sur l autre et fermer celle du dessus laissait
     l autre a l ecran. Ne pas en reintroduire une : passer par ce bloc, ou par
     window.confirmModal(message, { title, confirmLabel }) -> Promise<boolean>.
     ────────────────────────────────────────────────────────────────────────── --}}
<div id="cfx" class="cfx" role="dialog" aria-modal="true" aria-labelledby="cfx-title" hidden>
  <div class="cfx__backdrop" data-cfx-cancel></div>
  <div class="cfx__box" role="document">
    <div class="cfx__icon" id="cfx-icon"><i class="fas fa-triangle-exclamation"></i></div>
    <h3 class="cfx__title" id="cfx-title">Confirmer l’action</h3>
    <p class="cfx__msg" id="cfx-msg"></p>
    <div class="cfx__actions">
      <button type="button" class="btn-ghost btn-sm-pro" data-cfx-cancel>Annuler</button>
      <button type="button" class="btn-navy btn-sm-pro" id="cfx-ok">Confirmer</button>
    </div>
  </div>
</div>

<style>
.cfx { position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center; padding:1rem; }
.cfx[hidden] { display:none; }
.cfx__backdrop { position:absolute; inset:0; background:rgba(15,13,54,.55); backdrop-filter:blur(2px); }
.cfx__box {
  position:relative; background:var(--c-surface); border-radius:var(--radius);
  padding:1.75rem 1.5rem 1.25rem; width:100%; max-width:420px; text-align:center;
  box-shadow:0 18px 60px rgba(15,13,54,.35); border:1px solid var(--c-border);
}
.cfx__icon {
  width:52px; height:52px; border-radius:50%; margin:0 auto .875rem;
  display:flex; align-items:center; justify-content:center; font-size:1.25rem;
  background:#DBEAFE; color:var(--c-accent);
}
.cfx__icon.is-danger { background:#FEE2E2; color:var(--c-red); }
.cfx__title { font-size:1.0625rem; font-weight:800; color:var(--c-navy); margin:0 0 .375rem; }
.cfx__msg { font-size:.8375rem; color:var(--c-muted); margin:0 0 1.25rem; line-height:1.55; }
.cfx__actions { display:flex; gap:.5rem; justify-content:center; }
.cfx__actions .btn-navy.is-danger { background:var(--c-red); }
</style>

<script>
(function () {
  var boxes = document.querySelectorAll('#cfx, .cfx');
  // Si un doublon de balisage existe (layout inclus deux fois, injection tierce),
  // on ne garde que le premier et on retire les autres du DOM.
  for (var i = 1; i < boxes.length; i++) { boxes[i].remove(); }

  var box = boxes[0];
  if (!box) return;
  var msgEl = document.getElementById('cfx-msg'),
      titleEl = document.getElementById('cfx-title'),
      okEl  = document.getElementById('cfx-ok'),
      iconEl = document.getElementById('cfx-icon'),
      pending = null,
      onCancel = null;

  function close() {
    box.hidden = true;
    var abandon = onCancel;
    pending = null; onCancel = null;
    if (abandon) abandon();
  }

  function open(opts, onConfirm, onAbandon) {
    // Un modal est deja ouvert : on ignore, sinon deux boites se superposeraient
    // et la seconde ecraserait le callback de la premiere.
    if (!box.hidden) { if (onAbandon) onAbandon(); return; }

    msgEl.textContent   = opts.message || '';
    titleEl.textContent = opts.title || 'Confirmer l’action';
    okEl.textContent    = opts.ok || 'Confirmer';
    iconEl.classList.toggle('is-danger', !!opts.danger);
    okEl.classList.toggle('is-danger', !!opts.danger);
    pending  = onConfirm;
    onCancel = onAbandon || null;
    box.hidden = false;
    okEl.focus();
  }

  box.querySelectorAll('[data-cfx-cancel]').forEach(function (el) {
    el.addEventListener('click', close);
  });
  okEl.addEventListener('click', function () {
    var run = pending;
    onCancel = null;          // confirmation : ce n est pas un abandon
    close();
    if (run) run();
  });

  // API a promesse, pour le code qui declenche une confirmation lui-meme.
  window.confirmModal = function (message, opts) {
    opts = opts || {};
    return new Promise(function (resolve) {
      open({ message: message, title: opts.title, ok: opts.confirmLabel, danger: opts.danger },
           function () { resolve(true); },
           function () { resolve(false); });
    });
  };
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !box.hidden) close();
  });

  function optsFrom(el) {
    return {
      message: el.getAttribute('data-confirm'),
      title:   el.getAttribute('data-confirm-title'),
      ok:      el.getAttribute('data-confirm-ok'),
      danger:  el.getAttribute('data-confirm-danger') === '1',
    };
  }

  // Formulaires : on intercepte la soumission.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm]');
    if (!form || form.dataset.cfxOk === '1') return;
    e.preventDefault();
    open(optsFrom(form), function () {
      form.dataset.cfxOk = '1';
      if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
    });
  }, true);

  // Boutons et liens hors formulaire.
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el || el.tagName === 'FORM' || el.closest('form[data-confirm]')) return;
    if (el.dataset.cfxOk === '1') return;
    e.preventDefault();
    open(optsFrom(el), function () {
      el.dataset.cfxOk = '1';
      el.click();
    });
  }, true);
})();
</script>

</body>
</html>
