<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Apenas cria os roles
        $this->call(RoleSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(DifficultySeeder::class);
    }
}
