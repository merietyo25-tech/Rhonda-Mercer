<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Équipe présentée sur la page « À propos » : personnages fictifs destinés à la présentation du site aux clients.
 *
 * Idempotent : un membre déjà présent (même nom) n'est jamais écrasé, ce qui préserve les modifications faites
 * depuis l'administration (Site → Équipe). Leur fonction et leur biographie s'affichent dans la langue du visiteur
 * (lang/*\/team.php) tant qu'elles n'ont pas été modifiées. Les photos sont livrées dans public/assets/images/team/ ; remplacez
 * ces personnages par les vrais membres de l'équipe avant la mise en service.
 */
class TeamMemberSeeder extends Seeder
{
    public const MEMBERS = [
        [
            'slug'       => 'claire-lambert',
            'name'       => 'Claire Lambert',
            'title'      => 'Directrice générale',
            'bio'        => "Claire pilote la stratégie et le développement de la banque. Elle a construit sa carrière dans la banque de détail et les services de paiement, avec une conviction : une banque doit être simple, transparente et disponible à toute heure.\nSous sa direction, la banque a fait de l’ouverture de compte 100 % en ligne, de la vérification d’identité sécurisée et du suivi des virements en temps réel les piliers de son offre.",
            'photo_path' => 'assets/images/team/claire-lambert.jpg',
            'is_leader'  => true,
            'sort_order' => 1,
        ],
        [
            'slug'       => 'markus-brandt',
            'name'       => 'Markus Brandt',
            'title'      => 'Directeur des opérations',
            'bio'        => 'Responsable de la validation des virements, de la conformité des opérations et de la qualité de service rendue aux clients au quotidien.',
            'photo_path' => 'assets/images/team/markus-brandt.jpg',
            'is_leader'  => false,
            'sort_order' => 2,
        ],
        [
            'slug'       => 'sofia-marini',
            'name'       => 'Sofia Marini',
            'title'      => 'Responsable relations clientèle',
            'bio'        => 'Elle encadre l’équipe de conseillers et veille à ce que chaque client soit accompagné dans sa langue, de l’ouverture du compte à la gestion de sa carte.',
            'photo_path' => 'assets/images/team/sofia-marini.jpg',
            'is_leader'  => false,
            'sort_order' => 3,
        ],
        [
            'slug'       => 'julien-moreau',
            'name'       => 'Julien Moreau',
            'title'      => 'Directeur des risques et de la conformité',
            'bio'        => 'Garant de la sécurité des données, de la lutte contre la fraude et du respect des règles applicables aux établissements de paiement.',
            'photo_path' => 'assets/images/team/julien-moreau.jpg',
            'is_leader'  => false,
            'sort_order' => 4,
        ],
        [
            'slug'       => 'anika-vogel',
            'name'       => 'Anika Vogel',
            'title'      => 'Directrice du digital',
            'bio'        => 'Elle conçoit l’application mobile et l’espace client : parcours d’inscription, cartes, virements SEPA et en temps réel, notifications instantanées.',
            'photo_path' => 'assets/images/team/anika-vogel.jpg',
            'is_leader'  => false,
            'sort_order' => 5,
        ],
        [
            'slug'       => 'pieter-de-vries',
            'name'       => 'Pieter De Vries',
            'title'      => 'Responsable financier',
            'bio'        => 'En charge du pilotage financier, de la facturation des frais et de la transparence des conditions tarifaires.',
            'photo_path' => 'assets/images/team/pieter-de-vries.jpg',
            'is_leader'  => false,
            'sort_order' => 6,
        ],
    ];

    public function run(): void
    {
        foreach (self::MEMBERS as $member) {
            $row = TeamMember::firstOrCreate(['name' => $member['name']], $member + ['is_visible' => true]);

            // Membre créé avant l'ajout des traductions : on lui donne son identifiant sans toucher à ses textes
            if (! $row->slug) {
                $row->update(['slug' => $member['slug']]);
            }
        }
    }
}
