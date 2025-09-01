<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProposal extends Model
{
    protected $fillable = [
        'project_id',
        'freelancer_id',
        'amount',
        'experience',
        'experience_unit',
        'message',
        'status'
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
