<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freelancer extends Model
{
    use HasFactory;

    protected $table = 'freelancers';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_available',
        'country',
        'region',
        'address',
        'start_price',
        'end_price',
        'rate',
        'currency',
        'responds_in',
        'rating',
        'reviews_count',
        'projects_completed',
        'success_rate',
    ];

    /**
     * Casts for attributes
     */
    protected $casts = [
        'start_price' => 'decimal:2',
        'end_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'reviews_count' => 'integer',
        'projects_completed' => 'integer',
    ];

    /**
     * Relationship: Freelancer belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Optional: Accessor for full price range string
     */
    public function getPriceRangeAttribute()
    {
        if ($this->start_price && $this->end_price) {
            return $this->currency . ' ' . $this->start_price . '-' . $this->end_price . '/' . $this->rate;
        }

        return null;
    }


    public function freelancerSaves()
    {
        return $this->hasMany(FreelancerSave::class);
    }
}
