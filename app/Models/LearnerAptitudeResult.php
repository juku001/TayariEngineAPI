<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearnerAptitudeResult extends Model
{
    protected $fillable = [
        'user_id',
        'interests',
        'skill_level',
        'career_goals',
        'recommended_courses',
        'logical_score',
        'quantitative_score',
        'verbal_score',
        'total_score',
        'answers'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
