<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const COLUMNS = ['otp_clients_enabled', 'otp_staff_enabled', 'activation_mail_enabled'];

    /**
     * Le code OTP à la connexion et l'e-mail d'activation (lien) ne sont plus imposés : ils sont désactivés par défaut
     * et seul un administrateur ou le super-administrateur peut les activer (Site → Coordonnées → « Connexion et e-mails »).
     */
    public function up(): void
    {
        foreach (self::COLUMNS as $col) {
            DB::statement("ALTER TABLE site_contacts ALTER COLUMN {$col} SET DEFAULT 0");
        }

        DB::table('site_contacts')->update(array_fill_keys(self::COLUMNS, false));
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $col) {
            DB::statement("ALTER TABLE site_contacts ALTER COLUMN {$col} SET DEFAULT 1");
        }
    }
};
