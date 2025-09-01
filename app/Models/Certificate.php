<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'enrollment_id',
        'certificate_type',
        'certificate_code',
        'certificate_score',
        'issued_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }



}
