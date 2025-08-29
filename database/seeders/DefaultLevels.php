<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class DefaultLevels extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Beginner',
                'desc' => 'Just getting started.'
            ],
            [
                'name' => 'Intermediate',
                'desc' => 'Some experience, ready to grow.'
            ],
            [
                'name' => 'Difficult',
                'desc' => 'Looking to master specialized Skills'
            ]
        ];

        foreach ($levels as $level) {
            Level::create([
                'name' => $level['name'],
                'slug' => Str::slug($level['name']),
                'description' => $level['desc']
            ]);
        }
    }
}
