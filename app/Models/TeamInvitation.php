<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
    protected $fillable = [
        'company_id',
        'team_id',
        'email',
        'token',
        'invited_by',
        'status'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
