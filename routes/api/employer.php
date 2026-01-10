<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobMatchController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\ManageApplicationController;
use App\Http\Controllers\PopularController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SavedJobsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TrainingController;

Route::get('/jobs/matches', [JobMatchController::class, 'index'])->middleware('auth:sanctum');




Route::middleware(['auth:sanctum', 'user.type:employer'])->group(function () {

    Route::get('/dashboard/employer', [DashboardController::class, 'employer']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::patch('projects/{id}', [ProjectController::class, 'update']);

    Route::get('/jobs/companies/{id}', [JobPostController::class, 'companies']);
    Route::get('/jobs/recent', [JobApplicationController::class, 'recent']);
    Route::get('/jobs/candidates', [JobApplicationController::class, 'candidates']);
    Route::get('/jobs/applications', [JobApplicationController::class, 'applications']);

    Route::post('jobs', [JobPostController::class, 'store']);
    Route::put('jobs/{id}', [JobPostController::class, 'update'])->whereNumber('id');


    Route::get('/projects/companies/{id}', [ProjectController::class, 'companies']);

    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{id}', [TeamController::class, 'show']);

    Route::get('team/invites', [TeamController::class, 'getInvites']);
    Route::post('team/invites', [TeamController::class, 'invite']);

    Route::delete('/team/invite/remove/{id}', [TeamController::class, 'destroy']);
    Route::get('/dashboard/employer/teams', [DashboardController::class, 'teams']);

    Route::patch('/projects/{id}/review/start', [ProjectActivityController::class, 'reviewStart']);
    Route::post('/projects/{id}/review/submit', [ProjectActivityController::class, 'reviewSubmit']);


    Route::get('/applications/{id}/view', [ManageApplicationController::class, 'show']); //change the job_applications of this instance status as reviewed. and show all the details about that particular applicant including the cover letter resumee and personal details
    Route::post('/applications/{id}/reject', [ManageApplicationController::class, 'destroy']); //just change the job_applications status to rejected
    Route::post('/applications/{id}/invite', [ManageApplicationController::class, 'store']); //just change the job applications status to shortlisted and send an email to let them know
    Route::post('/applications/{id}/accept', [ManageApplicationController::class, 'accept']); //just change the job applications status to accepted and send an email to let them know


});

Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
    Route::post('/jobs/apply', [JobApplicationController::class, 'apply']);

    Route::get('/saved-jobs', [SavedJobsController::class, 'index']);
    Route::post('/saved-jobs', [SavedJobsController::class, 'store']);

    Route::post('/projects/{id}/start', [ProjectActivityController::class, 'learnerStart']);
    Route::post('/projects/{id}/complete', [ProjectActivityController::class, 'learnerComplete']);
});

Route::post('team/invite/accept/{token}', [TeamController::class, 'accept']);

Route::get('jobs', [JobPostController::class, 'index']);
Route::get('jobs/{id}', [JobPostController::class, 'show'])->whereNumber('id');
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show'])->whereNumber('id');

Route::get('/jobs-trending', [JobPostController::class, 'trending']);


Route::middleware(['auth:sanctum', 'user.type:employer'])->group(function () {
    Route::get('/employee/progress', [TrainingController::class, 'progress']);
    Route::get('/dashboard/training', [TrainingController::class, 'dashboard']);
    Route::get('/courses/performance', [TrainingController::class, 'courses']);
});


Route::resource('/job-types', JobTypeController::class);


Route::get('/jobs/trending', [PopularController::class, 'trending']);




//PROPOSALS

Route::middleware(['auth:sanctum', 'user.type:learner'])->group(function () {
    Route::prefix('/projects/proposals')->group(function () {
        Route::get('/', [ProposalController::class, 'index']);
        Route::post('/', [ProposalController::class, 'store']);
    });
});

Route::middleware(['auth:sanctum', 'user.type:employer'])->group(function () {
    Route::patch('/projects/proposal/feedback/{id}', [ProposalController::class, 'feedback']);
    Route::get('/projects/proposals/employer', [ProposalController::class, 'employer']); //get list of proposals with their projects (filter status)
    Route::get('/projects/proposals/project/{id}', [ProposalController::class, 'byProject']); //get list of proposals of one project (filter status)
});