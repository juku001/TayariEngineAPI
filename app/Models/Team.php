<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'company_id'
    ];

    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
