<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Espace « Prêts » de l'application client : simulation, demande et suivi. */
class LoanController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $applications = LoanApplication::where('user_id', $user->id)->latest()->get();
        $hasPending   = $applications->contains(fn ($a) => $a->isPending());

        return view('client.app.loans.index', compact('user', 'applications', 'hasPending'));
    }

    public function create()
    {
        $user = Auth::user();

        if (LoanApplication::where('user_id', $user->id)->where('status', LoanApplication::PENDING)->exists()) {
            return redirect()->route('client.app.loans')->with('error', __('loans.already_pending'));
        }

        $settings = LoanApplication::settings();

        return view('client.app.loans.create', compact('user', 'settings'));
    }

    public function store(Request $request)
    {
        $user     = Auth::user();
        $settings = LoanApplication::settings();

        if (LoanApplication::where('user_id', $user->id)->where('status', LoanApplication::PENDING)->exists()) {
            return redirect()->route('client.app.loans')->with('error', __('loans.already_pending'));
        }

        $data = $request->validate([
            'amount'          => ['required', 'numeric', 'min:' . $settings['min'], 'max:' . $settings['max']],
            'duration_months' => ['required', 'integer', 'min:' . $settings['dmin'], 'max:' . $settings['dmax']],
            'purpose'         => ['required', 'in:' . implode(',', LoanApplication::PURPOSES)],
            'message'         => ['nullable', 'string', 'max:1000'],
        ]);

        $amount  = round((float) $data['amount'], 2);
        $months  = (int) $data['duration_months'];
        $monthly = LoanApplication::monthlyPayment($amount, $months, $settings['rate']);

        $application = DB::transaction(function () use ($user, $data, $amount, $months, $monthly, $settings) {
            $app = LoanApplication::create([
                'user_id'         => $user->id,
                'amount'          => $amount,
                'duration_months' => $months,
                'purpose'         => $data['purpose'],
                'message'         => $data['message'] ?? null,
                'interest_rate'   => $settings['rate'],
                'monthly_payment' => $monthly,
                'total_cost'      => round($monthly * $months - $amount, 2),
                'currency'        => $user->currency ?: 'EUR',
            ]);
            $app->update(['reference' => 'LN-' . now()->format('y') . '-' . str_pad((string) $app->id, 5, '0', STR_PAD_LEFT)]);

            return $app;
        });

        foreach (AdminNotification::recipientAdminIds($user) as $adminId) {
            try {
                AdminNotification::forAdmin(
                    $adminId, 'account', 'Demande de prêt',
                    $user->name . ' demande un prêt de ' . number_format($amount, 2, ',', ' ') . ' ' . $application->currency . ' sur ' . $months . ' mois.',
                    ['client_id' => $user->id]
                );
            } catch (\Throwable $e) {
                Log::warning('LoanApplication notify failed', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('client.app.loans.show', $application)->with('success', __('loans.sent'));
    }

    public function show(LoanApplication $application)
    {
        abort_unless($application->user_id === Auth::id(), 404);

        return view('client.app.loans.show', ['user' => Auth::user(), 'application' => $application]);
    }

    public function cancel(LoanApplication $application)
    {
        abort_unless($application->user_id === Auth::id(), 404);

        if ($application->isPending()) {
            $application->update(['status' => LoanApplication::CANCELLED, 'decided_at' => now()]);
        }

        return redirect()->route('client.app.loans')->with('success', __('loans.cancelled_done'));
    }
}
