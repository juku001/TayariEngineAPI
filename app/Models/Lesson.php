<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'description',
        'course_id',
        'content_type',
        'content_url',
        'content_text',
        'duration',
        'order',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }


    public function course()
    {
        return $this->belongsTo(Course::class);
    }


    public function progresses()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
