<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class DefaultBadges extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                "name" => "Quick Learner",
                "desc" => "Complete 3 lessons in one day"
            ],
            [
                "name" => "Consistent",
                "desc" => "Study 7 days in a row"
            ],
            [
                "name" => "Quiz Master",
                "desc" => "Score 100% on 5 quizzes"
            ],
            [
                "name" => "Social Learner",
                "desc" => "Share 3 certificates"
            ],
            [
                "name" => "Marathon",
                "desc" => "Complete a 20+ hour course"
            ]
        ];


        foreach ($badges as $badge) {
            Badge::create([
                'name' => $badge['name'],
                'slug' => Str::slug($badge['name']),
                'description' => $badge['desc']
            ]);
        }
    }
}
