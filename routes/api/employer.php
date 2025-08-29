<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\TeamController;


Route::middleware(['auth:sanctum', 'user.type:employer'])->group(function () {
    Route::get('/dashboard/employer', [DashboardController::class, 'employer']);
    Route::post('jobs', [JobPostController::class, 'store']);

    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{id}', [TeamController::class, 'show']);

    Route::get('team/invites',[TeamController::class, 'getInvites']);
    Route::post('team/invites', [TeamController::class, 'invite']);
    Route::delete('team/invites/remove/{id}', [TeamController::class, 'destroy']);
Route::get('/dashboard/employer/teams', [DashboardController::class, 'teams']);

});


Route::post('team/invite/accept/{token}', [TeamController::class, 'accept']);
Route::get('jobs', [JobPostController::class, 'index']);
