<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SprintsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('sprints')->insert([
            [
                'number' => 1,
                'start_date' => '2025-01-15',
                'deadline' => '2025-01-29',
                'objective' => 'Mise en place de l\'architecture et authentification',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 2,
                'start_date' => '2025-01-30',
                'deadline' => '2025-02-13',
                'objective' => 'Développement du catalogue produits',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 1,
                'start_date' => '2025-02-01',
                'deadline' => '2025-02-15',
                'objective' => 'Interface administrateur et gestion des utilisateurs',
                'project_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
