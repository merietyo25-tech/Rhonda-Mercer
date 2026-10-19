<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\AccountMovement;
use App\Models\Card;
use App\Models\ClientNotification;
use App\Models\Invoice;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $unreadCount = ClientNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // 5 dernières transactions (mouvements admin + virements fusionnés)
        $adminMvts = AccountMovement::where('user_id', $user->id)
            ->latest()->limit(10)->get()
            ->map(fn ($m) => (object) [
                'source'    => 'account',
                'type'      => $m->type,
                'amount'    => (float) $m->amount,
                'currency'  => $m->currency,
                'label'     => $m->displayLabel(),
                'sub'       => $m->displaySub(),
                'status'    => 'completed',
                'created_at'=> $m->created_at,
            ]);

        $recentTransfers = Transfer::where('user_id', $user->id)
            ->whereIn('status', [
                Transfer::STATUS_PENDING,
                Transfer::STATUS_COMPLETED,
                Transfer::STATUS_FEE_REQUIRED,
                Transfer::STATUS_REJECTED,
            ])
            ->latest()->limit(10)->get()
            ->map(fn ($t) => (object) [
                'source'    => 'transfer',
                'type'      => $t->type === 'send' ? 'debit' : 'credit',
                'amount'    => (float) $t->amount,
                'currency'  => $t->currency,
                'label'     => $t->type === 'send'
                    ? __('app.mv_transfer_sent') . ' ' . $t->beneficiary_name
                    : __('app.mv_transfer_received'),
                'sub'       => $t->typeLabel() . ' · ' . $t->reference,
                'transfer_ref' => $t->reference,
                'status'    => $t->status,
                'created_at'=> $t->created_at,
            ]);

        $recentActivity = $adminMvts->toBase()->merge($recentTransfers->toBase())
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        $pendingBalance = $user->pendingBalance();

        return view('client.app.home', compact(
            'user', 'recentActivity', 'unreadCount', 'pendingBalance'
        ));
    }

    /** Cartes bancaires associées au compte du client. */
    public function cards()
    {
        $user  = Auth::user();
        $cards = Card::where('user_id', $user->id)->latest()->get();

        $pendingRequest = \App\Models\CardRequest::with('invoice')->where('user_id', $user->id)
            ->whereIn('status', [\App\Models\CardRequest::STATUS_PENDING, \App\Models\CardRequest::STATUS_AWAITING_PAYMENT])
            ->latest()->first();

        return view('client.app.cards', compact('user', 'cards', 'pendingRequest'));
    }

    public function invoices()
    {
        $user     = Auth::user();
        $invoices = Invoice::where('client_id', $user->id)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
            ->latest()
            ->get();

        return view('client.app.invoices.index', compact('user', 'invoices'));
    }

    public function invoicePdf(Invoice $invoice)
    {
        abort_if($invoice->client_id !== Auth::id(), 403);
        abort_unless(in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED], true), 404);

        return response(\App\Services\InvoicePdf::render($invoice), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Services\InvoicePdf::filename($invoice) . '"',
        ]);
    }

    public function invoiceShow(Invoice $invoice)
    {
        $user = Auth::user();
        abort_if($invoice->client_id !== $user->id, 403);

        return view('client.app.invoices.show', compact('user', 'invoice'));
    }

    public function profile()
    {
        $user      = Auth::user();
        $transfers = Transfer::where('user_id', $user->id)->latest()->limit(3)->get();

        return view('client.app.profile', compact('user', 'transfers'));
    }

    public function updateProfile(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'locale' => 'nullable|in:fr,en,pl,es,bg,hu,it,de,lt,ro,lv,nl,pt,hr,sk,sl,mt',
            'phone'  => 'nullable|string|max:30',
        ]);

        if (!empty($validated['locale'])) {
            $user->update(['locale' => $validated['locale']]);
        }

        return back()->with('success', __('app.profile_saved'));
    }

    // ── Notifications ────────────────────────────────────────────────────────

    public function notifications()
    {
        $user          = Auth::user();
        $notifications = ClientNotification::where('user_id', $user->id)
            ->latest()
            ->get();

        ClientNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('client.app.notifications', compact('user', 'notifications'));
    }

    public function notificationRead(int $id)
    {
        $user = Auth::user();
        ClientNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function notificationReadAll()
    {
        $user = Auth::user();
        ClientNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }

    public function notificationCount()
    {
        $user    = Auth::user();
        $count   = ClientNotification::where('user_id', $user->id)->whereNull('read_at')->count();
        $newest  = ClientNotification::where('user_id', $user->id)->whereNull('read_at')->latest()->first();

        return response()->json([
            'count' => $count,
            'type'  => $newest?->type,
        ]);
    }

    // ── Edit profile ─────────────────────────────────────────────────────────

    public function paymentMethods()
    {
        $user = Auth::user()->load(['bankAccount', 'card', 'kycVerification']);
        return view('client.app.payment-methods', compact('user'));
    }

    public function editProfile()
    {
        $user       = Auth::user();
        $pendingOtp = session()->has('profile_pending');

        return view('client.app.edit-profile', compact('user', 'pendingOtp'));
    }

    public function saveProfile(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'email'   => 'required|email|max:191',
        ]);

        // Toujours sauvegarder nom, tel, adresse immédiatement
        $user->update([
            'name'    => $validated['name'],
            'phone'   => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
        ]);

        // OTP uniquement si l'email est vraiment différent
        $newEmail = strtolower(trim($validated['email']));
        if ($newEmail !== strtolower(trim($user->email))) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('profile_otp_' . $user->id, Hash::make($otp), 600);
            session(['profile_pending' => ['email' => $newEmail]]);
            try {
                Mail::to($user->email)->locale($user->locale ?: 'fr')->send(new OtpMail($otp, $user));
            } catch (\Throwable) {
                return redirect()->route('client.app.profile')->with('success', __('app.profile_saved'));
            }
            return redirect()->route('client.app.profile.edit')->with('otp_sent', true);
        }

        return redirect()->route('client.app.profile')->with('success', __('app.profile_saved'));
    }

    public function confirmProfileOtp(Request $request)
    {
        $user    = Auth::user();
        $otp     = $request->input('otp');
        $pending = session('profile_pending');
        $cached  = Cache::get('profile_otp_' . $user->id);

        if (!$pending || !$cached || !Hash::check($otp, $cached)) {
            return back()->withErrors(['otp' => __('auth.otp_invalid')]);
        }

        $user->update(['email' => $pending['email']]);

        Cache::forget('profile_otp_' . $user->id);
        session()->forget('profile_pending');

        return redirect()->route('client.app.profile')->with('success', __('app.profile_saved'));
    }

    // ── Change password (logged in) ──────────────────────────────────────────

    public function changePassword()
    {
        $user = Auth::user();
        return view('client.app.change-password', compact('user'));
    }

    public function savePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => __('app.wrong_current_password')]);
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        return redirect()->route('client.app.profile')->with('success', __('app.password_changed'));
    }

    // ── Account movements ────────────────────────────────────────────────────

    public function movements()
    {
        $user = Auth::user();

        // Admin credit/debit operations
        $adminMvts = AccountMovement::where('user_id', $user->id)
            ->with('admin:id,name')
            ->get()
            ->map(fn ($m) => (object) [
                'source'       => 'account',
                'type'         => $m->type,
                'amount'       => (float) $m->amount,
                'currency'     => $m->currency,
                'label'        => $m->displayLabel(),
                'sub'          => $m->displaySub() ?: 'Système',
                'balance_after' => (float) $m->balance_after,
                'has_balance'  => true,
                'status'       => 'completed',
                'created_at'   => $m->created_at,
            ]);

        // Client transfers (send = debit, receive = credit)
        $transfers = Transfer::where('user_id', $user->id)
            ->whereIn('status', [
                Transfer::STATUS_PENDING,
                Transfer::STATUS_COMPLETED,
                Transfer::STATUS_FEE_REQUIRED,
                Transfer::STATUS_REJECTED,
            ])
            ->get()
            ->map(fn ($t) => (object) [
                'source'       => 'transfer',
                'type'         => $t->type === 'send' ? 'debit' : 'credit',
                'amount'       => (float) $t->amount,
                'currency'     => $t->currency,
                'label'        => $t->type === 'send'
                    ? __('app.mv_transfer_sent') . ' ' . $t->beneficiary_name
                    : __('app.mv_transfer_received'),
                'sub'          => $t->typeLabel() . ' · ' . $t->reference . ($t->note ? ' — ' . $t->note : ''),
                'transfer_ref' => $t->reference,
                'balance_after' => null,
                'has_balance'  => false,
                'status'       => $t->status,
                'created_at'   => $t->created_at,
            ]);

        $merged = $adminMvts->toBase()->merge($transfers->toBase())
            ->sortByDesc('created_at')
            ->values();

        return view('client.app.movements', compact('user', 'merged'));
    }

    // ── PWA ─────────────────────────────────────────────────────────────────

    /**
     * Icone PWA carree derivee du logo televerse par l administrateur.
     *
     * Les manifests pointaient vers /images/icon-*.png, des fichiers statiques : le logo
     * configure en admin n apparaissait donc jamais sur l ecran d accueil. On genere ici
     * l icone a la volee, sur un aplat bleu de marque, avec une marge de securite pour
     * l affichage "maskable" (Android rogne les bords en cercle).
     *
     * Le rendu est mis en cache sur disque ; la cle integre la date du fichier source,
     * donc un nouveau televersement invalide automatiquement l ancienne icone.
     */
    public function siteIcon(int $size)
    {
        // 16 et 32 sont les tailles de favicon usuelles : on descend jusque-la,
        // sinon un favicon 16px serait genere en 48 puis reduit par le navigateur.
        $size    = max(16, min(1024, $size));
        $contact = site_identity();
        $disk    = Storage::disk('public');

        // On retient la premiere variante dont le FICHIER existe reellement : un chemin
        // peut rester en base alors que l image a disparu du disque.
        $rel = null;
        foreach (array_filter([$contact?->logo_light_path, $contact?->logo_dark_path]) as $candidate) {
            if ($disk->exists($candidate)) {
                $rel = $candidate;
                break;
            }
        }

        if (! $rel) {
            // Aucun logo configure : monogramme de la marque (public/images/icon-512.png), mis a l echelle demandee.
            $base = public_path('images/icon-512.png');
            abort_unless(is_file($base), 404);

            if ($size === 512) {
                return response()->file($base, ['Cache-Control' => 'public, max-age=3600']);
            }

            $scaled = storage_path('app/site-icons/default-' . md5((string) filemtime($base)) . "-{$size}.png");
            if (! is_file($scaled)) {
                @mkdir(dirname($scaled), 0775, true);
                $src = imagecreatefrompng($base);
                $dst = imagecreatetruecolor($size, $size);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));
                imagepng($dst, $scaled, 9);
                imagedestroy($src);
                imagedestroy($dst);
            }

            return response()->file($scaled, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=3600']);
        }

        $cacheFile = storage_path(
            'app/site-icons/' . md5($rel . '|' . $disk->lastModified($rel)) . "-{$size}.png"
        );

        if (! is_file($cacheFile)) {
            @mkdir(dirname($cacheFile), 0775, true);

            $src = @imagecreatefromstring($disk->get($rel));
            abort_unless($src !== false, 404);

            // Beaucoup de logos sont livres avec une large marge vide. Sans la retirer,
            // le contenu utile n occupe qu une fraction du cadre et devient illisible en
            // 16 ou 32 px. On detecte la couleur de fond aux quatre coins et on rogne les
            // bords uniformes avant de redimensionner.
            [$sx, $sy, $sw, $sh] = $this->trimUniformBorder($src);

            // En favicon (16-48 px), un logotype large est illisible : le texte se
            // reduit a une bouillie de pixels. On garde alors le seul symbole, place
            // par convention a gauche du texte, via un recadrage carre sur ce bord.
            if ($size <= 48 && $sh > 0 && ($sw / $sh) > 1.5) {
                $sw = $sh;
            }

            $canvas = imagecreatetruecolor($size, $size);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 0x06, 0x57, 0xA4));

            // Un logo deja carre est presque toujours une icone concue comme telle :
            // on remplit le cadre. Un logotype large est encastre a 80% pour survivre
            // au rognage circulaire d Android (maskable).
            $isSquare = $sh > 0 && abs(($sw / $sh) - 1) < 0.1;
            $box      = (int) round($size * ($isSquare ? 1.0 : 0.80));
            $ratio = min($box / $sw, $box / $sh);
            $dw    = (int) round($sw * $ratio);
            $dh    = (int) round($sh * $ratio);

            imagecopyresampled(
                $canvas, $src,
                (int) (($size - $dw) / 2), (int) (($size - $dh) / 2),
                $sx, $sy, $dw, $dh, $sw, $sh
            );

            imagepng($canvas, $cacheFile, 9);
            imagedestroy($canvas);
            imagedestroy($src);
        }

        return response()->file($cacheFile, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Detecte et retire la marge uniforme autour d un logo.
     *
     * Retourne [x, y, largeur, hauteur] de la zone utile. Si les quatre coins ne
     * s accordent pas sur une couleur de fond, ou si le rognage donnerait une zone
     * degeneree, l image entiere est renvoyee telle quelle.
     */
    private function trimUniformBorder($img): array
    {
        $w = imagesx($img);
        $h = imagesy($img);

        $corners = [
            imagecolorat($img, 0, 0),
            imagecolorat($img, $w - 1, 0),
            imagecolorat($img, 0, $h - 1),
            imagecolorat($img, $w - 1, $h - 1),
        ];

        $rgb = static fn (int $c): array => [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
        $diff = static function (array $a, array $b): int {
            return abs($a[0] - $b[0]) + abs($a[1] - $b[1]) + abs($a[2] - $b[2]);
        };

        $bg = $rgb($corners[0]);
        foreach ($corners as $c) {
            // Coins discordants : pas de marge uniforme identifiable.
            if ($diff($rgb($c), $bg) > 24) {
                return [0, 0, $w, $h];
            }
        }

        $tol  = 40;   // tolerance au bruit de compression
        $step = max(1, (int) floor(min($w, $h) / 400)); // echantillonnage sur grandes images

        $rowIsBg = function (int $y) use ($img, $w, $bg, $rgb, $diff, $tol, $step): bool {
            for ($x = 0; $x < $w; $x += $step) {
                if ($diff($rgb(imagecolorat($img, $x, $y)), $bg) > $tol) {
                    return false;
                }
            }
            return true;
        };
        $colIsBg = function (int $x) use ($img, $h, $bg, $rgb, $diff, $tol, $step): bool {
            for ($y = 0; $y < $h; $y += $step) {
                if ($diff($rgb(imagecolorat($img, $x, $y)), $bg) > $tol) {
                    return false;
                }
            }
            return true;
        };

        $top = 0;        while ($top < $h - 1 && $rowIsBg($top))        { $top++; }
        $bottom = $h - 1; while ($bottom > $top && $rowIsBg($bottom))   { $bottom--; }
        $left = 0;       while ($left < $w - 1 && $colIsBg($left))      { $left++; }
        $right = $w - 1; while ($right > $left && $colIsBg($right))     { $right--; }

        $nw = $right - $left + 1;
        $nh = $bottom - $top + 1;

        // Rognage aberrant (image unie, ou contenu minuscule) : on garde l original.
        if ($nw < $w * 0.05 || $nh < $h * 0.05) {
            return [0, 0, $w, $h];
        }

        // Petite respiration autour du contenu.
        $pad   = (int) round(min($nw, $nh) * 0.06);
        $left  = max(0, $left - $pad);
        $top   = max(0, $top - $pad);
        $nw    = min($w - $left, $nw + 2 * $pad);
        $nh    = min($h - $top,  $nh + 2 * $pad);

        return [$left, $top, $nw, $nh];
    }

    public function manifest()
    {
        $data = [
            'id'               => '/app',
            'name'             => site_name(),
            'short_name'       => site_name(),
            'description'      => 'Gérez votre compte, vos cartes et vos virements en toute sécurité.',
            'start_url'        => '/app',
            'scope'            => '/app',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#312B81',
            'theme_color'      => '#312B81',
            'prefer_related_applications' => false,
            'lang'             => app()->getLocale(),
            'categories'       => ['finance', 'business'],
            'icons'            => [
                ['src' => '/site-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/site-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/site-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/site-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name'       => 'Mes cartes',
                    'short_name' => 'Cartes',
                    'url'        => '/app/cards',
                    'description'=> 'Consulter mes cartes',
                    'icons'      => [['src' => '/site-icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name'       => 'Virements',
                    'short_name' => 'Virements',
                    'url'        => '/app/transfers',
                    'description'=> 'Effectuer ou suivre mes virements',
                    'icons'      => [['src' => '/site-icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name'       => 'Support',
                    'short_name' => 'Support',
                    'url'        => '/app/support',
                    'description'=> 'Contacter le support client',
                    'icons'      => [['src' => '/site-icon-192.png', 'sizes' => '192x192']],
                ],
            ],
        ];

        return response()->json($data, 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function adminManifest()
    {
        $data = [
            'name'             => config('app.company_name', site_name()) . ' — Administration',
            'short_name'       => site_name() . ' Admin',
            'description'      => 'Gérez les clients et les opérations ' . site_name() . '.',
            'start_url'        => '/admin',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#0F0D36',
            'theme_color'      => '#312B81',
            'lang'             => app()->getLocale(),
            'categories'       => ['finance', 'business'],
            'icons'            => [
                ['src' => '/site-icon-180.png', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/site-icon-192.png',         'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/site-icon-192.png',         'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/site-icon-512.png',         'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/site-icon-512.png',         'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name'        => 'Tableau de bord',
                    'short_name'  => 'Dashboard',
                    'url'         => '/admin',
                    'description' => 'Vue d\'ensemble admin',
                    'icons'       => [['src' => '/site-icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name'        => 'Clients',
                    'short_name'  => 'Clients',
                    'url'         => '/admin/users',
                    'description' => 'Gérer les clients',
                    'icons'       => [['src' => '/site-icon-192.png', 'sizes' => '192x192']],
                ],
            ],
        ];

        return response()->json($data, 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function serviceWorker()
    {
        $js = <<<'JS'
// Nom de cache versionne : le changer force les appareils deja installes a recharger
// les fichiers. Sans bump, ils gardent lancienne charte et lancienne icone.
const CACHE = 'mf-v9';
const ICON  = '/site-icon-192.png';
const BADGE = '/images/icon-badge.png';
const SHELL = ['/app', '/login'];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE)
            .then(c => c.addAll(SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;

    const url = new URL(e.request.url);

    /* Ignorer extensions navigateur */
    if (!url.protocol.startsWith('http')) return;

    /* NE JAMAIS cacher storage/ — fichiers dynamiques uploadés */
    if (url.pathname.startsWith('/storage/')) return;

    if (url.pathname.startsWith('/build/assets/')) {
        e.respondWith(
            caches.open(CACHE).then(c =>
                c.match(e.request).then(cached => {
                    if (cached) return cached;
                    return fetch(e.request).then(resp => {
                        if (resp.ok) {
                            const clone = resp.clone();
                            c.put(e.request, clone);
                        }
                        return resp;
                    });
                })
            )
        );
        return;
    }

    e.respondWith(
        fetch(e.request)
            .then(resp => {
                if (resp.ok) {
                    const clone = resp.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return resp;
            })
            .catch(() =>
                caches.match(e.request)
                    .then(cached => cached || caches.match('/app'))
            )
    );
});

/* ── Push notifications ── */
self.addEventListener('push', e => {
    let data = { title: '__SITE_NAME__', body: '' };
    try { data = e.data ? e.data.json() : data; } catch (_) {}

    e.waitUntil(
        self.registration.showNotification(data.title || '__SITE_NAME__', {
            body:    data.body  || '',
            icon:    ICON,
            badge:   BADGE,
            vibrate: [200, 100, 200],
            tag:     data.tag || 'mf-notif',
            renotify: true,
            data:    { url: data.url || '/app/notifications' },
        })
    );
});

self.addEventListener('notificationclick', e => {
    e.notification.close();
    const target = (e.notification.data && e.notification.data.url) || '/app/notifications';
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            for (const c of list) {
                if (c.url.includes('/app') && 'focus' in c) {
                    c.navigate(target);
                    return c.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(target);
        })
    );
});
JS;
        $js = str_replace('__SITE_NAME__', addslashes(site_name()), $js);

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }
}
