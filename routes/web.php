<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/certificates/{code}',[CertificateController::class, 'create']);
Route::get('/certificates/{code}/download', [CertificateController::class, 'download']);
