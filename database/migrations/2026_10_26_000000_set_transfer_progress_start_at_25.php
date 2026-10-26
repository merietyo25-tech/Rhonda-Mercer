<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Le pourcentage de départ d'un virement est toujours 25 % : valeur par défaut de la colonne, et remise à 25 %
     * des virements encore au tout début (aucun code saisi) qui seraient restés à 0 % ou à l'ancien départ de 50 %.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE transfers ALTER COLUMN progress SET DEFAULT 25');

        DB::table('transfers')
            ->whereIn('status', ['pending', 'fee_required'])
            ->where('code_stage', 0)
            ->whereIn('progress', [0, 50])
            ->update(['progress' => 25]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transfers ALTER COLUMN progress SET DEFAULT 50');
    }
};
