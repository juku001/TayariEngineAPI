<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/email', function (){
    return view('emails.instructor-application-status', ['application'=> ['name'=> 'juku'], 'status'=> 'denied', 'pass'=> 'yes']);
});

Route::get('/certificates/{code}',[CertificateController::class, 'create']);
Route::get('/certificates/{code}/download', [CertificateController::class, 'download']);
