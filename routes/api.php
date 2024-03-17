<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register')->name('auth.register');
    Route::post('login', 'login')->name('auth.login');
    Route::post('refresh', 'refresh')->name('auth.refresh');
    Route::post('logout', 'logout')->middleware(['auth:api'])->name('auth.logout');
});

Route::controller(ForgotPasswordController::class)->prefix('auth')->group(function () {
    Route::post('reset/password', 'sendResetPassword')->middleware(['throttle:7,30'])->name('auth.send_reset_password');
    Route::post('reset/password/confirm/{token}', 'confirmResetPassowrd')->name('auth.confirm_reset_passowrd');
    Route::get('reset/password/check/{token}', 'checkToken')->middleware(['throttle:10,1'])->name('auth.check_token');
});

Route::controller(EmailVerificationController::class)->middleware('auth:api')->group(function () {
    Route::get('email/verif/{token}', 'verif')->name('email.verif')->middleware(['throttle:5,1']);
    Route::get('email/resend', 'resend')->name('email.verif')->middleware(['throttle:1,2']);
});


Route::controller(FriendsController::class)->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('friends', 'index')->name("friend.index");
    Route::get('friends/searchfriend/{username?}', 'searchFriend')->name("friend.searchFriend");
    Route::post('friends/sendfriendrequest/{user:username?}', 'sendFriendRequest')->middleware(['throttle:5,1'])->name("friend.sendFriendRequest");
    Route::patch('friends/confirmfriendrequest/{user:username}/{id?}', 'confirmFriendRequest')->name("friend.confirmFriendRequest");
    Route::post('friends/cancelfriendrequest/{user:username}/{id?}', 'cancelFriendRequest')->name("friend.cancelFriendRequest");
    Route::get('friends/profile/{user_id}', 'friendProfile')->name("friend.friendProfile");
    Route::get('friends/search/{username?}', 'searchPeople')->name("friend.search");
});

Route::controller(ProfileController::class)->prefix('profile')->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('me', 'me')->name('profile.me');
    Route::put('update', 'update')->name('profile.update');
    Route::get('username/{username?}', 'username')->name('profile.username');
    Route::patch('changepassword', 'changepassword')->middleware('checkpasswordchangetime')->name('profile.changepassword');
});

Route::controller(ChatController::class)->prefix('chat')->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('list', 'loadChat')->name('chat.loadchat');
    Route::post('update-status-read', 'updateStatusRead')->name('chat.updateStatusRead');
    Route::post('{user_id}', 'store')->name('chat.store');
    Route::get('{user_id}', 'showChat')->name('chat.show');
});

Route::controller(NotificationsController::class)->prefix('notifications')->middleware(['auth:api', 'email_verif'])->group(function () {
    Route::get('/', 'index')->name('notifications.index');
    Route::put('read', 'NotficationsRead')->name('notifications.read');
});
