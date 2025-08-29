<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AptitudeQuestion extends Model
{
    protected $fillable = [
        'title',
        'sub_title',
        'qn_type'
    ];

    public function options(){
        return $this->hasMany(AptitudeOption::class, 'question_id');
    }
}
