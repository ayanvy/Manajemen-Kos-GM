<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\TagihanController;

// Login API tanpa token.
Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

// Endpoint yang membutuhkan token Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    Route::get('/kamar', [KamarController::class, 'index'])
        ->name('api.kamar.index');

    Route::get('/kamar/{id}', [KamarController::class, 'show'])
        ->whereNumber('id')
        ->name('api.kamar.show');
    
    Route::get('/tagihan', [TagihanController::class, 'index'])
        ->name('api.tagihan.index');

    Route::get('/tagihan/{id}', [TagihanController::class, 'show'])
        ->whereNumber('id')
        ->name('api.tagihan.show');
});