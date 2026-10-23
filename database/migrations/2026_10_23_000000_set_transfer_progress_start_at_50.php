<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Le pourcentage de départ d'un virement est toujours 50 % : valeur par défaut de la colonne, et correction
     * des virements encore au tout début (aucun code saisi) qui seraient restés à 0 %.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE transfers ALTER COLUMN progress SET DEFAULT 50');

        DB::table('transfers')
            ->whereIn('status', ['pending', 'fee_required'])
            ->where('code_stage', 0)
            ->where('progress', '<', 50)
            ->update(['progress' => 50]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transfers ALTER COLUMN progress SET DEFAULT 0');
    }
};
