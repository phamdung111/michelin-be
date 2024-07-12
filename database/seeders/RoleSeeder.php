<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'password' => bcrypt('admin@123'),
            'role_id'=> 1,
            'email'=> 'admin@michelin.com',
            'avatar'=> '/user-placeholder.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
