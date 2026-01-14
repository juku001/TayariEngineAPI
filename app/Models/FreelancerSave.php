<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerSave extends Model
{
    protected $fillable = [
        'freelancer_id',
        'user_id'
    ];

    public function freelancer()
    {
        return $this->belongsTo(Freelancer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
