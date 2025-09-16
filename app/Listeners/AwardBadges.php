<?php

namespace App\Listeners;

use App\Events\LessonCompleted;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AwardBadges
{
    /**
     * Create the event listener.
     */
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Handle the event.
     */
    public function handle($event)
    {
        $this->badgeService->checkAndAward($event->user);
    }
}
