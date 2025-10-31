<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessagesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('messages')->insert([
            [
                'content' => 'Bonjour tout le monde ! Bienvenue sur le projet Application E-commerce.',
                'chat_project_id' => 1,
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'Quelqu\'un a commencé à travailler sur l\'authentification ?',
                'chat_project_id' => 1,
                'user_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'content' => 'Oui, je m\'en occupe. Je devrais finir demain.',
                'chat_project_id' => 1,
                'user_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
