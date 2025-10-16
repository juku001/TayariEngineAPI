<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
 



Route::get('/', [AuthController::class, 'unauthorized'])->name('login');
Route::get('/is_auth', [AuthController::class, 'authorized'])->middleware('auth:sanctum');
Route::post('is_verified', [AuthController::class, 'verified']);
Route::prefix('auth')->group(function () {

    Route::post('/login', [LogInController::class, 'index']);
    Route::post('/register', [RegisterController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'destroy']);


    Route::post('/google', [LogInController::class, 'redirect']);
    Route::get('/google/callback', [LogInController::class, 'callback']);


    Route::post('/forgot_password', [PasswordController::class,'index']);
    Route::post('/verify_code', [PasswordController::class,'verify']);
    Route::post('/reset_password', [PasswordController::class, 'store']);
    Route::post('update_password', [PasswordController::class, 'update'])->middleware('auth:sanctum');

    Route::post('/verify-email', [VerificationController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed']) // remove auth
        ->name('verification.verify');

});

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('user.type:super_admin,admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
    });
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('user.type:super_admin');
});
