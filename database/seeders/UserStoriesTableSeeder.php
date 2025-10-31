<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('user_stories')->insert([
            [
                'title' => 'Authentification utilisateur',
                'description' => 'En tant qu\'utilisateur, je veux pouvoir me connecter pour accéder à mon compte',
                'sprint_id' => 1,
                'product_backlog_id' => 1,
                'status' => 'completed',
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Création de compte',
                'description' => 'En tant que visiteur, je veux pouvoir créer un compte pour devenir client',
                'sprint_id' => 1,
                'product_backlog_id' => 1,
                'status' => 'completed',
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Gestion du profil',
                'description' => 'En tant qu\'utilisateur, je veux modifier mon profil pour mettre à jour mes informations',
                'sprint_id' => 1,
                'product_backlog_id' => 1,
                'status' => 'active',
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Recherche de produits',
                'description' => 'En tant que client, je veux rechercher des produits pour trouver rapidement ce que je cherche',
                'sprint_id' => 2,
                'product_backlog_id' => 1,
                'status' => 'pending',
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
