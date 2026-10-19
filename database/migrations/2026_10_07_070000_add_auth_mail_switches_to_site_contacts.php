<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Interrupteurs : OTP de connexion (clients / personnel) et e-mail d'activation du compte. Tout est désactivé par défaut : seul un administrateur ou le super-administrateur peut les activer. */
    public function up(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->boolean('otp_clients_enabled')->default(true);
            $table->boolean('otp_staff_enabled')->default(true);
            $table->boolean('activation_mail_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->dropColumn(['otp_clients_enabled', 'otp_staff_enabled', 'activation_mail_enabled']);
        });
    }
};
