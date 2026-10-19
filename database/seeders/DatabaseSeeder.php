<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // En premier : la ligne site_contacts doit exister avant tout appel a
        // site_name() declenche par le chargement des fichiers de lang/.
        $this->call(SiteContactSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(ExceptionalPermissionsSeeder::class);
        
        $this->call(CurrencySeeder::class);

        // Équipe présentée sur la page « À propos » (personnages fictifs de présentation, modifiables depuis Site → Équipe)
        $this->call(TeamMemberSeeder::class);
    }
}
