<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Mail\AdminNewAccountMail;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SignupController extends Controller
{
    private const SUPPORTED_LOCALES = ['fr', 'en', 'pl', 'es', 'bg', 'hu', 'it', 'de', 'lt', 'ro', 'lv', 'nl', 'pt', 'hr', 'sk', 'sl', 'mt'];

    public function create(string $locale)
    {
        return view('auth.register', ['locale' => $locale]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => 'required|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $locale = $request->input('locale', app()->getLocale());
        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'en';
        }

        // Compte inactif : email_verified_at reste null jusqu'au clic sur le lien d'activation.
        // La devise est toujours l'euro, aucun choix n'est proposé à l'inscription.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'type'     => 'client',
            'gender'   => 'N',
            'phone'    => $data['phone'],
            'currency' => 'EUR',
            'locale'   => $locale,
        ]);

        $user->assignRole('client');

        $sent = EmailVerificationController::sendActivationMail($user);

        $this->notifyAdmins($user);

        // Sans e-mail d'activation (interrupteur éteint), le compte est déjà validé : connexion directe à l'espace client.
        if ($user->fresh()->email_verified_at) {
            Auth::login($user->fresh());
            $request->session()->regenerate();

            return redirect()->route('client.app.home')->with('success', __('onboarding.signup_done_direct'));
        }

        // Validation par code OTP : on invite le client à saisir le code reçu par e-mail
        if (\App\Models\SiteContact::current()->activationMethod() === 'otp') {
            $request->session()->put('activation_user_id', $user->id);
            $to = redirect()->route('activation.otp')->with('signup_ok', __('onboarding.signup_done_verify'));

            return $sent ? $to : $to->withErrors(['code' => __('onboarding.resend_failed')]);
        }

        $redirect = redirect()->route('verification.notice')->with('verify_email', $user->email)->with('signup_ok', __('onboarding.signup_done_verify'));

        return $sent ? $redirect : $redirect->with('error', __('onboarding.resend_failed'));
    }

    /**
     * Auto-inscription publique : pas de created_by (aucun admin assigné) —
     * on notifie donc tous les super-admins, à la différence de
     * AdminNotification::recipientAdminIds() utilisé pour les clients déjà rattachés.
     */
    private function notifyAdmins(User $client): void
    {
        $adminIds = User::role('super-admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            AdminNotification::forAdmin(
                $adminId,
                'account',
                'Nouveau compte créé — ' . $client->name,
                $client->name . ' (' . $client->email . ') vient de créer un compte via le site public.',
                ['client_id' => $client->id]
            );

            $admin = User::find($adminId);
            if ($admin) {
                try {
                    Mail::to($admin->email)->send(new AdminNewAccountMail($client, $admin->locale ?? 'fr'));
                } catch (\Exception) {
                    // La notification en cloche (AdminNotification) reste créée même si l'email échoue.
                }
            }
        }
    }
}
