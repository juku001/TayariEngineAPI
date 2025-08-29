<?php

namespace App\Helpers;

use App\Models\LearnerAptitudeResult;

class JobMatchingHelper
{
    protected $jobPost;
    protected $user;
    protected $aptitude;

    public function __construct($jobPost, $user)
    {
        $this->jobPost = $jobPost;
        $this->user = $user;
        $this->aptitude = LearnerAptitudeResult::where('user_id', $user->id)->first();
    }

    public function getMatchingStatus()
    {
        if (!$this->aptitude) {
            return [
                'status' => $this->getMatchLabel(0),
                'value' => 0
            ];
        }

     
        $skillScore = $this->calculateSkillScore();

        
        $interests = json_decode($this->aptitude->interests, true) ?? [];
        $interestScore = in_array($this->jobPost->category_id, $interests) ? 100 : 0;

       
        $careerGoals = json_decode($this->aptitude->career_goals, true) ?? [];
        $goalScore = in_array($this->jobPost->job_type, $careerGoals) ? 100 : 0;

      
        $finalScore = ($skillScore * 0.6) + ($interestScore * 0.2) + ($goalScore * 0.2);

        return [
            'status' => $this->getMatchLabel($finalScore),
            'value' => round($finalScore, 2)
        ];
    }

    private function calculateSkillScore()
    {
        $jobSkills = $this->jobPost->skills->pluck('name')->toArray(); // assuming relation
        $userSkillLevel = strtolower($this->aptitude->skill_level); // beginner/intermediate/advanced

        // map levels to numeric scores
        $map = [
            'beginner' => 30,
            'intermediate' => 60,
            'advanced' => 90,
        ];

        if (empty($jobSkills) || !isset($map[$userSkillLevel])) {
            return 0;
        }

        // simple: all skills get the same score from learner’s level
        return $map[$userSkillLevel];
    }

    private function getMatchLabel($score)
    {
        if ($score >= 80)
            return 'Great Match';
        if ($score >= 60)
            return 'Good Match';
        if ($score >= 40)
            return 'Partial Match';
        return 'Not a Fit';
    }
}
