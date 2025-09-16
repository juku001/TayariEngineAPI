<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserBadge;
use Carbon\Carbon;

class BadgeService
{
    public function checkAndAward(User $user)
    {
        $badgesAwarded = [];


        $lessonsToday = $user->lessonProgress()
            ->whereDate('created_at', Carbon::today())
            ->count();
        if ($lessonsToday >= 3) {
            $this->awardBadge($user, 'quick-learner');
            $badgesAwarded[] = 'Quick Learner';
        }


        $days = $user->lessonProgress()
            ->selectRaw('DATE(created_at) as day')
            ->distinct()
            ->orderBy('day', 'desc')
            ->take(7)
            ->pluck('day')
            ->toArray();

        if (count($days) == 7 && $this->areDaysConsecutive($days)) {
            $this->awardBadge($user, 'consistent');
            $badgesAwarded[] = 'Consistent';
        }



        $perfectQuizzes = QuizAttempt::whereHas('enrollment', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('score', 100)
            ->count();


        if ($perfectQuizzes >= 5) {
            $this->awardBadge($user, 'quiz-master');
            $badgesAwarded[] = 'Quiz Master';
        }


        $shares = $user->certificateShares()->count();
        if ($shares >= 3) {
            $this->awardBadge($user, 'social-learner');
            $badgesAwarded[] = 'Social Learner';
        }


        $longCourse = $user->enrollments()
            ->whereHas('course', fn($q) => $q->where('duration', '>=', 1200)) // 1200 mins = 20 hours
            ->where('status', 'completed')
            ->exists();
        if ($longCourse) {
            $this->awardBadge($user, 'marathon');
            $badgesAwarded[] = 'Marathon';
        }

        return $badgesAwarded;
    }

    protected function awardBadge(User $user, string $badgeCode)
    {
        $badge = Badge::where('slug', $badgeCode)->first();
        if (!$badge) {
            return;
        }


        UserBadge::firstOrCreate([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    protected function areDaysConsecutive(array $days)
    {
        $dates = collect($days)->map(fn($d) => Carbon::parse($d))->sort()->values();

        for ($i = 1; $i < $dates->count(); $i++) {
            if ($dates[$i]->diffInDays($dates[$i - 1]) !== 1) {
                return false;
            }
        }

        return true;
    }
}
