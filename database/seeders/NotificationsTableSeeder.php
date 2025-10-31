<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('notifications')->insert([
            [
                'object' => 'Nouvelle tâche assignée',
                'message' => 'La tâche "Créer la page de login" vous a été assignée',
                'user_id' => 5,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'object' => 'Tâche terminée',
                'message' => 'La tâche "Implémenter l\'authentification" a été marquée comme terminée',
                'user_id' => 2,
                'is_read' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'object' => 'Nouveau message',
                'message' => 'Nouveau message dans le chat du projet Application E-commerce',
                'user_id' => 3,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
