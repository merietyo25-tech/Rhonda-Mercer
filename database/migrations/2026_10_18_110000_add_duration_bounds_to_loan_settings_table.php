<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Durée minimale et maximale (en mois) proposées au client dans sa demande de prêt
        Schema::table('loan_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_min')->default(6)->after('max_amount');
            $table->unsignedSmallInteger('duration_max')->default(84)->after('duration_min');
        });
    }

    public function down(): void
    {
        Schema::table('loan_settings', fn (Blueprint $t) => $t->dropColumn(['duration_min', 'duration_max']));
    }
};
