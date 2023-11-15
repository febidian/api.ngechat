<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('register', 'register')->withoutMiddleware('auth:api')->name('auth.register');
    Route::post('login', 'login')->withoutMiddleware('auth:api')->name('auth.login');
    Route::post('refresh', 'refresh')->withoutMiddleware('auth:api')->name('auth.refresh');
    Route::post('logout', 'logout')->name('auth.logout');
    Route::get('me', 'me')->name('auth.me');
});

Route::controller(ChatController::class)->middleware(('auth:api'))
    ->group(function () {
        Route::post('chat/{user:username}', 'store')->name('chat.store');
    });
