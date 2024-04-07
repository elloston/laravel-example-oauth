<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Socialite
Route::get('/oauth/{provider}', [AuthController::class, 'redirect']);
Route::get('/oauth/{provider}/callback', [AuthController::class, 'callback']);
