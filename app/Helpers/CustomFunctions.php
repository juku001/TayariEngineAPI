<?php

namespace App\Helpers;

use App\Models\Role;

class CustomFunctions
{


    public static function getIdFromUserRole(string $role)
    {
        return Role::where('name', $role)->first()->id;
    }





}