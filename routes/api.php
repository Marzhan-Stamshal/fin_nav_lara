<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\PaymentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/loans', [LoanApiController::class, 'index']);
    Route::post('/loans', [LoanApiController::class, 'store']);
    Route::get('/loans/{loan}', [LoanApiController::class, 'show']);
    Route::put('/loans/{loan}', [LoanApiController::class, 'update']);
    Route::delete('/loans/{loan}', [LoanApiController::class, 'destroy']);
    Route::get('/loans/{loan}/payments', [LoanApiController::class, 'payments']);
    Route::post('/loans/{loan}/payments', [LoanApiController::class, 'addPayment']);

    Route::get('/payments', [PaymentApiController::class, 'index']);
});
