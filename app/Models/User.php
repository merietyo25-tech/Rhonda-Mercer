<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements \Illuminate\Contracts\Translation\HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'type', 'gender', 'created_by', 'invitation_token',
        'phone', 'address', 'country', 'birth_date', 'id_type', 'id_number', 'date_delivre', 'tax_number', 'activity', 'currency', 'locale', 'balance',
        'bank_account', 'bic',
        'is_blocked', 'unblock_token', 'unblock_token_expires_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'        => 'datetime',
        'birth_date'               => 'date',
        'date_delivre'             => 'date',
        'balance'                  => 'decimal:2',
        'is_blocked'               => 'boolean',
        'unblock_token_expires_at' => 'datetime',
        'bank_account'             => \App\Casts\SafeEncrypted::class,
        'bic'                      => \App\Casts\SafeEncrypted::class,
        'id_number'                => \App\Casts\SafeEncrypted::class,
        'tax_number'               => \App\Casts\SafeEncrypted::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->uuid = $user->uuid ?? (string) \Illuminate\Support\Str::uuid();
        });
    }

    // Clé utilisée pour le routage HTTP ({user}) — non devinable, distincte de l'id interne
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** Langue des e-mails et notifications : celle choisie à la création du compte (puis modifiable dans le profil). */
    public function preferredLocale(): string
    {
        return $this->locale ?: 'fr';
    }

    // Le personnel (admin/super-admin) reçoit un email de réinitialisation dédié,
    // avec un lien vers l'espace staff plutôt que l'espace client.
    public function sendPasswordResetNotification($token): void
    {
        if ($this->type === 'staff') {
            $this->notify(new \App\Notifications\StaffResetPasswordNotification($token));
            return;
        }

        parent::sendPasswordResetNotification($token);
    }

    // Messages de support (côté client)
    public function supportMessages()
    {
        return $this->hasMany(SupportMessage::class, 'client_id');
    }

    // Notifications admin
    public function adminNotifications()
    {
        return $this->hasMany(AdminNotification::class, 'admin_id');
    }

    // Vérification d'identité (KYC) — une par client
    public function kycAnswers()
    {
        return $this->hasMany(KycAnswer::class);
    }

    public function kycVerification()
    {
        return $this->hasOne(KycVerification::class);
    }

    // Coordonnées bancaires attribuées par un admin
    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }

    // Carte attribuée par un admin
    public function card()
    {
        return $this->hasOne(Card::class);
    }

    /** Montants en attente de validation (solde en attente). */
    public function pendingCredits()
    {
        return $this->hasMany(PendingCredit::class);
    }

    /** Somme des montants en attente : elle rejoint le solde du compte quand l'administration les valide. */
    public function pendingBalance(): float
    {
        return round((float) $this->pendingCredits()->where('status', PendingCredit::PENDING)->sum('amount'), 2);
    }

    /** Compte bancaire (ou carte) bloqué par l'administration. */
    public function hasBlockedAccount(): bool
    {
        return $this->bankAccount?->status === \App\Models\BankAccount::STATUS_BLOCKED
            || $this->card?->status === \App\Models\Card::STATUS_BLOCKED;
    }
}
