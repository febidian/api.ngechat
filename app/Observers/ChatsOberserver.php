<?php

namespace App\Observers;

use App\Events\SortListChatEvent;
use App\Models\Chat;
use App\Models\List_Chats;
use Carbon\Carbon;

class ChatsOberserver
{
    /**
     * Handle the Chat "created" event.
     */
    public function created(Chat $chat): void
    {

        $updateListChats_1 = List_Chats::updateOrCreate(
            [
                "user_id" => $chat->sender_id,
                "friend_id" => $chat->receiver_id,

            ],
            ["updated_at" => $chat->updated_at]
        );
        $updateListChats_2 = List_Chats::updateOrCreate(
            [
                "user_id" => $chat->receiver_id,
                "friend_id" => $chat->sender_id,

            ],
            ["updated_at" => $chat->updated_at]
        );

        if (!$updateListChats_1->wasRecentlyCreated && !$updateListChats_2->wasRecentlyCreated) {
            broadcast(new SortListChatEvent($updateListChats_1))->toOthers();
        }
    }

    /**
     * Handle the Chat "updated" event.
     */
    public function updated(Chat $chat): void
    {
        //
    }

    /**
     * Handle the Chat "deleted" event.
     */
    public function deleted(Chat $chat): void
    {
        //
    }

    /**
     * Handle the Chat "restored" event.
     */
    public function restored(Chat $chat): void
    {
        //
    }

    /**
     * Handle the Chat "force deleted" event.
     */
    public function forceDeleted(Chat $chat): void
    {
        //
    }
}
