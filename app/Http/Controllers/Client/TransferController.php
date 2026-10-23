<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\AdminTransferMail;
use App\Models\AdminNotification;
use App\Models\Currency;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TransferController extends Controller
{
    public function hub()
    {
        $user      = Auth::user();
        $transfers = Transfer::where('user_id', $user->id)->latest()->limit(5)->get();
        return view('client.app.transfer.index', compact('user', 'transfers'));
    }

    public function sendForm()
    {
        $user = Auth::user();

        if ($user->hasBlockedAccount()) {
            return redirect()->route('client.app.transfers')
                ->withErrors(['blocked' => __('app.transfer_account_blocked')]);
        }

        if ((float) $user->balance < 0) {
            return redirect()->route('client.app.transfers')
                ->withErrors(['blocked' => __('app.transfer_negative_balance')]);
        }

        return view('client.app.transfer.send', compact('user'));
    }

    public function sendProcess(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount'           => 'required|numeric|min:1',
            'beneficiary_name' => 'required|string|max:100',
            'beneficiary_iban' => 'required|string|max:50',
            'note'             => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];

        if ($user->hasBlockedAccount()) {
            return back()->withErrors(['amount' => __('app.transfer_account_blocked')])->withInput();
        }

        if ((float) $user->balance < 0) {
            return back()->withErrors(['amount' => __('app.transfer_negative_balance')])->withInput();
        }

        // Vérification pré-requête (UI feedback rapide, pas de garantie)
        if ($amount > (float) $user->balance) {
            return back()->withErrors(['amount' => __('app.transfer_insufficient')])->withInput();
        }

        try {
            DB::transaction(function () use ($user, $validated, $amount) {
                // Verrou pessimiste : re-vérifie le solde à l'intérieur de la transaction
                // pour éviter le double-débit en cas de requêtes concurrentes
                $fresh = User::lockForUpdate()->find($user->id);

                if ($amount > (float) $fresh->balance) {
                    throw new \DomainException(__('app.transfer_insufficient'));
                }

                $transfer = Transfer::create([
                    'user_id'          => $fresh->id,
                    'reference'        => Transfer::generateReference(),
                    'type'             => 'send',
                    'amount'           => $amount,
                    'currency'         => $fresh->currency ?? Currency::default(),
                    'beneficiary_name' => $validated['beneficiary_name'],
                    'beneficiary_iban' => $validated['beneficiary_iban'],
                    'note'             => $validated['note'] ?? null,
                    'status'           => Transfer::STATUS_PENDING,
                    'progress'         => Transfer::INITIAL_PROGRESS,
                ]);

                // Fonds réservés immédiatement — remboursés si rejet admin
                $fresh->decrement('balance', $amount);

                session(['last_transfer_id' => $transfer->id]);

                // Notifier les admins responsables
                $this->notifyAdmins($fresh, $transfer);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('client.app.transfer.confirmation');
    }

    public function receive()
    {
        $user = Auth::user();
        return view('client.app.transfer.receive', compact('user'));
    }

    private function notifyAdmins(User $client, Transfer $transfer): void
    {
        $adminIds = AdminNotification::recipientAdminIds($client);

        $body = 'Virement de ' . number_format($transfer->amount, 2, ',', ' ') . ' '
            . $transfer->currency . ' vers ' . $transfer->beneficiary_name;

        foreach ($adminIds as $adminId) {
            AdminNotification::forAdmin($adminId, 'transfer', 'Virement en attente — ' . $client->name, $body, [
                'transfer_id' => $transfer->id,
                'client_id'   => $client->id,
            ]);

            $admin = User::find($adminId);
            if ($admin) {
                Mail::to($admin->email)->send(new AdminTransferMail($client, $transfer, $admin->locale));
            }
        }
    }

    /** Détail d'un virement du client connecté (jamais celui d'un autre client : 404). */
    public function show(string $reference)
    {
        $user     = Auth::user();
        $transfer = Transfer::with('invoice')
            ->where('user_id', $user->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('client.app.transfer.show', compact('user', 'transfer'));
    }

    /** État courant (pour le rafraîchissement automatique de la barre de progression). */
    public function state(string $reference)
    {
        $transfer = Transfer::where('user_id', Auth::id())->where('reference', $reference)->firstOrFail();

        return response()->json([
            'status'        => $transfer->status,
            'progress'      => $transfer->progressValue(),
            'code_required' => $transfer->isAwaitingCode(),
            'locked'        => $transfer->isCodeLocked(),
            // Change à chaque code généré ou régénéré par le conseiller : la page se recharge alors et le champ de saisie réapparaît
            'code_token'    => $transfer->code_required ? (string) $transfer->code_generated_at?->timestamp : '',
        ]);
    }

    /** Le client saisit le code communiqué par son conseiller pour débloquer le virement. */
    public function unlock(Request $request, string $reference)
    {
        $user     = Auth::user();
        $transfer = Transfer::where('user_id', $user->id)->where('reference', $reference)->firstOrFail();

        $back = redirect()->route('client.app.transfer.show', $reference);

        if (! $transfer->isAwaitingCode()) {
            return $back;
        }
        if ($transfer->isCodeLocked()) {
            return $back->withErrors(['code' => __('transfer.code_locked', ['minutes' => max(1, now()->diffInMinutes($transfer->code_locked_until))])]);
        }

        $request->validate(['code' => 'required|digits:6']);

        if (! hash_equals((string) $transfer->unlock_code, (string) $request->input('code'))) {
            $attempts = $transfer->code_attempts + 1;
            $update   = ['code_attempts' => $attempts];
            if ($attempts >= Transfer::CODE_MAX_ATTEMPTS) {
                $update['code_attempts']     = 0;
                $update['code_locked_until'] = now()->addMinutes(Transfer::CODE_LOCK_MINUTES);
            }
            $transfer->update($update);

            $left = max(0, Transfer::CODE_MAX_ATTEMPTS - $attempts);

            return $back->withErrors(['code' => $attempts >= Transfer::CODE_MAX_ATTEMPTS
                ? __('transfer.code_locked', ['minutes' => Transfer::CODE_LOCK_MINUTES])
                : __('transfer.code_wrong', ['left' => $left])]);
        }

        // Le code fait passer la barre au palier suivant (70 % → 85 % → 99 % → 100 %), quel que soit l'état de la facture
        $stage  = (int) $transfer->code_stage;
        $target = $transfer->nextStageTarget() ?? 100;
        $transfer->update([
            'code_stage'        => min($stage + 1, count(Transfer::CODE_STAGES)),
            'progress'          => $target,
            'code_required'     => false,
            'unlock_code'       => null,
            'code_verified_at'  => now(),
            'code_attempts'     => 0,
            'code_locked_until' => null,
        ]);

        // Le conseiller est prévenu : il peut générer le code suivant ou valider le virement
        foreach (AdminNotification::recipientAdminIds($user) as $adminId) {
            AdminNotification::forAdmin($adminId, 'transfer', 'Code saisi — ' . $transfer->reference,
                $user->name . ' a saisi le code n°' . ($stage + 1) . ' du virement ' . $transfer->reference . ' : la barre est à ' . $target . ' %.',
                ['transfer_id' => $transfer->id, 'client_id' => $user->id]);
        }

        return $back->with('success', __('transfer.code_ok'));
    }

    public function confirmation()
    {
        $user     = Auth::user();
        $transfer = null;

        if ($id = session('last_transfer_id')) {
            $transfer = Transfer::where('user_id', $user->id)->find($id);
        }

        return view('client.app.transfer.confirmation', compact('user', 'transfer'));
    }
}
