<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductBacklogsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_backlogs')->insert([
            ['project_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
