<?php

namespace Database\Seeders;

use App\Models\AptitudeOption;
use App\Models\AptitudeQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultAptitude extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aptitudes = [



            [
                'title' => 'What interests you most?',
                'sub_title' => "Select all areas you'd like to learn about",
                'qn_type' => 'multiple',
                'options' => [
                    [
                        'icon' => 'fa fa-business',
                        'title' => 'Technology & Programming',
                        'key' => 'Tech',
                        'color' => '#ff0000'
                    ],
                    [
                        'icon' => 'fa fa-stocks',
                        'title' => 'Digital Marketing',
                        'key' => 'Marketing',
                        'color' => '#ff2e20'
                    ],
                    [
                        'icon' => 'fa fa-interface',
                        'title' => 'UI/UX Design',
                        'key' => 'Design',
                        'color' => '#ff532a'
                    ],
                    [
                        'icon' => 'fa fa-data',
                        'title' => 'Data Analytics',
                        'key' => 'Data',
                        'color' => '#ff532a',
                    ]
                ]
            ],
            [
                'title' => "What's your current skill level?",
                'sub_title' => "Be honest - this helps us recommend the right courses",
                'qn_type' => 'single',
                'options' => [
                    [
                        'title' => 'Beginner',
                        'key' => 'beginner',
                        'sub_title' => 'Just getting started'
                    ],
                    [
                        'title' => 'Intermediate',
                        'key' => 'intermediate',
                        'sub_title' => 'Some experience, ready to go'
                    ],
                    [
                        'title' => 'Difficult',
                        'key' => 'difficult',
                        'sub_title' => 'Looking to master specialized skills'
                    ]
                ]
            ],
            [
                'title' => 'What type of opportunities interest you?',
                'sub_title' => "Select all that apply",
                'qn_type' => 'single',
                'options' => [
                    [
                        'icon' => 'fa fa-business',
                        'title' => 'Full-time Employment',
                        'key' => 'full-time',
                        'color' => '#ff0000'
                    ],
                    [
                        'icon' => 'fa fa-stocks',
                        'title' => 'Freelance Projects',
                        'color' => '#ff2e20',
                        'key' => 'project'
                    ],
                    [
                        'icon' => 'fa fa-interface',
                        'title' => 'Internship',
                        'color' => '#ff532a',
                        'key' => 'internship'
                    ]
                ]
            ]
        ];


        foreach ($aptitudes as $aptitude) {
            $aptQn = AptitudeQuestion::create([
                'title' => $aptitude['title'],
                'sub_title' => $aptitude['sub_title'],
                'qn_type' => $aptitude['qn_type'],
            ]);

            foreach ($aptitude['options'] as $aptOption) {
                AptitudeOption::create([
                    'title' => $aptOption['title'],
                    'key' => $aptOption['key'],
                    'sub_title' => $aptOption['sub_title'] ?? null,
                    'color' => $aptOption['color'] ?? null,
                    'icon' => $aptOption['icon'] ?? null,
                    'question_id' => $aptQn->id, // also don’t forget to link option to question
                ]);

            }
        }
    }
}
