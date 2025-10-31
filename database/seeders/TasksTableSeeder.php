<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasksTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tasks')->insert([
            [
                'title' => 'Créer la page de login',
                'description' => 'Développer l\'interface de connexion avec validation',
                'user_story_id' => 1,
                'assigned_to' => 5,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Implémenter l\'authentification',
                'description' => 'Développer le système d\'authentification avec Laravel Sanctum',
                'user_story_id' => 1,
                'assigned_to' => 6,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Créer le formulaire d\'inscription',
                'description' => 'Développer l\'interface d\'inscription avec validation des données',
                'user_story_id' => 2,
                'assigned_to' => 5,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Page de modification du profil',
                'description' => 'Créer l\'interface pour modifier les informations du profil',
                'user_story_id' => 3,
                'assigned_to' => 6,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
