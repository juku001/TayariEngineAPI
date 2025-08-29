<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;


Route::get('dashboard/admin', [DashboardController::class, 'admin'])->middleware([
    'auth:sanctum',
    'user.type:super_admin,admin'
]);
Route::prefix('admin')->group(function () {

    Route::middleware(['auth:sanctum', 'user.type:super_admin,admin'])->group(function () {
        // Route::get('users', [UserController::class, 'admin']);
        // Route::get('users/{id}', [UserController::class, 'admin']);

        Route::get('courses/stats', [CourseController::class, 'stats']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::put('courses/{id}', [CourseController::class, 'update']);

        Route::get('logs', [AdminController::class, 'logs']);
        Route::post('communications', [AdminController::class, 'comms']);

    });

});