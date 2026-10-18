<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\TransferValidationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\ClientLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\StaffForgotPasswordController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Auth\StaffResetPasswordController;
use App\Http\Controllers\Client\AppController as ClientAppController;
use App\Http\Controllers\Client\SupportController as ClientSupportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\SuperAdminDashboardController;
use App\Http\Controllers\SignupController;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Langues actives : si la base est injoignable (déploiement, .env pas encore renseigné, composer install),
// on retombe sur fr/en au lieu de faire échouer toute commande artisan qui charge les routes.
try {
    $supportedLocales = Schema::hasTable('languages')
        ? Language::enabledCodes()
        : ['fr', 'en'];
} catch (\Throwable $e) {
    $supportedLocales = ['fr', 'en'];
}

Route::get('/', function (Request $request) use ($supportedLocales) {
    $locale = 'en';

    $header = $request->header('Accept-Language', '');
    if ($header) {
        $languages = [];
        foreach (explode(',', $header) as $part) {
            [$tag, $q] = array_pad(explode(';q=', trim($part)), 2, '1');
            $languages[strtolower(trim($tag))] = (float) $q;
        }
        arsort($languages);

        foreach (array_keys($languages) as $tag) {
            $short = substr($tag, 0, 2);
            if (in_array($short, $supportedLocales)) {
                $locale = $short;
                break;
            }
        }
    }

    return redirect("/{$locale}");
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => 'setLocale',
    'where' => ['locale' => implode('|', $supportedLocales)]
], function () {
    Route::get('/', function () {
        return view('landing');
    })->name('home');


    Route::get('/about', function () {
        // Équipe présentée publiquement (gérée depuis l'administration : Site → Équipe)
        $members = \App\Models\TeamMember::visibleList();

        return view('about', compact('members'));
    })->name('about');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');

    // Landing page orientée conversion (ouverture de compte)
    Route::get('/start', function () {
        return view('landing');
    })->name('landing');


    Route::get('/signup', [SignupController::class, 'create'])->name('signup');

    Route::get('/terms', function () {
        return view('terms');
    })->name('terms');

    Route::get('/privacy', function () {
        return view('privacy');
    })->name('privacy');


});
Route::post('/contact/send', [ContactController::class, 'sendMail'])->name('contact.send');
Route::post('/subscribe/send', [ContactController::class, 'subscribeMail'])->name('subscribe.send');
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');

// ── Page "prochaines étapes" (lien signé envoyé par email, sans connexion) ──

// ── Locale switcher (for auth pages without {locale} prefix) ────────────────
Route::get('/lang/{lang}', function (Request $request, $lang) {
    if (in_array($lang, Language::enabledCodes())) {
        session(['locale' => $lang]);
    }
    $back = $request->headers->get('referer', url('/'));
    return redirect($back);
})->name('lang.switch');

// ── Authentication ──────────────────────────────────────────────────────────

// Account invitation / activation (public — no auth required)
Route::get('/invitation/{token}',  [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}', [InvitationController::class, 'activate'])->name('invitation.activate');

// Client login
Route::get('/login',        [ClientLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login',       [ClientLoginController::class, 'login'])->name('login.submit')->middleware(['guest', 'throttle:5,1']);
Route::post('/logout',      [ClientLoginController::class, 'logout'])->name('logout');
Route::get('/login/forget', [ClientLoginController::class, 'forgetAccount'])->name('login.forget');

// Activation du compte par e-mail (lien signé, idempotent)
Route::get('/email/verify',                    [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])->name('verification.notice');
Route::get('/email/verify/{uuid}/{hash}',      [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware('throttle:20,1');
Route::post('/email/resend',                   [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])->name('verification.resend')->middleware('throttle:3,1');

// OTP verification
Route::get('/otp-verify',  [OtpController::class, 'show'])->name('otp.show');
Route::post('/otp-verify', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:5,1');
Route::post('/otp-resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:3,1');

// Jeton CSRF frais — appelé par le JS quand une requête échoue en 419 (session
// expirée pendant que l'onglet OTP/activation restait ouvert), pour régénérer
// un token sans recharger toute la page.
Route::get('/csrf-refresh', function (\Illuminate\Http\Request $request) {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

// Account unblock (via email link)
Route::get('/account/unblock/{token}', [OtpController::class, 'unblock'])->name('account.unblock');

// Forgot / reset password (clients)
Route::get('/forgot-password',         [ForgotPasswordController::class, 'show'])->name('password.request')->middleware('guest');
Route::post('/forgot-password',        [ForgotPasswordController::class, 'send'])->name('password.email')->middleware(['guest', 'throttle:5,1']);
Route::get('/reset-password/{token}',  [ResetPasswordController::class, 'show'])->name('password.reset')->middleware('guest');
Route::post('/reset-password',         [ResetPasswordController::class, 'reset'])->name('password.update')->middleware(['guest', 'throttle:5,1']);

// Staff login (admin / super-admin)
Route::get('/staff/login',  [StaffLoginController::class, 'showLoginForm'])->name('staff.login')->middleware('guest');
Route::post('/staff/login', [StaffLoginController::class, 'login'])->name('staff.login.submit')->middleware(['guest', 'throttle:5,1']);
Route::post('/staff/logout',[StaffLoginController::class, 'logout'])->name('staff.logout');

// Forgot / reset password (staff / admin)
Route::get('/staff/forgot-password',         [StaffForgotPasswordController::class, 'show'])->name('staff.password.request')->middleware('guest');
Route::post('/staff/forgot-password',        [StaffForgotPasswordController::class, 'send'])->name('staff.password.email')->middleware(['guest', 'throttle:5,1']);
Route::get('/staff/reset-password/{token}',  [StaffResetPasswordController::class, 'show'])->name('staff.password.reset')->middleware('guest');
Route::post('/staff/reset-password',         [StaffResetPasswordController::class, 'reset'])->name('staff.password.update')->middleware(['guest', 'throttle:5,1']);

// ── Client dashboard ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:client'])->prefix('dashboard')->name('client.')->group(function () {
    Route::get('/', fn () => redirect()->route('client.app.home'))->name('dashboard');
});

// ── Application mobile client (PWA) ─────────────────────────────────────────
Route::middleware(['auth', 'role:client', 'client.locale'])->prefix('app')->name('client.app.')->group(function () {
    Route::get('/',                    [ClientAppController::class, 'index'])->name('home');

    Route::get('/cards',               [ClientAppController::class, 'cards'])->name('cards');
    Route::post('/cards/{card}/suspend', [\App\Http\Controllers\Client\CardSettingsController::class, 'toggleSuspend'])->name('cards.suspend');
    Route::post('/cards/{card}/limit',   [\App\Http\Controllers\Client\CardSettingsController::class, 'updateLimit'])->name('cards.limit');
    Route::post('/cards/request',      [\App\Http\Controllers\Client\CardRequestController::class, 'store'])->middleware('throttle:30,1')->name('cards.request');

    Route::get('/profile',             [ClientAppController::class, 'profile'])->name('profile');
    Route::post('/profile',            [ClientAppController::class, 'updateProfile'])->name('profile.update');
    Route::get('/payment-methods',     [ClientAppController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/profile/edit',        [ClientAppController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/edit',       [ClientAppController::class, 'saveProfile'])->name('profile.save');
    Route::post('/profile/edit/otp',   [ClientAppController::class, 'confirmProfileOtp'])->name('profile.edit.otp');
    Route::get('/profile/password',    [ClientAppController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/password',   [ClientAppController::class, 'savePassword'])->name('profile.password.save');

    // Notifications (page)
    Route::get('/notifications',             [ClientAppController::class, 'notifications'])->name('notifications');

    // Mouvements de compte
    Route::get('/movements', [ClientAppController::class, 'movements'])->name('movements');

    // Factures
    Route::get('/invoices',            [ClientAppController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}',  [ClientAppController::class, 'invoiceShow'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [ClientAppController::class, 'invoicePdf'])->name('invoices.pdf');

    // Transferts : hub central (bouton FAB nav) + sous-pages — KYC approuvé requis
    Route::middleware('kyc.approved')->group(function () {
        Route::get('/transfers',           [\App\Http\Controllers\Client\TransferController::class, 'hub'])->name('transfers');
        Route::get('/transfer/send',       [\App\Http\Controllers\Client\TransferController::class, 'sendForm'])->name('transfer.send');
        Route::post('/transfer/send',      [\App\Http\Controllers\Client\TransferController::class, 'sendProcess'])->name('transfer.send.process');
        Route::get('/transfer/receive',    [\App\Http\Controllers\Client\TransferController::class, 'receive'])->name('transfer.receive');
        Route::get('/transfer/confirmation', [\App\Http\Controllers\Client\TransferController::class, 'confirmation'])->name('transfer.confirmation');

        // Prêts : simulation, demande et suivi — KYC approuvé requis
        Route::get('/loans',                       [\App\Http\Controllers\Client\LoanController::class, 'index'])->name('loans');
        Route::get('/loans/new',                   [\App\Http\Controllers\Client\LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans',                      [\App\Http\Controllers\Client\LoanController::class, 'store'])->middleware('throttle:10,1')->name('loans.store');
        Route::get('/loans/{application}',         [\App\Http\Controllers\Client\LoanController::class, 'show'])->name('loans.show');
        Route::post('/loans/{application}/cancel', [\App\Http\Controllers\Client\LoanController::class, 'cancel'])->name('loans.cancel');
    });

    // Support client (page principale)
    Route::get('/support', [ClientSupportController::class, 'index'])->name('support');

    // Détail d'un virement (depuis les mouvements, l'accueil ou la liste des virements)
    Route::get('/transfer/detail/{reference}', [\App\Http\Controllers\Client\TransferController::class, 'show'])->name('transfer.show');
    Route::get('/transfer/detail/{reference}/state', [\App\Http\Controllers\Client\TransferController::class, 'state'])->name('transfer.state');
    Route::post('/transfer/detail/{reference}/unlock', [\App\Http\Controllers\Client\TransferController::class, 'unlock'])->name('transfer.unlock')->middleware('throttle:10,1');

    // Vérification d'identité (KYC)
    Route::get('/kyc',  [\App\Http\Controllers\Client\KycController::class, 'show'])->name('kyc.show');
    Route::post('/kyc', [\App\Http\Controllers\Client\KycController::class, 'saveStep'])->name('kyc.store');
    Route::post('/kyc/info', [\App\Http\Controllers\Client\KycController::class, 'saveStep'])->name('kyc.info');

    // ── Push notifications ──────────────────────────────────────────────────
    Route::post('/push/subscribe',   [\App\Http\Controllers\Client\PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\Client\PushController::class, 'unsubscribe'])->name('push.unsubscribe');

    // ── Endpoints AJAX internes (rate-limited + sécurisés) ──────────────────
    Route::middleware(['ajax.secure', 'throttle:60,1'])->group(function () {
        Route::get('/support/poll',              [ClientSupportController::class, 'poll'])->name('support.poll');
        Route::post('/support',                  [ClientSupportController::class, 'store'])->name('support.store');
        Route::get('/notifications/unread-count',[ClientAppController::class, 'notificationCount'])->name('notifications.count');
        Route::post('/notifications/read-all',   [ClientAppController::class, 'notificationReadAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read',  [ClientAppController::class, 'notificationRead'])->name('notifications.read');
    });

    Route::post('/locale', function (\Illuminate\Http\Request $request) {
        $locale = $request->input('locale', 'en');
        if (in_array($locale, Language::enabledCodes())) {
            $request->user()->update(['locale' => $locale]);
            session(['locale' => $locale]);
        }
        return back();
    })->name('locale');
});
// /favicon.ico etait un fichier vide (0 octet) : les navigateurs qui le demandent
// directement affichaient une icone cassee. On le sert desormais depuis la meme
// source que le reste, donc toujours synchronise avec le logo configure.
Route::get('/favicon.ico', fn () => app(ClientAppController::class)->siteIcon(32))
    ->name('favicon');

// Icone PWA generee depuis le logo televerse en admin (repli sur /images/icon-*.png).
Route::get('/site-icon-{size}.png', [ClientAppController::class, 'siteIcon'])
    ->whereNumber('size')->name('site.icon');
Route::get('/manifest.json',       [ClientAppController::class, 'manifest'])->name('pwa.manifest');
Route::get('/admin-manifest.json', [ClientAppController::class, 'adminManifest'])->name('pwa.admin-manifest');
Route::get('/sw.js',               [ClientAppController::class, 'serviceWorker'])->name('pwa.sw');

Route::get('/storage/{path}', function (string $path) {
    $file = storage_path('app/public/' . $path);
    abort_unless(file_exists($file) && is_file($file), 404);
    $mime = mime_content_type($file) ?: 'application/octet-stream';
    return response()->file($file, ['Content-Type' => $mime]);
})->where('path', '.+')->name('storage.serve');


// ── Admin dashboard ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Gestion des demandes de prêt

    // Modèles de contrats

    // User management
    Route::get('/users',                        [UserManagementController::class, 'index'])->name('users');
    Route::post('/users',                       [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}',                 [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/users/{user}',                 [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}',              [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/resend-invite',  [UserManagementController::class, 'resendInvitation'])->name('users.resend-invite');
    Route::post('/users/{user}/assign-admin',   [UserManagementController::class, 'assignAdmin'])->name('users.assign-admin')->middleware('role:super-admin');

    // Account management (credit / debit)
    Route::get('/accounts',                        [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{account}',              [AccountController::class, 'show'])->name('accounts.show');
    Route::post('/accounts/{account}/credit',      [AccountController::class, 'credit'])->name('accounts.credit');
    Route::post('/accounts/{account}/debit',       [AccountController::class, 'debit'])->name('accounts.debit');
    Route::post('/accounts/{account}/pending',     [AccountController::class, 'holdPending'])->name('accounts.pending.hold');
    Route::post('/accounts/{account}/pending/{credit}/validate', [AccountController::class, 'validatePending'])->name('accounts.pending.validate');
    Route::post('/accounts/{account}/pending/{credit}/cancel',   [AccountController::class, 'cancelPending'])->name('accounts.pending.cancel');
    Route::post('/accounts/{account}/card-cap',    [AccountController::class, 'updateCardCap'])->name('accounts.card-cap');

    // Validation des transferts clients
    Route::get('/transfers',                          [TransferValidationController::class, 'index'])->name('transfers.index');
    Route::post('/transfers/{transfer}/approve',      [TransferValidationController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject',       [TransferValidationController::class, 'reject'])->name('transfers.reject');
    Route::post('/transfers/{transfer}/progress',     [TransferValidationController::class, 'progress'])->name('transfers.progress');
    Route::post('/transfers/{transfer}/invoice',      [TransferValidationController::class, 'invoice'])->name('transfers.invoice');

    // Vérification d'identité (KYC)
    // Champs du formulaire de vérification d'identité (configurables)
    Route::get('/kyc/fields',                  [\App\Http\Controllers\Admin\KycFieldController::class, 'index'])->name('kyc.fields');
    Route::post('/kyc/fields',                 [\App\Http\Controllers\Admin\KycFieldController::class, 'update'])->name('kyc.fields.update');
    Route::post('/kyc/fields/new',             [\App\Http\Controllers\Admin\KycFieldController::class, 'store'])->name('kyc.fields.store');
    Route::delete('/kyc/fields/{field}',       [\App\Http\Controllers\Admin\KycFieldController::class, 'destroy'])->name('kyc.fields.destroy');
    Route::get('/kyc',                         [\App\Http\Controllers\Admin\KycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{kyc}',                   [\App\Http\Controllers\Admin\KycController::class, 'show'])->name('kyc.show');
    Route::get('/kyc/{kyc}/file/{key}',        [\App\Http\Controllers\Admin\KycController::class, 'answerFile'])->name('kyc.file');
    Route::get('/kyc/{kyc}/document/{type}',   [\App\Http\Controllers\Admin\KycController::class, 'document'])->name('kyc.document');
    Route::post('/kyc/{kyc}/approve',          [\App\Http\Controllers\Admin\KycController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{kyc}/reject',           [\App\Http\Controllers\Admin\KycController::class, 'reject'])->name('kyc.reject');
    // Valider un client sans qu'il ait envoyé ses documents (génère aussi IBAN + carte)
    Route::post('/users/{user}/kyc/validate',  [\App\Http\Controllers\Admin\KycController::class, 'forceApprove'])->name('users.kyc.validate');

    // Attribution IBAN / carte (réservé aux clients dont le KYC est approuvé)
    Route::get('/users/{user}/banking',       [\App\Http\Controllers\Admin\BankingController::class, 'edit'])->name('users.banking.edit');
    Route::post('/users/{user}/banking',      [\App\Http\Controllers\Admin\BankingController::class, 'store'])->name('users.banking.store');
    Route::post('/users/{user}/banking/toggle-block', [\App\Http\Controllers\Admin\BankingController::class, 'toggleBlock'])->name('users.banking.toggle-block');
    Route::delete('/users/{user}/banking',    [\App\Http\Controllers\Admin\BankingController::class, 'destroy'])->name('users.banking.destroy');

    // Demandes de carte des clients
    Route::get('/card-requests',                       [\App\Http\Controllers\Admin\CardRequestController::class, 'index'])->name('card-requests.index');
    Route::post('/card-requests/{cardRequest}/invoice', [\App\Http\Controllers\Admin\CardRequestController::class, 'invoice'])->name('card-requests.invoice');
    Route::post('/card-requests/{cardRequest}/approve', [\App\Http\Controllers\Admin\CardRequestController::class, 'approve'])->name('card-requests.approve');
    Route::post('/card-requests/{cardRequest}/reject',  [\App\Http\Controllers\Admin\CardRequestController::class, 'reject'])->name('card-requests.reject');

    // Demandes de prêt des clients
    Route::get('/loans',                          [\App\Http\Controllers\Admin\LoanApplicationController::class, 'index'])->name('loans.index');
    Route::get('/loans/settings',                 [\App\Http\Controllers\Admin\LoanApplicationController::class, 'settings'])->name('loans.settings');
    Route::post('/loans/settings',                [\App\Http\Controllers\Admin\LoanApplicationController::class, 'updateSettings'])->name('loans.settings.update');
    Route::post('/loans/{application}/approve',   [\App\Http\Controllers\Admin\LoanApplicationController::class, 'approve'])->name('loans.approve');
    Route::post('/loans/{application}/reject',    [\App\Http\Controllers\Admin\LoanApplicationController::class, 'reject'])->name('loans.reject');

    // Support (pages HTML)
    Route::get('/support',          [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/{client}', [AdminSupportController::class, 'show'])->name('support.show');

    // Support AJAX (poll + envoi message)
    Route::middleware(['ajax.secure', 'throttle:60,1'])->group(function () {
        Route::post('/support/{client}',     [AdminSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{client}/poll', [AdminSupportController::class, 'poll'])->name('support.poll');
    });

    // Notifications admin — endpoints AJAX sécurisés
    Route::middleware(['ajax.secure', 'throttle:60,1'])->group(function () {
        Route::get('/notifications',                      [AdminNotificationController::class, 'list'])->name('notifications.list');
        Route::get('/notifications/count',                [AdminNotificationController::class, 'unreadCount'])->name('notifications.count');
        Route::post('/notifications/read-all',            [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
    });

    // Profil admin
    Route::get('/profile',           [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile');
    Route::post('/profile',          [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Réglages globaux de la plateforme — réservés au super-admin
    // (ou à un admin ayant reçu la permission exceptionnelle correspondante)

    // Coordonnées du site (footer)
    Route::middleware('role_or_permission:super-admin|manage-site-contacts')->group(function () {
        Route::get('/site-contacts',  [\App\Http\Controllers\Admin\SiteContactController::class, 'edit'])->name('site-contacts.edit');
        Route::post('/site-contacts', [\App\Http\Controllers\Admin\SiteContactController::class, 'update'])->name('site-contacts.update');
    });

    // Équipe présentée sur la page « À propos »
    Route::middleware('role_or_permission:super-admin|manage-team')->group(function () {
        Route::get('/team',            [\App\Http\Controllers\Admin\TeamMemberController::class, 'index'])->name('team.index');
        Route::post('/team',           [\App\Http\Controllers\Admin\TeamMemberController::class, 'store'])->name('team.store');
        Route::put('/team/{member}',   [\App\Http\Controllers\Admin\TeamMemberController::class, 'update'])->name('team.update');
        Route::delete('/team/{member}', [\App\Http\Controllers\Admin\TeamMemberController::class, 'destroy'])->name('team.destroy');
    });

    // Réseaux sociaux (footer + page contact)
    Route::middleware('role_or_permission:super-admin|manage-social-links')->group(function () {
        Route::get('/social-links',                  [\App\Http\Controllers\Admin\SocialLinkController::class, 'index'])->name('social-links.index');
        Route::get('/social-links/create',           [\App\Http\Controllers\Admin\SocialLinkController::class, 'create'])->name('social-links.create');
        Route::post('/social-links',                 [\App\Http\Controllers\Admin\SocialLinkController::class, 'store'])->name('social-links.store');
        Route::get('/social-links/{socialLink}/edit',[\App\Http\Controllers\Admin\SocialLinkController::class, 'edit'])->name('social-links.edit');
        Route::put('/social-links/{socialLink}',     [\App\Http\Controllers\Admin\SocialLinkController::class, 'update'])->name('social-links.update');
        Route::delete('/social-links/{socialLink}',  [\App\Http\Controllers\Admin\SocialLinkController::class, 'destroy'])->name('social-links.destroy');
    });

    // Langues visibles dans le sélecteur du site
    Route::middleware('role_or_permission:super-admin|manage-languages')->group(function () {
        Route::get('/languages',  [LanguageController::class, 'index'])->name('languages.index');
        Route::put('/languages',  [LanguageController::class, 'update'])->name('languages.update');
    });

    // Devises disponibles dans le projet
    Route::middleware('role_or_permission:super-admin|manage-currencies')->group(function () {
        Route::get('/currencies',               [CurrencyController::class, 'index'])->name('currencies.index');
        Route::put('/currencies',                [CurrencyController::class, 'update'])->name('currencies.update');
        Route::post('/currencies',               [CurrencyController::class, 'store'])->name('currencies.store');
        Route::delete('/currencies/{currency}',  [CurrencyController::class, 'destroy'])->name('currencies.destroy');
    });

    // Paramètres de prêt (taux d'intérêt annuel)

    // Facturation
    Route::get('/invoices',                         [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create',                  [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices',                        [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}',               [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf',           [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/payment',      [InvoiceController::class, 'updatePayment'])->name('invoices.payment');
    Route::get('/invoices/{invoice}/edit',          [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}',               [InvoiceController::class, 'update'])->name('invoices.update');
    Route::post('/invoices/{invoice}/send',         [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/mark-paid',    [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/cancel',       [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::delete('/invoices/{invoice}',            [InvoiceController::class, 'destroy'])->name('invoices.destroy');
});

// ── Super Admin dashboard ───────────────────────────────────────────────────
Route::middleware(['auth', 'role:super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/roles', [SuperAdminDashboardController::class, 'roles'])->name('roles');
    Route::post('/users/{user}/role', [SuperAdminDashboardController::class, 'assignRole'])->name('users.role');
    Route::post('/users/{user}/permissions', [SuperAdminDashboardController::class, 'updatePermissions'])->name('users.permissions');



    // Profil super-admin
    Route::get('/profile',           [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile');
    Route::post('/profile',          [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Test push notification (super-admin uniquement) ──────────────────
    Route::post('/push/test/{user}', function (\App\Models\User $user) {
        try {
            \App\Models\ClientNotification::forUser(
                $user->id,
                'system',
                'Test notification',
                'Si vous voyez ceci, les notifications push fonctionnent correctement.',
                ['url' => '/app/notifications']
            );
            return back()->with('success', 'Notification de test envoyée à ' . $user->name . '.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    })->name('push.test');
});
