<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSkill extends Model
{
    protected $fillable = [
        'course_id',
        'skill_id'
    ];

    public function courses()
    {
        return $this->belongsTo(Course::class);
    }

    public function skills()
    {
        return $this->belongsTo(Skill::class);
    }
}
