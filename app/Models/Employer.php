<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'role'
    ];


    public function company(){
        return $this->belongsTo(Company::class);
    }


    public function user(){
        return $this->belongsTo(User::class);
    }
}
