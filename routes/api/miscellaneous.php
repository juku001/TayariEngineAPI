<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SkillController;
use App\Models\Level;


Route::resource('categories', CategoryController::class);
Route::resource('skills', SkillController::class);
Route::get('levels', [SkillController::class, 'levels']);

Route::get('/badges', [BadgeController::class, 'index'])->middleware(['auth:sanctum', 'user.type:learner']);