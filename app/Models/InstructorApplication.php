<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorApplication extends Model
{
    protected $fillable = [
        'name',
        'email',
        'user_id',
        'phone_number',
        'experience',
        'profession',
        'interests',
        'additional_info',
        'status',
        'admin_notes'
    ];
}
