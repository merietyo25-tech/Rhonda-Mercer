<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Validation du compte à l'inscription par code à 6 chiffres (option « Code OTP » de Site → Coordonnées),
 * à la place du lien d'activation. Le code est envoyé par e-mail dans la langue du client.
 */
class ActivationOtpController extends Controller
{
    private const TTL          = 900; // 15 minutes
    private const MAX_ATTEMPTS = 5;

    /** Génère et envoie un nouveau code ; retourne false si l'envoi échoue. */
    public static function sendCode(User $user): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('activation_otp_' . $user->id, Hash::make($code), self::TTL);
        Cache::forget('activation_otp_tries_' . $user->id);

        try {
            Mail::to($user->email)->locale($user->locale ?: 'fr')->send(new OtpMail($code, $user));

            // Mode test (mailer log) : code en clair dans le log
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                Log::info("[MODE TEST] Code de validation du compte pour {$user->email} : {$code}");
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('ActivationOtp: échec envoi du code', ['user_id' => $user->id, 'message' => $e->getMessage()]);

            return false;
        }
    }

    private function pendingUser(Request $request): ?User
    {
        $id   = $request->session()->get('activation_user_id');
        $user = $id ? User::find($id) : null;

        return ($user && $user->type === 'client' && ! $user->email_verified_at) ? $user : null;
    }

    public function show(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.activate-otp', ['masked' => $this->maskEmail($user->email)]);
    }

    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate(['code' => ['required', 'digits:6']]);

        $tries = (int) Cache::get('activation_otp_tries_' . $user->id, 0);
        $hash  = Cache::get('activation_otp_' . $user->id);

        if (! $hash) {
            return back()->withErrors(['code' => __('auth.otp_expired')]);
        }
        if ($tries >= self::MAX_ATTEMPTS) {
            Cache::forget('activation_otp_' . $user->id);

            return back()->withErrors(['code' => __('auth.otp_expired')]);
        }

        if (! Hash::check($data['code'], $hash)) {
            Cache::put('activation_otp_tries_' . $user->id, $tries + 1, self::TTL);

            return back()->withErrors(['code' => __('auth.otp_invalid', ['remaining' => max(0, self::MAX_ATTEMPTS - $tries - 1)])]);
        }

        $user->forceFill(['email_verified_at' => now()])->save();
        Cache::forget('activation_otp_' . $user->id);
        Cache::forget('activation_otp_tries_' . $user->id);
        $request->session()->forget('activation_user_id');

        return redirect()->route('login')->with('verified_status', __('onboarding.verified_success'));
    }

    public function resend(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return self::sendCode($user)
            ? back()->with('resent', __('onboarding.resent'))
            : back()->withErrors(['code' => __('onboarding.resend_failed')]);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 2) . str_repeat('•', max(1, mb_strlen($name) - 2)) . '@' . $domain;
    }
}
