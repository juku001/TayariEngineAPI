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
}
