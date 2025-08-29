<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPostApplication extends Model
{
    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }
}
