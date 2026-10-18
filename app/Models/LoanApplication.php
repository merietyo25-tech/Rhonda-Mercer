<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LoanApplication extends Model
{
    public const PENDING   = 'pending';
    public const APPROVED  = 'approved';
    public const REJECTED  = 'rejected';
    public const CANCELLED = 'cancelled';

    public const PURPOSES = ['personal', 'auto', 'home', 'business', 'education'];

    public const DURATION_MIN = 6;
    public const DURATION_MAX = 84;

    protected $fillable = [
        'reference', 'user_id', 'amount', 'duration_months', 'purpose', 'message', 'interest_rate', 'monthly_payment',
        'total_cost', 'currency', 'status', 'admin_note', 'decided_by', 'decided_at',
    ];

    protected $casts = [
        'amount'          => 'float',
        'interest_rate'   => 'float',
        'monthly_payment' => 'float',
        'total_cost'      => 'float',
        'decided_at'      => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function decider(): BelongsTo { return $this->belongsTo(User::class, 'decided_by'); }

    public function isPending(): bool { return $this->status === self::PENDING; }

    /**
     * Paramètres du prêt fixés par l'administration (table loan_settings, ligne unique) ;
     * valeurs par défaut si aucune ligne n'existe.
     */
    public static function settings(): array
    {
        $row = DB::table('loan_settings')->first();

        return [
            'rate' => (float) ($row->annual_rate ?? 5.0),
            'min'  => (float) ($row->min_amount ?? 1000),
            'max'  => (float) ($row->max_amount ?? 50000),
        ];
    }

    /** Mensualité d'un prêt amortissable (taux annuel en %). */
    public static function monthlyPayment(float $amount, int $months, float $annualRate): float
    {
        if ($months <= 0) {
            return 0.0;
        }
        $r = $annualRate / 12 / 100;

        return round($r > 0 ? $amount * $r / (1 - pow(1 + $r, -$months)) : $amount / $months, 2);
    }
}
