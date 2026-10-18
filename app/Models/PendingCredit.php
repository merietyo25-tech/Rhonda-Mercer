<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Montant en attente de validation : il rejoint le solde du compte une fois validé par l'administration. */
class PendingCredit extends Model
{
    public const PENDING   = 'pending';
    public const VALIDATED = 'validated';
    public const CANCELLED = 'cancelled';

    protected $fillable = ['user_id', 'admin_id', 'validated_by', 'amount', 'currency', 'label', 'note', 'status', 'movement_id', 'loan_application_id', 'resolved_at'];

    protected $casts = [
        'amount'      => 'float',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }

    public function isPending(): bool { return $this->status === self::PENDING; }
}
