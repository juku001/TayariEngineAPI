<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateRoles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'learner',
            'instructor',
            'employer',
            'employee'
        ];


        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

    }
}
