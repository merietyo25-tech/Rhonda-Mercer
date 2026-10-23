<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TransferActionMail;
use App\Models\ClientNotification;
use App\Models\Invoice;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransferValidationController extends Controller
{
    public function index(Request $request)
    {
        $auth        = Auth::user();
        $isSuperAdmin = $auth->hasRole('super-admin');

        $query = Transfer::with(['user:id,name,email,currency'])
            ->where('type', 'send');

        if (! $isSuperAdmin) {
            $adminId = $auth->id;
            $query->whereHas('user', function ($q) use ($adminId) {
                $q->where('created_by', $adminId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', [Transfer::STATUS_PENDING, Transfer::STATUS_FEE_REQUIRED]);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
            });
        }

        $transfers = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'pending'      => $this->baseQuery($isSuperAdmin)->where('status', Transfer::STATUS_PENDING)->count(),
            'fee_required' => $this->baseQuery($isSuperAdmin)->where('status', Transfer::STATUS_FEE_REQUIRED)->count(),
            'completed'    => $this->baseQuery($isSuperAdmin)->where('status', Transfer::STATUS_COMPLETED)->count(),
            'rejected'     => $this->baseQuery($isSuperAdmin)->where('status', Transfer::STATUS_REJECTED)->count(),
        ];

        return view('admin.transfers.index', compact('transfers', 'stats', 'isSuperAdmin'));
    }

    public function approve(Request $request, Transfer $transfer)
    {
        $this->authorizeTransfer($transfer);
        abort_unless(in_array($transfer->status, [Transfer::STATUS_PENDING, Transfer::STATUS_FEE_REQUIRED]), 422, 'Statut invalide.');

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($transfer, $request) {
            $transfer->update([
                'status'        => Transfer::STATUS_COMPLETED,
                'progress'      => 100,
                'code_required' => false,
                'unlock_code'   => null,
                'admin_id'      => Auth::id(),
                'admin_note'    => $request->admin_note,
                'processed_at'  => now(),
            ]);
        });

        $client = $transfer->user;
        $cur    = $transfer->currency;
        $amount = number_format($transfer->amount, 2, ',', ' ');

        ClientNotification::notifyUser(
            $client,
            'transfer',
            'app.notif_transfer_approved',
            'app.notif_transfer_approved_body',
            ['reference' => $transfer->reference, 'amount' => $amount, 'currency' => $cur, 'name' => $transfer->beneficiary_name],
            ['transfer_id' => $transfer->id, 'reference' => $transfer->reference]
        );

        try {
            Mail::to($client->email)
                ->locale($client->locale ?? 'fr')
                ->send(new TransferActionMail($transfer, 'approved'));
        } catch (\Throwable $e) {
            Log::error('TransferActionMail (approved) failed: ' . $e->getMessage());
        }

        return back()->with('success', "Virement {$transfer->reference} validé.");
    }

    /**
     * Générateur de code : crée le code à 4 chiffres du prochain palier. Le client le saisit pour que la
     * barre avance — 1er code → 70 %, 2e → 99 %, 3e (dernier) → 100 %. La progression ne dépend ni du
     * paiement de la facture de frais ni du fait que la barre soit déjà à 100 % : seule la saisie des codes la fait avancer.
     */
    public function progress(Request $request, Transfer $transfer)
    {
        $this->authorizeTransfer($transfer);
        abort_unless(in_array($transfer->status, [Transfer::STATUS_PENDING, Transfer::STATUS_FEE_REQUIRED], true), 422, 'Ce virement ne peut plus avancer.');

        $target = $transfer->nextStageTarget();
        if ($target === null) {
            return back()->with('success', "Les 3 codes du virement {$transfer->reference} ont déjà été saisis (barre à 100 %).");
        }

        $transfer->update([
            'code_required'     => true,
            'unlock_code'       => Transfer::newUnlockCode(),
            'code_generated_at' => now(),
            'code_verified_at'  => null,
            'code_attempts'     => 0,
            'code_locked_until' => null,
            'admin_id'          => Auth::id(),
        ]);

        $client = $transfer->user;
        $locale = $client->locale ?? 'fr';
        ClientNotification::forUser(
            $client->id,
            'transfer',
            __('transfer.notif_code_title', [], $locale),
            __('transfer.notif_code_body', ['reference' => $transfer->reference, 'progress' => $transfer->progress], $locale),
            ['transfer_id' => $transfer->id, 'reference' => $transfer->reference]
        );

        return back()->with('success', 'Code n°' . ((int) $transfer->code_stage + 1) . ' du virement ' . $transfer->reference
            . ' (→ ' . $target . ' %) : ' . $transfer->unlock_code . ' — à communiquer au client.');
    }

    public function reject(Request $request, Transfer $transfer)
    {
        $this->authorizeTransfer($transfer);
        abort_unless(in_array($transfer->status, [Transfer::STATUS_PENDING, Transfer::STATUS_FEE_REQUIRED]), 422, 'Statut invalide.');

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $client = $transfer->user;
        $amount = (float) $transfer->amount;
        $cur    = $transfer->currency;

        DB::transaction(function () use ($transfer, $request, $client, $amount) {
            $transfer->update([
                'status'       => Transfer::STATUS_REJECTED,
                'admin_id'     => Auth::id(),
                'admin_note'   => $request->admin_note,
                'processed_at' => now(),
            ]);

            // Rembourser les fonds réservés
            $client->increment('balance', $amount);
        });

        $locale    = $client->locale ?? 'fr';
        $notifBody = __('app.notif_transfer_rejected_body', [
            'reference' => $transfer->reference,
            'amount'    => number_format($amount, 2, ',', ' '),
            'currency'  => $cur,
        ], $locale);
        if ($request->admin_note) {
            $notifBody .= ' ' . $request->admin_note;
        }
        ClientNotification::forUser(
            $client->id,
            'transfer',
            __('app.notif_transfer_rejected', [], $locale),
            $notifBody,
            ['transfer_id' => $transfer->id, 'reference' => $transfer->reference]
        );

        try {
            Mail::to($client->email)
                ->locale($client->locale ?? 'fr')
                ->send(new TransferActionMail($transfer, 'rejected'));
        } catch (\Throwable $e) {
            Log::error('TransferActionMail (rejected) failed: ' . $e->getMessage());
        }

        return back()->with('success', "Virement {$transfer->reference} rejeté — solde recrédité.");
    }

    public function invoice(Request $request, Transfer $transfer)
    {
        $this->authorizeTransfer($transfer);
        abort_unless($transfer->status === Transfer::STATUS_PENDING, 422, 'Ce virement n\'est pas en attente.');

        $request->merge([
            'payment_iban' => strtoupper(preg_replace('/[\s\x{00A0}]+/u', '', (string) $request->input('payment_iban'))) ?: null,
            'payment_bic'  => strtoupper(preg_replace('/[[:space:]-]+/', '', (string) $request->input('payment_bic'))) ?: null,
        ]);

        $data = $request->validate([
            'fee_amount'  => 'required|numeric|min:0.01|max:9999999',
            'description' => 'nullable|string|max:500',
            'payment_iban' => ['nullable', 'string', 'max:40', new \App\Rules\ValidIban()],
            'payment_bic'  => ['nullable', 'string', 'regex:/^[A-Z0-9]{8}([A-Z0-9]{3})?$/'],
            'payment_holder' => ['nullable', 'string', 'max:100'],
            'payment_type'   => ['nullable', 'in:sepa,instant'],
        ]);

        if (empty($data['payment_iban']) && ! \App\Models\SiteContact::current()->payment_iban) {
            return back()->withErrors(['payment_iban' => 'Renseignez l\'IBAN sur lequel le client réglera les frais.'])->withInput();
        }

        $client   = $transfer->user;
        $currency = $transfer->currency;

        DB::transaction(function () use ($transfer, $data, $client, $currency, $request) {
            // Le nom et le type du virement figurent sur la facture : « Frais de traitement — Virement émis TRF-… vers Anna »
            $about = $transfer->typeLabel('fr') . ' ' . $transfer->reference . ($transfer->beneficiary_name ? ' — ' . $transfer->beneficiary_name : '');
            $desc  = ($data['description'] ?? null) ?: 'Frais de traitement';
            $desc  = $desc . ' (' . $about . ')';

            $invoice = Invoice::create([
                'reference'   => Invoice::generateReference(),
                'admin_id'    => Auth::id(),
                'client_id'   => $client->id,
                'transfer_id' => $transfer->id,
                'currency'    => $currency,
                'subtotal'    => $data['fee_amount'],
                'tax_rate'    => 0,
                'tax_amount'  => 0,
                'total'       => $data['fee_amount'],
                'payment_iban' => $data['payment_iban'] ?? null,
                'payment_bic'  => $data['payment_bic'] ?? null,
                'payment_holder' => $data['payment_holder'] ?? null,
                'payment_type'   => $data['payment_type'] ?? null,
                'status'      => Invoice::STATUS_SENT,
                'issue_date'  => now()->toDateString(),
                'description' => $desc,
                'note'        => 'Facture liée au virement ' . $transfer->reference . ' (' . $transfer->typeLabel('fr') . ' — ' . $transfer->beneficiary_name . ')',
                'items'       => [[
                    'kind'        => 'transfer_fee',                       // libellé recomposé dans la langue du lecteur
                    'text'        => ($data['description'] ?? null) ?: null, // texte libre saisi par l'admin, s'il y en a un
                    'description' => $desc,
                    'quantity'    => 1,
                    'unit_price'  => $data['fee_amount'],
                    'total'       => $data['fee_amount'],
                ]],
                'sent_at'     => now(),
            ]);

            $transfer->update([
                'status'     => Transfer::STATUS_FEE_REQUIRED,
                'admin_id'   => Auth::id(),
                'invoice_id' => $invoice->id,
                'admin_note' => 'Frais requis — facture ' . $invoice->reference,
            ]);
        });

        $invoice = Invoice::where('client_id', $client->id)->latest()->first();

        ClientNotification::notifyUser(
            $client,
            'system',
            'app.notif_fees_required',
            'app.notif_fees_required_body',
            ['amount' => number_format($data['fee_amount'], 2, ',', ' '), 'currency' => $currency, 'reference' => $transfer->reference],
            ['transfer_id' => $transfer->id, 'reference' => $transfer->reference]
        );

        try {
            Mail::to($client->email)
                ->locale($client->locale ?? 'fr')
                ->send(new TransferActionMail($transfer->fresh(['invoice']), 'fee_required'));
        } catch (\Throwable $e) {
            Log::error('TransferActionMail (fee_required) failed: ' . $e->getMessage());
        }

        return back()->with('success', "Facture de frais créée et envoyée au client pour le virement {$transfer->reference}.");
    }

    private function baseQuery(bool $isSuperAdmin): \Illuminate\Database\Eloquent\Builder
    {
        $query = Transfer::where('type', 'send');

        if (! $isSuperAdmin) {
            $adminId = Auth::id();
            $query->whereHas('user', function ($q) use ($adminId) {
                $q->where('created_by', $adminId);
            });
        }

        return $query;
    }

    private function authorizeTransfer(Transfer $transfer): void
    {
        $auth = Auth::user();
        if ($auth->hasRole('super-admin')) return;

        $adminId   = $auth->id;
        $client    = $transfer->user;
        $isManaged = $client->created_by === $adminId;

        abort_unless($isManaged, 403, 'Accès non autorisé à ce virement.');
    }
}
