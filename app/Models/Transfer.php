<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    const STATUS_PENDING      = 'pending';
    const STATUS_COMPLETED    = 'completed';
    const STATUS_REJECTED     = 'rejected';
    const STATUS_FEE_REQUIRED = 'fee_required';

    protected $fillable = [
        'user_id', 'admin_id', 'reference', 'type',
        'amount', 'currency',
        'beneficiary_name', 'beneficiary_iban',
        'note', 'admin_note', 'invoice_id',
        'status', 'processed_at',
        'progress', 'code_stage', 'code_required', 'unlock_code', 'code_generated_at', 'code_verified_at', 'code_attempts', 'code_locked_until',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
        'progress'          => 'integer',
        'code_stage'        => 'integer',
        'code_required'     => 'boolean',
        'unlock_code'       => \App\Casts\SafeEncrypted::class,
        'code_generated_at' => 'datetime',
        'code_verified_at'  => 'datetime',
        'code_locked_until' => 'datetime',
    ];

    /** Niveau de la barre dès l'émission du virement par le client : toujours 25 %, avant le 1er code. */
    public const INITIAL_PROGRESS = 25;

    /**
     * Paliers atteints à chaque code saisi par le client : le code est généré en 6 fois, un par palier —
     * 1er code → 40 %, 2e → 55 %, 3e → 70 %, 4e → 85 %, 5e → 99 %, 6e (dernier) → 100 %.
     */
    public const CODE_STAGES = [40, 55, 70, 85, 99, 100];

    /** Tout nouveau virement démarre à 25 %, quel que soit le chemin de création. */
    protected $attributes = [
        'progress' => self::INITIAL_PROGRESS,
    ];

    /** Nombre d'essais de code avant blocage temporaire. */
    public const CODE_MAX_ATTEMPTS = 5;
    /** Durée du blocage après trop d'essais (minutes). */
    public const CODE_LOCK_MINUTES = 15;

    /** Progression effective affichée au client : 100 % si validé, sinon le palier fixé par le conseiller. */
    public function progressValue(): int
    {
        return $this->status === self::STATUS_COMPLETED ? 100 : max(0, min(100, (int) $this->progress));
    }

    /**
     * Vrai tant que le client doit saisir le code de son conseiller pour que le virement avance.
     * Indépendant de la facture de frais : le virement garde sa barre qu'elle soit payée ou non.
     */
    public function isAwaitingCode(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_FEE_REQUIRED], true) && $this->code_required;
    }

    /** Palier (en %) que débloquera le prochain code, ou null si les 6 codes ont déjà été saisis. */
    public function nextStageTarget(): ?int
    {
        return self::CODE_STAGES[(int) $this->code_stage] ?? null;
    }

    public function isCodeLocked(): bool
    {
        return $this->code_locked_until && $this->code_locked_until->isFuture();
    }

    /** Code à 6 chiffres, généré côté serveur (CSPRNG). */
    public static function newUnlockCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** « Virement émis » / « Virement reçu » dans la langue demandée (langue courante par défaut). */
    public function typeLabel(?string $locale = null): string
    {
        return __($this->type === 'send' ? 'transfer.type_send' : 'transfer.type_receive', [], $locale);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'En attente',
            self::STATUS_COMPLETED    => 'Validé',
            self::STATUS_REJECTED     => 'Rejeté',
            self::STATUS_FEE_REQUIRED => 'Frais requis',
            default                   => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'orange',
            self::STATUS_COMPLETED    => 'green',
            self::STATUS_REJECTED     => 'red',
            self::STATUS_FEE_REQUIRED => 'blue',
            default                   => 'gray',
        };
    }

    public static function generateReference(): string
    {
        $year = now()->format('Y');
        $seq  = self::whereYear('created_at', $year)->count() + 1;
        return 'TRF-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
