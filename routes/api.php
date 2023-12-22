<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register')->name('auth.register');
    Route::post('login', 'login')->name('auth.login');
    Route::post('refresh', 'refresh')->name('auth.refresh');
    Route::post('logout', 'logout')->middleware(['auth:api'])->name('auth.logout');
});

Route::controller(EmailVerificationController::class)->middleware('auth:api')->group(function () {
    Route::get('email/verif/{token}', 'verif')->name('email.verif')->middleware(['throttle:5,1']);
    Route::get('email/resend', 'resend')->name('email.verif')->middleware(['throttle:1,2']);
});

Route::controller(ChatController::class)->middleware(('auth:api'))
    ->group(function () {
        Route::post('chat/{user:username}', 'store')->name('chat.store');
    });





Route::controller(FriendsController::class)->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('friends', 'index')->name("friend.index");
    Route::get('friends/searchfriend/{username?}', 'searchFriend')->name("friend.searchFriend");
    Route::post('friends/sendfriendrequest/{user:username?}', 'sendFriendRequest')->name("friend.sendFriendRequest");
    Route::patch('friends/confirmfriendrequest/{user:username}', 'confirmFriendRequest')->name("friend.confirmFriendRequest");
    Route::post('friends/cancelfriendrequest/{user:username}', 'cancelFriendRequest')->name("friend.cancelFriendRequest");
    Route::get('friends/search/{username?}', 'searchPeople')->name("friend.search");
});

Route::controller(ProfileController::class)->prefix('profile')->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('me', 'me')->name('profile.me');
    Route::put('update', 'update')->name('profile.update');
    Route::get('username/{username?}', 'username')->name('profile.username');
    Route::patch('changepassword', 'changepassword')->middleware('checkpasswordchangetime')->name('profile.changepassword');
});
