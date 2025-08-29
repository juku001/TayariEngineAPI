<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'website',
        'logo',
        'size_range'
    ];

    public function employers(){
        return $this->hasMany(Employer::class);
    }
    
}
