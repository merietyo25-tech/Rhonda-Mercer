<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Validation du compte à l'inscription par code OTP (saisi dans l'application) à la place du lien envoyé par e-mail.
        // Désactivé par défaut : seul un administrateur ou le super-administrateur peut l'activer.
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->boolean('activation_otp_enabled')->default(false)->after('activation_mail_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('site_contacts', fn (Blueprint $t) => $t->dropColumn('activation_otp_enabled'));
    }
};
