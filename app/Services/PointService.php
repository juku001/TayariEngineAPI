<?php

namespace App\Services;

use App\Models\User;
use App\Models\LearnerPoint;

class PointService
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Add points to user
     */
    public function addPoints(int $points, string $reason = null)
    {
        return LearnerPoint::create([
            'user_id' => $this->user->id,
            'points'  => $points, 
            'reason' => $reason, // only if you add this column later
        ]);
    }

    /**
     * Award points when user finishes a lesson
     */
    public function lessonCompleted()
    {
        return $this->addPoints(10, 'Completion of lesson');
    }

    /**
     * Award points when user answers quiz correctly
     */
    public function quizCorrect()
    {
        return $this->addPoints(5, 'Getting a quiz correctly.');
    }

    /**
     * Get total points of user
     */
    public function totalPoints()
    {
        return $this->user->points()->sum('points');
    }
}
