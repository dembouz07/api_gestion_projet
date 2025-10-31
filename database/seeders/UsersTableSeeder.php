<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Project Manager',
                'email' => 'pm@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'projectManager',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Product Owner',
                'email' => 'po@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'productOwner',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Scrum Master',
                'email' => 'sm@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'scrumMaster',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Member 1',
                'email' => 'team1@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'teamMember',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Member 2',
                'email' => 'team2@example.com',
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'teamMember',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
