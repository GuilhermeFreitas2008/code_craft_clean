<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
        'C#', 
        'CSS', 
        'HTML', 
        'JavaScript', 
        'Python', 
        'Ruby', 
        'TypeScript'
        ];
    
        foreach ($topics as $topic) {
            \App\Models\Topic::create([
                'name' => $topic,
                'slug' => str()->slug($topic)
            ]);
        }
    }
}
