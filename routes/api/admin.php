<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AptitudeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\UserController;
use Masterminds\HTML5\InstructionProcessor;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;


Route::get('dashboard/admin', [DashboardController::class, 'admin'])->middleware([
    'auth:sanctum',
    'user.type:super_admin,admin'
]);





Route::prefix('admin')->group(function () {

    Route::middleware(['auth:sanctum', 'user.type:super_admin,admin'])->group(function () {
        Route::get('courses/stats', [CourseController::class, 'stats']);
        Route::get('courses', [CourseController::class, 'admin']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::patch('courses/{id}', [CourseController::class, 'update']);
        Route::delete('courses/{id}', [CourseController::class, 'destroy']);
        Route::patch('courses/{id}/publish', [CourseController::class, 'status']);
        Route::post('/courses/assign', [InstructorController::class, 'assign']);
        Route::get('logs', [AdminController::class, 'logs']);
        Route::post('communications', [AdminController::class, 'comms']);

        Route::get('/aptitudes', [AptitudeController::class, 'getAllAptitudes']);
        Route::post('/aptitudes', [AptitudeController::class, 'addNewAptitude']);
        Route::put('/aptitudes/{id}', [AptitudeController::class, 'update']);
        Route::get('/aptitudes/{id}', [AptitudeController::class, 'show']);
        Route::delete('/aptitudes/{id}', [AptitudeController::class, 'destroy']);
        Route::delete('/aptitude/{questionId}/options/{optionid}', [AptitudeController::class, 'destroyOptions']);
    });

});



