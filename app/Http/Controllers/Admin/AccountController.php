<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountMovementMail;
use App\Models\AccountMovement;
use App\Models\Card;
use App\Rules\ValidIban;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ClientNotification;
use App\Models\Currency;
use App\Models\PendingCredit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $auth    = Auth::user();
        $isSuperAdmin = $auth->hasRole('super-admin');

        $query = User::where('type', 'client')->with('bankAccount');

        if (! $isSuperAdmin) {
            $adminId = $auth->id;
            $query->where(function ($q) use ($adminId) {
                $q->where('created_by', $adminId);
            });
        }

        // Recherche et tri passent cote serveur : ils etaient faits en JavaScript sur
        // les lignes presentes dans le DOM, ce qui ne couvrirait plus que la page
        // courante une fois la pagination en place.
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like);
            });
        }

        // Liste blanche : $sort part de l URL et finit dans un ORDER BY.
        $sort = $request->query('sort') === 'balance' ? 'balance' : 'name';
        $dir  = $request->query('dir')  === 'desc'    ? 'desc'    : 'asc';

        // Les cartes de synthese doivent porter sur l ENSEMBLE des comptes, pas sur la
        // page affichee : on agrege donc avant de paginer, sinon le solde total et les
        // compteurs ne refleteraient que les 20 lignes courantes.
        $totalAccounts = (clone $query)->count();
        $totalBalance  = (clone $query)->sum('balance');
        $positiveCount = (clone $query)->where('balance', '>', 0)->count();
        $negativeCount = (clone $query)->where('balance', '<', 0)->count();

        // Etait un ->get() : la vue chargeait la totalite des clients, sans pagination
        // ni limite. appends() preserve les filtres de recherche entre les pages.
        $clients = $query->orderBy($sort, $dir)->paginate(20)->appends($request->query());

        return view('admin.accounts.index', compact(
            'clients', 'isSuperAdmin', 'totalAccounts', 'totalBalance', 'positiveCount', 'negativeCount', 'search', 'sort', 'dir'
        ));
    }

    public function show(User $account)
    {
        $this->authorizeAccount($account);

        $movements = AccountMovement::where('user_id', $account->id)
            ->with('admin:id,name')
            ->latest()
            ->paginate(20);

        $card = $account->card()->first();

        // Dépenses carte du mois (comparées au plafond choisi par le client)
        $cardSpent = (float) AccountMovement::where('user_id', $account->id)->where('kind', 'card')
            ->where('created_at', '>=', now()->startOfMonth())->sum('amount');

        $pendingCredits = $account->pendingCredits()->latest()->limit(15)->get();
        $pendingBalance = $account->pendingBalance();

        return view('admin.accounts.show', compact('account', 'movements', 'card', 'cardSpent', 'pendingCredits', 'pendingBalance'));
    }

    public function credit(Request $request, User $account)
    {
        return $this->record($request, $account, 'credit');
    }

    public function debit(Request $request, User $account)
    {
        return $this->record($request, $account, 'debit');
    }

    /**
     * Crédit ou débit : l'administrateur choisit le type d'opération (virement SEPA / temps réel, dépôt,
     * paiement par carte, retrait, frais…) et renseigne la contrepartie. Le client reçoit une notification
     * et un e-mail qui reprennent ces informations.
     */
    private function record(Request $request, User $account, string $direction)
    {
        $this->authorizeAccount($account);

        $request->merge([
            'counterparty_iban' => strtoupper(preg_replace('/[\s\x{00A0}]+/u', '', (string) $request->input('counterparty_iban'))) ?: null,
        ]);

        $validated = $request->validate([
            'kind'              => ['required', 'string', 'in:' . implode(',', AccountMovement::KINDS[$direction])],
            'amount'            => 'required|numeric|min:0.01|max:9999999',
            'counterparty'      => ['nullable', 'string', 'max:120', 'required_if:kind,card'],
            'counterparty_iban' => ['nullable', 'string', 'max:40', new ValidIban()],
            'reference'         => 'nullable|string|max:60',
            'note'              => 'nullable|string|max:255',
        ]);

        $kind   = $validated['kind'];
        $amount = round((float) $validated['amount'], 2);
        $cur    = $account->currency ?? Currency::default();
        $cardId = null;

        // Dépense par carte : la carte du client doit être active et le plafond (cumul du mois) respecté
        if ($kind === 'card') {
            $card = $account->card()->first();
            if (! $card) {
                return back()->withErrors(['kind' => 'Ce client n\'a pas de carte : impossible d\'enregistrer une dépense par carte.'])->withInput();
            }
            if ($card->status !== Card::STATUS_ACTIVE) {
                return back()->withErrors(['kind' => 'La carte du client est ' . ($card->isSuspended() ? 'suspendue' : 'bloquée') . ' : aucune dépense ne peut être enregistrée.'])->withInput();
            }
            $spent = (float) AccountMovement::where('user_id', $account->id)->where('kind', 'card')
                ->where('created_at', '>=', now()->startOfMonth())->sum('amount');
            $limit = $card->effectiveLimit(); // choix du client, borné par le plafond défini par l'administration
            if ($spent + $amount > $limit) {
                return back()->withErrors(['amount' => 'Plafond de la carte dépassé : ' . number_format($spent, 2, ',', ' ') . ' déjà dépensés ce mois-ci sur '
                    . number_format($limit, 0, ',', ' ') . ' ' . $cur . '.'])->withInput();
            }
            $cardId = $card->id;
        }

        $movement = DB::transaction(function () use ($account, $validated, $direction, $kind, $amount, $cur, $cardId) {
            $before = (float) $account->balance;
            $after  = $direction === 'credit' ? $before + $amount : $before - $amount;
            $direction === 'credit' ? $account->increment('balance', $amount) : $account->decrement('balance', $amount);

            return AccountMovement::create([
                'user_id'           => $account->id,
                'admin_id'          => Auth::id(),
                'type'              => $direction,
                'kind'              => $kind,
                'counterparty'      => $validated['counterparty'] ?? null,
                'counterparty_iban' => in_array($kind, AccountMovement::TRANSFER_KINDS, true) ? ($validated['counterparty_iban'] ?? null) : null,
                'reference'         => $validated['reference'] ?? null,
                'card_id'           => $cardId,
                'amount'            => $amount,
                'currency'          => $cur,
                'balance_before'    => $before,
                'balance_after'     => $after,
                'note'              => $validated['note'] ?? null,
            ]);
        });

        $this->notifyClient($account, $movement);

        return back()->with('success', ($direction === 'credit' ? 'Compte crédité de ' : 'Compte débité de ') . number_format($amount, 2, ',', ' ') . ' ' . $cur
            . ' (' . $movement->kindLabel('fr') . '). Le client a été prévenu par notification et par e-mail.');
    }

    /** Annonce un montant au client sans le créditer : il reste « en attente » jusqu'à sa validation. */
    public function holdPending(Request $request, User $account)
    {
        $this->authorizeAccount($account);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'label'  => 'nullable|string|max:120',
            'note'   => 'nullable|string|max:255',
        ]);

        $amount = round((float) $data['amount'], 2);
        $cur    = $account->currency ?? Currency::default();

        PendingCredit::create([
            'user_id'  => $account->id,
            'admin_id' => Auth::id(),
            'amount'   => $amount,
            'currency' => $cur,
            'label'    => $data['label'] ?? null,
            'note'     => $data['note'] ?? null,
        ]);

        $locale = $account->locale ?? 'fr';
        ClientNotification::forUser(
            $account->id,
            'system',
            __('app.notif_pending_added', [], $locale),
            __('app.notif_pending_added_body', ['amount' => number_format($amount, 2, ',', ' '), 'currency' => $cur], $locale),
            ['amount' => $amount, 'currency' => $cur, 'kind' => 'pending']
        );

        return back()->with('success', number_format($amount, 2, ',', ' ') . ' ' . $cur . ' ajoutés au solde en attente. Le client en est informé.');
    }

    /** Valide un montant en attente : il devient du solde disponible (crédit du compte). */
    public function validatePending(User $account, PendingCredit $credit)
    {
        $this->authorizeAccount($account);
        abort_unless($credit->user_id === $account->id, 404);

        if (! $credit->isPending()) {
            return back()->withErrors(['amount' => 'Ce montant a déjà été traité.']);
        }

        $movement = DB::transaction(function () use ($account, $credit) {
            $locked = PendingCredit::whereKey($credit->id)->lockForUpdate()->first();
            if (! $locked || ! $locked->isPending()) {
                return null;
            }
            $before = (float) $account->fresh()->balance;
            $amount = (float) $locked->amount;
            $account->increment('balance', $amount);

            $movement = AccountMovement::create([
                'user_id'        => $account->id,
                'admin_id'       => Auth::id(),
                'type'           => 'credit',
                'kind'           => 'deposit',
                'counterparty'   => $locked->label,
                'reference'      => 'ATT-' . $locked->id,
                'amount'         => $amount,
                'currency'       => $locked->currency,
                'balance_before' => $before,
                'balance_after'  => $before + $amount,
                'note'           => $locked->note ?: 'Montant en attente validé',
            ]);

            $locked->update(['status' => PendingCredit::VALIDATED, 'validated_by' => Auth::id(), 'movement_id' => $movement->id, 'resolved_at' => now()]);

            return $movement;
        });

        if (! $movement) {
            return back()->withErrors(['amount' => 'Ce montant a déjà été traité.']);
        }

        $this->notifyClient($account, $movement);

        return back()->with('success', number_format($movement->amount, 2, ',', ' ') . ' ' . $movement->currency . ' validés : ils sont désormais dans le solde du compte.');
    }

    /** Annule un montant en attente (il n'est jamais crédité). */
    public function cancelPending(User $account, PendingCredit $credit)
    {
        $this->authorizeAccount($account);
        abort_unless($credit->user_id === $account->id, 404);

        if (! $credit->isPending()) {
            return back()->withErrors(['amount' => 'Ce montant a déjà été traité.']);
        }

        $credit->update(['status' => PendingCredit::CANCELLED, 'validated_by' => Auth::id(), 'resolved_at' => now()]);

        return back()->with('success', 'Montant en attente annulé.');
    }

    /** Plafond maximal de la carte : défini par l'administration ; le plafond choisi par le client est ramené dans cette limite. */
    public function updateCardCap(Request $request, User $account)
    {
        $this->authorizeAccount($account);

        $card = $account->card()->first();
        if (! $card) {
            return back()->withErrors(['spending_cap' => 'Ce client n\'a pas de carte.']);
        }

        $data = $request->validate([
            'spending_cap' => ['required', 'integer', 'min:' . Card::LIMIT_MIN, 'max:' . Card::CAP_CEILING],
        ]);

        $cap = (int) $data['spending_cap'];
        $card->update([
            'spending_cap'   => $cap,
            'spending_limit' => min((int) ($card->spending_limit ?: Card::LIMIT_MIN), $cap),
        ]);

        $locale = $account->locale ?? 'fr';
        ClientNotification::forUser(
            $account->id,
            'system',
            __('app.notif_card_cap', [], $locale),
            __('app.notif_card_cap_body', ['amount' => number_format($cap, 0, ',', ' '), 'currency' => $account->currency ?? Currency::default()], $locale),
            ['cap' => $cap, 'kind' => 'card_cap']
        );

        return back()->with('success', 'Plafond de la carte fixé à ' . number_format($cap, 0, ',', ' ') . ' ' . ($account->currency ?? Currency::default()) . '.');
    }

    /** Notification dans l'application + e-mail, dans la langue du client. */
    private function notifyClient(User $account, AccountMovement $movement): void
    {
        $locale = $account->locale ?? 'fr';
        $credit = $movement->type === 'credit';
        $amount = number_format($movement->amount, 2, ',', ' ');

        $body = __($credit ? 'app.notif_account_credited_body' : 'app.notif_account_debited_body', ['amount' => $amount, 'currency' => $movement->currency], $locale)
            . ' — ' . $movement->kindLabel($locale)
            . ($movement->counterparty ? ' · ' . $movement->counterparty : '')
            . ($movement->note ? ' · ' . $movement->note : '');

        ClientNotification::forUser(
            $account->id,
            'system',
            __($credit ? 'app.notif_account_credited' : 'app.notif_account_debited', [], $locale),
            $body,
            ['amount' => $movement->amount, 'currency' => $movement->currency, 'kind' => $movement->kind]
        );

        try {
            Mail::to($account->email)->locale($locale)->send(new AccountMovementMail($account, $movement));
        } catch (\Throwable $e) {
            Log::error('AccountMovementMail failed for client ' . $account->id . ': ' . $e->getMessage());
        }
    }

    private function authorizeAccount(User $client): void
    {
        $auth = Auth::user();
        if ($auth->hasRole('super-admin')) return;

        $adminId   = $auth->id;
        $isManaged = $client->created_by === $adminId;

        abort_unless($isManaged, 403, 'Accès non autorisé à ce compte.');
    }
}
