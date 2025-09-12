<?php

namespace Database\Seeders;

use App\Models\JobPostType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class DefaultJobTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobPostTypes = [
            [
                'name' => 'Full-Time',
                'desc' => 'Full time Employment'
            ],
            [
                'name' => 'Project',
                'desc' => 'Freelance Projects'
            ],
            [
                'name' => 'Internship',
                'desc' => 'Just for internship skills'
            ],
            [
                'name' => 'Flexible Virtual Hire',
                'desc' => 'For online virtual projects'
            ],
            [
                'name' => 'Part Time',
                'desc' => 'Short time Employment'
            ]
        ];

        foreach ($jobPostTypes as $jbT) {
            JobPostType::create([
                'name' => $jbT['name'],
                'slug' => Str::slug($jbT['name']),
                'description' => $jbT['desc']
            ]);
        }
    }
}
