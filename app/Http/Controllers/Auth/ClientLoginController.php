<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\SiteContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientLoginController extends Controller
{
    private const OTP_TTL = 600;

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectAuthenticated(Auth::user());
        }

        $remembered = null;
        $raw = request()->cookie('solberg_remembered');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (isset($decoded['name'], $decoded['email'])) {
                $remembered = $decoded;
            }
        }

        return view('auth.login-client', compact('remembered'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password'   => 'required',
        ]);

        $identifier = $request->input('identifier');

        $user = User::where('email', $identifier)
                    ->orWhere('phone', $identifier)
                    ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['identifier' => __('auth.failed')])
                ->onlyInput('identifier');
        }

        if ($user->type !== 'client') {
            return back()->withErrors(['identifier' => __('auth.portal_clients_only')]);
        }

        // Compte non activé : l'adresse e-mail doit d'abord être confirmée. On envoie l'utilisateur
        // sur la page « vérifiez votre boîte mail » (renvoi possible) ; une fois activé, il passe à l'OTP.
        // E-mail d'activation désactivé dans les paramètres : aucun mail n'est envoyé, le compte est donc validé d'office.
        $method = SiteContact::current()->activationMethod();
        if (! $user->email_verified_at && $method === 'none') {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Validation par code OTP : on envoie un nouveau code et on ouvre la page de saisie
        if (! $user->email_verified_at && $method === 'otp') {
            $request->session()->put('activation_user_id', $user->id);
            \App\Http\Controllers\Auth\ActivationOtpController::sendCode($user);

            return redirect()->route('activation.otp')->with('error', __('onboarding.email_not_verified'));
        }

        if (! $user->email_verified_at) {
            return redirect()->route('verification.notice')
                ->with('verify_email', $user->email)
                ->with('error', __('onboarding.email_not_verified'));
        }

        if ($user->is_blocked) {
            return back()
                ->withErrors(['identifier' => __('auth.account_blocked')])
                ->onlyInput('identifier');
        }

        // OTP désactivé pour les clients : connexion directe, aucun code n'est envoyé.
        if (! SiteContact::current()->otp_clients_enabled) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->redirectAuthenticated($user)->withCookie(Cookie::make(
                'solberg_remembered',
                json_encode(['name' => $user->name, 'email' => $user->email]),
                60 * 24 * 30
            ));
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp_' . $user->id, Hash::make($otp), self::OTP_TTL);

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_remember', $request->boolean('remember'));

        try {
            Mail::to($user->email)->locale($user->locale ?: 'fr')->send(new OtpMail($otp, $user));
        } catch (\Throwable $e) {
            Log::error('ClientLoginController: échec envoi OTP', ['user_id' => $user->id, 'message' => $e->getMessage()]);
            return back()->withErrors(['identifier' => __('auth.otp_send_failed')])->onlyInput('identifier');
        }

        $cookie = Cookie::make(
            'solberg_remembered',
            json_encode(['name' => $user->name, 'email' => $user->email]),
            60 * 24 * 30
        );

        return redirect()->route('otp.show')->withCookie($cookie);
    }

    public function forgetAccount()
    {
        return redirect('/login')->withCookie(Cookie::forget('solberg_remembered'));
    }

    public function logout(Request $request)
    {
        $isStaff = Auth::check() && Auth::user()->type === 'staff';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($isStaff ? route('staff.login') : '/login');
    }

    private function redirectAuthenticated(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->route('super-admin.dashboard');
        }
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        // Clients → PWA (client.app.home)
        return redirect()->route('client.app.home');
    }
}
