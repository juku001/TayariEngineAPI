<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'criteria'
    ];

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }
}
