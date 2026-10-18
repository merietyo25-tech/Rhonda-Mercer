<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plafond maximal de la carte, défini par un administrateur : le client règle son plafond de dépenses dans cette limite.
        Schema::table('cards', function (Blueprint $table) {
            $table->unsignedInteger('spending_cap')->default(5000)->after('spending_limit');
        });
    }

    public function down(): void
    {
        Schema::table('cards', fn (Blueprint $t) => $t->dropColumn('spending_cap'));
    }
};
