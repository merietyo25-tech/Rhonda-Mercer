<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientNotification;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Demandes de prêt des clients : l'administration les accepte ou les refuse (avec un message pour le client). */
class LoanApplicationController extends Controller
{
    private function scoped()
    {
        $auth  = Auth::user();
        $query = LoanApplication::with('user:id,name,email,created_by,locale,currency');

        if (! $auth->hasRole('super-admin')) {
            $query->whereHas('user', fn ($q) => $q->where('created_by', $auth->id));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $query  = $this->scoped();

        if (in_array($status, [LoanApplication::PENDING, LoanApplication::APPROVED, LoanApplication::REJECTED, LoanApplication::CANCELLED], true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        $applications = $query->latest()->paginate(20)->appends($request->query());
        $pendingCount = $this->scoped()->where('status', LoanApplication::PENDING)->count();

        return view('admin.loans.index', compact('applications', 'status', 'pendingCount'));
    }

    /** Réglages des prêts : taux annuel, montants et durées proposés au client. */
    public function settings()
    {
        return view('admin.loans.settings', ['s' => LoanApplication::settings()]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'annual_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
            'min_amount'   => ['required', 'numeric', 'min:1', 'max:99999999'],
            'max_amount'   => ['required', 'numeric', 'gt:min_amount', 'max:99999999'],
            'duration_min' => ['required', 'integer', 'min:1', 'max:600'],
            'duration_max' => ['required', 'integer', 'gte:duration_min', 'max:600'],
        ], [
            'max_amount.gt'     => 'Le montant maximum doit être supérieur au montant minimum.',
            'duration_max.gte'  => 'La durée maximale doit être supérieure ou égale à la durée minimale.',
        ]);

        DB::table('loan_settings')->updateOrInsert(
            ['id' => DB::table('loan_settings')->value('id') ?? 1],
            $data + ['updated_at' => now(), 'created_at' => now()]
        );

        return redirect()->route('admin.loans.settings')->with('success', 'Réglages des prêts enregistrés. Ils s\'appliquent aux nouvelles demandes.');
    }

    public function approve(Request $request, LoanApplication $application)
    {
        return $this->decide($request, $application, LoanApplication::APPROVED);
    }

    public function reject(Request $request, LoanApplication $application)
    {
        return $this->decide($request, $application, LoanApplication::REJECTED);
    }

    private function decide(Request $request, LoanApplication $application, string $status)
    {
        $allowed = $this->scoped()->whereKey($application->id)->exists();
        abort_unless($allowed, 403, 'Accès non autorisé à cette demande.');

        if (! $application->isPending()) {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);

        $application->update([
            'status'     => $status,
            'admin_note' => $data['admin_note'] ?? null,
            'decided_by' => Auth::id(),
            'decided_at' => now(),
        ]);

        $approved = $status === LoanApplication::APPROVED;
        ClientNotification::notifyUser(
            $application->user,
            'system',
            $approved ? 'loans.notif_approved' : 'loans.notif_rejected',
            $approved ? 'loans.notif_approved_body' : 'loans.notif_rejected_body',
            [
                'reference' => $application->reference,
                'amount'    => number_format($application->amount, 2, ',', ' '),
                'currency'  => $application->currency,
            ],
            ['url' => '/app/loans/' . $application->id, 'kind' => 'loan']
        );

        return back()->with('success', $approved ? 'Demande acceptée : le client est prévenu.' : 'Demande refusée : le client est prévenu.');
    }
}
