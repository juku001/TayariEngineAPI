<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration_min',
        'duration_max',
        'duration_unit',
        'employer_id',
        'company_id',
        'status',
        'salary_min',
        'salary_max',
        'currency',
        'deadline',
        'views',
        'proposal_count',
        'slug',
    ];


    public function projectSkills()
    {
        return $this->hasMany(ProjectSkill::class);
    }

    public function projectProposals()
    {
        return $this->hasMany(ProjectProposal::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
