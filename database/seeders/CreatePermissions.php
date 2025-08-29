<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreatePermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                "name" => 'manage_users',
                'description' => 'Create, update,delete users'
            ],
            [
                'name' => 'manage_roles',
                'description' => 'Assign/revoke roles'
            ],
            [
                'name' => 'view_courses',
                'description' => 'Browse available courses'
            ],
            [
                'name' => 'create_courses',
                'description' => 'Create new courses'
            ],
            [
                'name' => 'update_courses',
                'description' => 'Update courses they own'
            ],
            [
                'name' => 'delete_courses',
                'description' => 'Delete courses they own'
            ],
            [
                'name' => 'upload_materials',
                'description' => 'Upload course materials'
            ],
            [
                'name' => 'view_learners',
                'description' => 'View enrolled learns in their courses'
            ],
            [
                'name' => 'post_jobs',
                'description' => 'Upload job listings'
            ],
            [
                'name' => 'manage_jobs',
                'description' => 'Update/delete or change status of a job listings'
            ],

            [
                'name' => 'view_applicants',
                'description' => 'View candidates for jobs they posted'
            ],
            [
                'name' => 'apply_jobs',
                'description' => 'Apply for jobs '
            ],
            [
                'name' => 'view_reports',
                'description' => 'See platform analytics and reports'
            ]
        ];

        foreach ($permissions as $key => $permission) {
            Permission::create([
                'name' => $permission['name'],
                'description' => $permission['description']
            ]);
        }
    }
}
