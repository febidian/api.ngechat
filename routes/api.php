<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FriendsController;
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


Route::controller(FriendsController::class)->middleware('auth:api')->group(function () {
    Route::get('friends', 'index')->name("friend.index");
    Route::post('friends/sendfriendrequest/{user:username?}', 'sendFriendRequest')->name("friend.sendFriendRequest");
    Route::patch('friends/confirmfriendrequest/{user:username}', 'confirmFriendRequest')->name("friend.confirmFriendRequest");
    Route::post('friends/cancelfriendrequest/{user:username}', 'cancelFriendRequest')->name("friend.cancelFriendRequest");
    Route::get('friends/search/{username?}', 'searchPeople')->name("friend.search");
});
