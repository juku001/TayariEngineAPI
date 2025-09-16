<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{

    protected $fillable = [
        'enrollment_id',
        'quiz_id',
        'score',
        'is_passed'
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
