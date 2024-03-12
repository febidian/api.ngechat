<?php

use App\Models\Chat;
use App\Models\Friends;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chats.{receiver_id}', function (User $user, $receiver_id) {

    return (int) $user->user_id === (int) $receiver_id;
});
Broadcast::channel('App.Models.User.{user_id}', function (User $user, $user_id) {

    return (int) $user->user_id === (int) $user_id;
});

Broadcast::channel('statusUser.{sender_id}', function (User $user, $sender_id) {
    return (int) $user->user_id === (int) $sender_id;
});

Broadcast::channel('statusUserTyping.{receiver_id}', function (User $user, $receiver_id) {
    return Auth::check();
});

//  

Broadcast::channel('sortlisfriendchat.{friend_id}', function (User $user, $friend_id) {
    return (int) $user->user_id === (int) $friend_id;
});
