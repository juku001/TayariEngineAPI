<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultCategories extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categores = [
            [
                "name" => 'Digital Marketing',
                "slug" => "Market"
            ],
            [
                "name" => 'Technology & Programming',
                "slug" => "Tech"
            ],
            [
                "name" => 'UI/UX Design',
                "slug" => 'Design'
            ],
            [
                "name" => 'Data Analytics',
                "slug" => 'Data'
            ]
        ];

        foreach ($categores as $key => $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => $cat['slug']
            ]);
        }
    }
}
