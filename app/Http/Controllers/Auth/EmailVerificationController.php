<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyAccountMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /** Page « consultez votre boîte mail » affichée juste après l'inscription. */
    public function notice(Request $request)
    {
        $email = $request->session()->get('verify_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $request->session()->keep(['verify_email']);

        return view('auth.verify-notice', compact('email'));
    }

    /**
     * Lien d'activation (URL signée, valable 7 jours). Idempotent : un second clic sur un
     * compte déjà activé redirige simplement vers la connexion, sans erreur.
     */
    public function verify(Request $request, string $uuid, string $hash)
    {
        // Lien copié depuis du HTML brut (fichier log, source du mail) : « &amp; » au lieu de « & »
        // casse la signature. On corrige l'URL et on la relance.
        if (str_contains($request->getRequestUri(), '&amp;')) {
            return redirect()->to(str_replace('&amp;', '&', $request->getRequestUri()));
        }

        $user = User::where('uuid', $uuid)->first();

        if (! $user || ! hash_equals(sha1($user->email), $hash)) {
            return redirect()->route('login')->withErrors(['identifier' => __('onboarding.link_invalid')]);
        }

        // Déjà activé : pas d'erreur, même si le lien a entre-temps expiré.
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('verified_status', __('onboarding.already_verified'));
        }

        // Lien expiré ou altéré, compte pas encore activé : on propose directement le renvoi.
        if (! $request->hasValidSignature(absolute: false)) {
            return redirect()->route('verification.notice')
                ->with('verify_email', $user->email)
                ->with('error', __('onboarding.link_invalid'));
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('login')->with('verified_status', __('onboarding.verified_success'));
    }

    /** Renvoi de l'e-mail d'activation (depuis la page de confirmation ou la page de connexion). */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->first();

        // Réponse identique que le compte existe ou non (pas d'énumération d'adresses).
        if ($user && $user->type === 'client' && ! $user->email_verified_at) {
            $sent = self::sendActivationMail($user);
            if (! $sent) {
                return back()->with('verify_email', $user->email)->with('error', __('onboarding.resend_failed'));
            }
        }

        return back()->with('verify_email', $request->input('email'))->with('resent', __('onboarding.resent'));
    }

    /**
     * URL signée sans le domaine dans la signature : le lien reste valide quelle que soit
     * l'adresse d'accès au site (localhost, 127.0.0.1:8000, sous-dossier, domaine final),
     * alors qu'une signature « absolue » dépend de l'hôte exact utilisé à l'envoi.
     */
    public static function activationUrl(User $user): string
    {
        $path = URL::temporarySignedRoute(
            'verification.verify',
            now()->addDays(7),
            ['uuid' => $user->uuid, 'hash' => sha1($user->email)],
            absolute: false
        );

        return url($path);
    }

    public static function sendActivationMail(User $user): bool
    {
        $method = \App\Models\SiteContact::current()->activationMethod();

        // Méthode « code OTP » : un code à 6 chiffres est envoyé, à saisir dans l'application (pas de lien).
        if ($method === 'otp') {
            return ActivationOtpController::sendCode($user);
        }

        // Aucune méthode (par défaut) : aucun mail, le compte est validé d'office.
        if ($method === 'none') {
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            return true;
        }

        try {
            $url = self::activationUrl($user);
            Mail::to($user->email)->locale($user->locale ?: 'fr')->send(new VerifyAccountMail($user, $url));

            // Mode test (mailer log) : lien en clair, copiable tel quel depuis le log
            // (dans le HTML du mail il apparaît avec « &amp; », qui casse la signature).
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                Log::info("[MODE TEST] Lien d'activation pour {$user->email} : {$url}");
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailVerification: échec envoi activation', ['user_id' => $user->id, 'message' => $e->getMessage()]);

            return false;
        }
    }
}
