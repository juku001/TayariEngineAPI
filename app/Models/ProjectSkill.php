<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSkill extends Model
{
    protected $fillable = [
        'project_id',
        'skill_id'
    ];

    public function projects()
    {
        return $this->belongsTo(Project::class);
    }
    public function skills()
    {
        return $this->belongsTo(Skill::class);
    }
}
