<?php

use App\Models\Chat;
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

Broadcast::channel('statusUser.{chat_id}', function (User $user, $chat_id) {
    $chat = Chat::where('chat_id', $chat_id)
        ->where(function ($query) use ($user) {
            $query->where('sender_id', $user->user_id)
                ->orWhere('receiver_id', $user->user_id);
        })
        ->first();
    if ($chat) {
        return true;
    }
});

//  

Broadcast::channel('sortlisfriendchat.{friend_id}', function (User $user, $friend_id) {
    return (int) $user->user_id === (int) $friend_id;
});
