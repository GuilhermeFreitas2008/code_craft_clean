<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primeiro cria os roles
        $this->call(RoleSeeder::class);

        // Depois cria um utilizador de teste
        User::create([
            'username' => 'TestUser',
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password'),
            'role_id' => 1, // admin
            'slug' => Str::slug('TestUser'),
        ]);
    }
}
