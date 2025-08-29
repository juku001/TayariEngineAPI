<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder\Function_;

class JobPostType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description'
    ];


    public function jobPost(){
        return $this->hasMany(JobPost::class);
    }
}
