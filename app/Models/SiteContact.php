<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContact extends Model
{
    protected $fillable = [
        'name',
        'logo_light_path',
        'logo_dark_path',
        'email_signature_path',
        'address_1',
        'address_2',
        'address_3',
        'phone_1',
        'phone_2',
        'email',
        'default_bic',
        'iban_bank_code',
        'payment_iban',
        'payment_bic',
        'payment_holder',
        'payment_type',
        'otp_clients_enabled',
        'otp_staff_enabled',
        'activation_mail_enabled',
        'activation_otp_enabled',
        'kyc_steps',
        'whatsapp_number',
        'whatsapp_enabled',
    ];

    protected $casts = [
        'whatsapp_enabled' => 'boolean',
        'otp_clients_enabled'     => 'boolean',
        'otp_staff_enabled'       => 'boolean',
        'activation_mail_enabled' => 'boolean',
        'activation_otp_enabled'  => 'boolean',
    ];

    /** Validation du compte à l'inscription : « otp » (code saisi), « link » (lien par e-mail) ou « none » (activation automatique). */
    public function activationMethod(): string
    {
        return $this->activation_otp_enabled ? 'otp' : ($this->activation_mail_enabled ? 'link' : 'none');
    }

    /**
     * Enregistrement singleton des coordonnées du site.
     */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    /**
     * Lien wa.me si l'assistant WhatsApp est configuré et actif, sinon null.
     */
    public function whatsappUrl(): ?string
    {
        if (! $this->whatsapp_enabled || ! $this->whatsapp_number) {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', $this->whatsapp_number);

        return $digits ? "https://wa.me/{$digits}" : null;
    }
}
