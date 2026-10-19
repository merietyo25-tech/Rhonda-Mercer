<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Permissions accordables au cas par cas à un admin classique, pour lui donner
 * accès à une zone habituellement réservée au super-admin sans le promouvoir.
 * Séparé de RolesAndPermissionsSeeder pour pouvoir être rejoué sans risque
 * (idempotent, ne touche à aucun compte).
 */
class ExceptionalPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage-site-contacts'          => 'Coordonnées du site',
        'manage-social-links'           => 'Réseaux sociaux',
        'manage-languages'              => 'Langues',
        'manage-currencies'             => 'Devises',
        'manage-team'                   => 'Équipe (À propos)',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
