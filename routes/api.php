<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('register', 'register')->withoutMiddleware('auth:api')->name('auth.register');
    Route::post('login', 'login')->withoutMiddleware('auth:api')->name('auth.login');
});
