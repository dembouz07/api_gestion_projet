<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('projects')->insert([
            [
                'name' => 'Application E-commerce',
                'description' => 'Développement d\'une plateforme e-commerce complète',
                'start_date' => '2025-01-15',
                'deadline' => '2025-06-15',
                'status' => 'active',
                'project_manager_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Système de Gestion de Contenu',
                'description' => 'CMS pour la gestion de contenu d\'entreprise',
                'start_date' => '2025-02-01',
                'deadline' => '2025-05-01',
                'status' => 'pending',
                'project_manager_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
