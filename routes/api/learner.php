<?php

use App\Http\Controllers\AptitudeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuizController;

Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
    Route::get('/aptitude/start', [AptitudeController::class, 'index']);
    Route::post('/aptitude/submit', [AptitudeController::class, 'store']);
    Route::get('dashboard/learner', [DashboardController::class, 'learner']);
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}', [CertificateController::class, 'show']);
    Route::post('/quiz/submit/{id}',[QuizController::class, 'storeAttempt']);
    Route::post('/quiz/check/{id}',[QuizController::class, 'checkAnswer']);
    Route::post('/complete/lesson/{id}',[LessonController::class, 'completeLesson']);
    Route::get('/learning/progress',[LessonController::class, 'progress']);
 
});




Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{id}', [CourseController::class, 'show']);
    Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
        Route::post('/{id}/enroll', [EnrollmentController::class, 'store']);
        Route::get('/{id}/progress', [EnrollmentController::class, 'progress']);
    });
});