<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{

    protected $fillable = [

        'title',
        'description',
        'city',
        'country',
        'type_id',
        'employer_id',
        'company_id',
        'category_id',
        'status',
        'salary_min',
        'salary_max',
        'currency',
        'experience_level',
        'education_level',
        'is_remote',
        'deadline',
        'views',
        'applications_count',
        'slug'
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function jobPostType()
    {
        return $this->belongsTo(JobPostType::class);
    }


    public function applications()
    {
        return $this->hasMany(JobPostApplication::class);
    }

    public function jobSkills()
    {
        return $this->hasMany(JobSkill::class);
    }

}
