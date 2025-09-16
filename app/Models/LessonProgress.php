<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'lesson_id',
        'is_completed',
        'completed_at'
    ];


    protected static function booted()
    {
        static::created(function ($progress) {
            event(new \App\Events\LessonCompleted($progress->user));
        });
    }



    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
