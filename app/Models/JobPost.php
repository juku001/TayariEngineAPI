<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;

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

    protected $appends = ['is_saved'];

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
        return $this->belongsTo(JobPostType::class, 'type_id');
    }

    public function applications()
    {
        return $this->hasMany(JobPostApplication::class);
    }

    public function jobSkills()
    {
        return $this->hasMany(JobSkill::class);
    }

    public function savedJobs()
    {
        return $this->hasMany(SavedJob::class, 'job_id');
    }




    public function getIsSavedAttribute()
    {
        $user = null;
        $token = Request::bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable; // authenticated user
            }
        }
        if (!$user) {
            return false;
        }
        return $this->savedJobs()
            ->where('user_id', $user->id)
            ->exists();
    }

}
