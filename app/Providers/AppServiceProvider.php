<?php

namespace App\Providers;

use App\Events\CertificateShared;
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\QuizAttempted;
use App\Listeners\AwardBadges;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Event::listen(
            [LessonCompleted::class, CourseCompleted::class, CertificateShared::class, QuizAttempted::class],
            AwardBadges::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
