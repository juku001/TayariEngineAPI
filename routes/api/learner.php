<?php

use App\Http\Controllers\AptitudeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateShareController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseRatingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LearnerSkillController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PopularController;
use App\Http\Controllers\PopularCourseController;
use App\Http\Controllers\QuizController;

Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
    Route::get('/aptitude/start', [AptitudeController::class, 'index']);
    Route::post('/aptitude/submit', [AptitudeController::class, 'store']);
    Route::get('dashboard/learner', [DashboardController::class, 'learner']);
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::post('/certificates/share', [CertificateShareController::class, 'share']);
    Route::get('/certificates/{id}', [CertificateController::class, 'show']);
    Route::get('/module/{id}/quiz',[QuizController::class, 'index']);
    Route::post('/quiz/submit/{id}', [QuizController::class, 'storeAttempt']);
    Route::post('/quiz/check/{id}', [QuizController::class, 'checkAnswer']);
    Route::post('/complete/lesson/{id}', [LessonController::class, 'completeLesson']);
    Route::get('/learning/progress', [LessonController::class, 'progress']);
    Route::get('/learner/skills', [LearnerSkillController::class, 'index']);
    Route::get('/learner/points', [LearnerSkillController::class, 'points']);
    

});





Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{id}', [CourseController::class, 'show'])->whereNumber('id');
    Route::get('/featured',[PopularCourseController::class, 'index']);
    Route::patch('/{id}/featured',[PopularCourseController::class, 'update'])->middleware(['auth:sanctum', 'user.type:super_admin'])->whereNumber('id');
    
    Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
        
        Route::post('/{id}/enroll', [EnrollmentController::class, 'store']);
        Route::post('/{id}/drop', [EnrollmentController::class, 'destroy']);
        Route::get('/{id}/progress', [EnrollmentController::class, 'progress']);
        Route::get('/{id}/ratings',[CourseRatingController::class, 'index']);
        Route::post('/{id}/ratings',[CourseRatingController::class, 'store']);
    });
});

Route::get('/popular/categories', [PopularController::class, 'category']);
Route::get('/popular/courses', [PopularController::class, 'courses']);

