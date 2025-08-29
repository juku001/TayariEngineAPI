<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class InitialSkill extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            "React",
            "Figma",
            "Node.js",
            "MongoDB",
            "SEO",
            "Social Media",
            "Content Strategy",
            "Python"
        ];


        foreach ($skills as $skill) {
            Skill::create([
                'name' => $skill,
                'slug' => Str::slug($skill)
            ]);
        }
    }
}
