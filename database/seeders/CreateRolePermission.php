<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class CreateRolePermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePermissions = [
            'super_admin' => Permission::pluck('id')->toArray(),
            'admin' => ['manage_users', 'manage_roles', 'view_reports'],
            'learner' => ['view_courses', 'apply_jobs'],
            'employee' => ['view_courses'],
            'instructor' => [
                'view_courses',
                'create_courses',
                'update_courses',
                'delete_courses',
                'upload_materials',
                'view_learners',
                'apply_jobs'
            ],
            'employer' => [
                'post_jobs',
                'manage_jobs',
                'view_applicants'
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                continue;
            }
            if ($roleName === 'super_admin') {
                $role->permissions()->sync(Permission::pluck('id')->toArray());
            } else {
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
