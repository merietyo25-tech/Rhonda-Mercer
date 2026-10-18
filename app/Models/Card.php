<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    const STATUS_ACTIVE  = 'active';
    const STATUS_BLOCKED = 'blocked';
    /** Suspendue par le client lui-même (réversible par lui) ; « bloquée » est décidée par l'administration. */
    const STATUS_SUSPENDED = 'suspended';

    const LIMIT_MIN = 1000;
    const LIMIT_MAX = 5000;
    const LIMIT_STEP = 100;

    /** Plafond maximal par défaut ; un administrateur peut le modifier carte par carte (spending_cap). */
    const CAP_DEFAULT = 5000;
    const CAP_CEILING = 1000000;

    protected $fillable = ['user_id', 'assigned_by', 'holder_name', 'last_four', 'network', 'card_type', 'expires_at', 'status', 'spending_limit', 'spending_cap'];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** Plafond maximal défini par l'administration. */
    public function cap(): int
    {
        return (int) ($this->spending_cap ?: self::CAP_DEFAULT);
    }

    /** Plafond de dépenses en vigueur : le choix du client, borné par le plafond de l'administration. */
    public function effectiveLimit(): int
    {
        return min((int) ($this->spending_limit ?: self::LIMIT_MIN), $this->cap());
    }

    public function isSuspended(): bool { return $this->status === self::STATUS_SUSPENDED; }

    public function maskedNumber(): string
    {
        return '•••• •••• •••• ' . $this->last_four;
    }
}
