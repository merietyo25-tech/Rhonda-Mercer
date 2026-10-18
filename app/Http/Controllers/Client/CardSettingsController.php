<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Réglages de la carte par le client : suspension / réactivation et plafond de dépenses. */
class CardSettingsController extends Controller
{
    private function ownCard(Card $card): Card
    {
        abort_unless($card->user_id === Auth::id(), 404);

        return $card;
    }

    /** Suspend la carte, ou la réactive si elle l'était. Une carte bloquée par l'administration ne peut pas être réactivée ici. */
    public function toggleSuspend(Card $card)
    {
        $this->ownCard($card);

        if ($card->status === Card::STATUS_BLOCKED) {
            return back();
        }

        $suspend = $card->status === Card::STATUS_ACTIVE;
        $card->update(['status' => $suspend ? Card::STATUS_SUSPENDED : Card::STATUS_ACTIVE]);

        return back()->with('success', __($suspend ? 'cards.suspended_done' : 'cards.resumed_done'));
    }

    /** Plafond de dépenses : entre 1 000 et le plafond maximal défini par l'administration. */
    public function updateLimit(Request $request, Card $card)
    {
        $this->ownCard($card);

        $data = $request->validate([
            'spending_limit' => ['required', 'integer', 'min:' . Card::LIMIT_MIN, 'max:' . max(Card::LIMIT_MIN, $card->cap())],
        ]);

        $card->update(['spending_limit' => (int) $data['spending_limit']]);

        return back()->with('success', __('cards.limit_saved'));
    }
}
