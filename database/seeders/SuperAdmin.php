<?php

namespace Database\Seeders;

use App\Helpers\CustomFunctions;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $pass = 'admin123';
        $userAdmin = [
            'first_name' => 'Tayari',
            'last_name' => 'Admin',
            'email' => 'admin@tayari.com',
            'password' => Hash::make($pass),
            'email_verified_at' => Carbon::now(),
            'provider' => 'email'
        ];
        $user = User::create($userAdmin);
        $roleId = CustomFunctions::getIdFromUserRole('super_admin');


        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $roleId
        ]);
    }
}
