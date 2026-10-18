<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un prêt accepté crée un montant en attente sur le compte du client : on garde le lien avec la demande.
        Schema::table('pending_credits', function (Blueprint $table) {
            $table->foreignId('loan_application_id')->nullable()->after('movement_id')->constrained('loan_applications')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pending_credits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_application_id');
        });
    }
};
