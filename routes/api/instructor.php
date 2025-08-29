<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;


Route::prefix('instructor')->group(function () {

    Route::post('/apply', [InstructorController::class, 'store']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/applications', [InstructorController::class, 'index']);
        Route::get('/applications/{id}', [InstructorController::class, 'show']);
        Route::patch('/applications/{id}', [InstructorController::class, 'update'])->middleware('user.type:super_admin,admin');
    });
});

Route::get('/dashboard/instructor', [DashboardController::class, 'instructor'])
->middleware('auth:sanctum,user.type:instructor');