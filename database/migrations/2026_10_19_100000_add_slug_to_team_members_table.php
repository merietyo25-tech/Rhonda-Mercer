<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Identifiant des membres livrés avec le site : il permet d'afficher leur présentation dans la langue du visiteur (fichiers lang, team.php). */
    private const SLUGS = [
        'Claire Lambert'  => 'claire-lambert',
        'Markus Brandt'   => 'markus-brandt',
        'Sofia Marini'    => 'sofia-marini',
        'Julien Moreau'   => 'julien-moreau',
        'Anika Vogel'     => 'anika-vogel',
        'Pieter De Vries' => 'pieter-de-vries',
    ];

    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->unique()->after('id');
        });

        foreach (self::SLUGS as $name => $slug) {
            DB::table('team_members')->where('name', $name)->whereNull('slug')->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('team_members', fn (Blueprint $t) => $t->dropColumn('slug'));
    }
};
