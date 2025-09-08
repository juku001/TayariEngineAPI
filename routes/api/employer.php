<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\TeamController;


Route::middleware(['auth:sanctum', 'user.type:employer'])->group(function () {
    Route::get('/dashboard/employer', [DashboardController::class, 'employer']);
    Route::post('jobs', [JobPostController::class, 'store']);
    Route::post('projects', [ProjectController::class, 'store']);

    Route::get('/jobs/companies/{id}', [JobPostController::class, 'companies']);

    Route::get('/projects/companies/{id}', [ProjectController::class, 'companies']);

    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{id}', [TeamController::class, 'show']);

    Route::get('team/invites', [TeamController::class, 'getInvites']);
    Route::post('team/invites', [TeamController::class, 'invite']);
    Route::delete('team/invites/remove/{id}', [TeamController::class, 'destroy']);
    Route::get('/dashboard/employer/teams', [DashboardController::class, 'teams']);

    Route::patch('/projects/proposal/feedback/{id}', [ProposalController::class, 'feedback']);



    Route::patch('/projects/{id}/review/start', [ProjectActivityController::class, 'reviewStart']);
    Route::post('/projects/{id}/review/submit', [ProjectActivityController::class, 'reviewSubmit']);

});

Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
    Route::prefix('/projects/proposals')->group(function () {
        Route::get('/', [ProposalController::class, 'index']);
        Route::post('/', [ProposalController::class, 'store']);
    });
        Route::post('/projects/{id}/start', [ProjectActivityController::class, 'learnerStart']);
    Route::post('/projects/{id}/complete', [ProjectActivityController::class, 'learnerComplete']);
});

Route::post('team/invite/accept/{token}', [TeamController::class, 'accept']);
Route::get('jobs', [JobPostController::class, 'index']);
Route::get('jobs/{id}', [JobPostController::class, 'show']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

