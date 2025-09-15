<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'status',
        'progress',
        'enrollment_type',
        'team_id',
        'assigned_by'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function progresses()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
