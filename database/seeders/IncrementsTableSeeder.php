<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncrementsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('increments')->insert([
            [
                'name' => 'Sprint 1 - Authentification',
                'user_story_id' => 1,
                'image' => null,
                'file' => 'documentation_sprint1.pdf',
                'link' => 'https://github.com/project/releases/v1.0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maquettes interface',
                'user_story_id' => 2,
                'image' => 'maquette_inscription.png',
                'file' => null,
                'link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
