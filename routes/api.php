<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckPasswordChangeTime;
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
});

Route::controller(ChatController::class)->middleware(('auth:api'))
    ->group(function () {
        Route::post('chat/{user:username}', 'store')->name('chat.store');
    });


Route::controller(FriendsController::class)->middleware('auth:api')->group(function () {
    Route::get('friends', 'index')->name("friend.index");
    Route::get('friends/searchfriend/{username?}', 'searchFriend')->name("friend.searchFriend");
    Route::post('friends/sendfriendrequest/{user:username?}', 'sendFriendRequest')->name("friend.sendFriendRequest");
    Route::patch('friends/confirmfriendrequest/{user:username}', 'confirmFriendRequest')->name("friend.confirmFriendRequest");
    Route::post('friends/cancelfriendrequest/{user:username}', 'cancelFriendRequest')->name("friend.cancelFriendRequest");
    Route::get('friends/search/{username?}', 'searchPeople')->name("friend.search");
});

Route::controller(ProfileController::class)->prefix('profile')->middleware('auth:api')->group(function () {
    Route::get('me', 'me')->name('profile.me');
    Route::put('update', 'update')->name('profile.update');
    Route::get('username/{username?}', 'username')->name('profile.username');
    Route::patch('changepassword', 'changepassword')->middleware('checkpasswordchangetime')->name('profile.changepassword');
});
