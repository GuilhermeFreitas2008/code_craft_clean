<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Difficulty;

class DifficultySeeder extends Seeder
{
    public function run(): void
    {
        $difficulties = [
            'Beginner',
            'Intermediate',
            'Advanced'
        ];

        foreach ($difficulties as $difficulty) {
            Difficulty::create([
                'name' => $difficulty
            ]);
        }
    }
}
