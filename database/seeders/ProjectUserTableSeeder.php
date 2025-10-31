<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectUserTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('project_user')->insert([
            ['project_id' => 1, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 1, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 1, 'user_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 1, 'user_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 1, 'user_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 2, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 2, 'user_id' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
