<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class   Course extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'description',
        'objectives',
        'requirements',
        'language',
        'category_id',
        'level_id',
        'cover_image',
        'cover_video',
        'price',
        'is_free',
        'is_featured',
        'certificate_type',
        'status',
        'tags',
        'avg_rating',
        'instructor',
        'created_by',
    ];


    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function instructorUser()
    {
        return $this->belongsTo(User::class, 'instructor'); // 'instructor' is the FK column
    }


    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'course_skills', 'course_id', 'skill_id');
    }
}
