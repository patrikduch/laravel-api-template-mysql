<?php

use App\Http\Controllers\AwsSESController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Registration (UserController)
Route::post('/register', [UserController::class, 'store']);

// Auth (AuthController)
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']); // renamed from /user for clarity

    // AWS SES - simple hello email (protected)
    Route::post('/aws-ses/hello', [AwsSESController::class, 'sendHello']);
});
