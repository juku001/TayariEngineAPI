<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AptitudeOption extends Model
{
    protected $filalble = [
        'question_id',
        'title',
        'sub_title',
        'icon',
        'key',
        'color'
    ];


    public function question()
    {
        return $this->belongsTo(AptitudeQuestion::class, 'question_id');
    }
}
