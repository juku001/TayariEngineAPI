<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_id'
    ];


    public function jobSkills()
    {
        return $this->hasMany(JobSkill::class);
    }


    public function courses()
    {
        return $this->hasMany(CourseSkill::class);
    }


    public function learners()
    {
        return $this->hasMany(LearnerSkill::class, );
    }


}
