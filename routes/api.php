<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — JWT-Authenticated
|--------------------------------------------------------------------------
|
| Public endpoints issue tokens.
| All other endpoints require a valid Bearer token via the jwt.auth middleware.
|
*/

// ── Public (no token required) ────────────────────────────────────────────────
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('login',   [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
});

// ── Protected (token required) ────────────────────────────────────────────────
Route::middleware('custom.jwt')->prefix('auth')->name('api.auth.')->group(function () {
    Route::get ('me',      [AuthController::class, 'me'])->name('me');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('logout',  [AuthController::class, 'logout'])->name('logout');
});
