<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admin')->updateOrInsert(
            ['email' => 'admin@gmail.com'],
            [
                'password'   => Hash::make('Admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
