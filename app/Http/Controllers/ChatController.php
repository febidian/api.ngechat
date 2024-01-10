<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Http\Resources\ChatsResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function store(Request $request, Chat $chat, User $user)
    {
        $chats = Chat::create([
            'sender_id' => Auth::user()->user_id,
            'receiver_id' => $user->user_id,
            'message' => $request->message,
        ]);

        broadcast(new MessageEvent($chats))->toOthers();
    }

    public function showChat(User $user)
    {
        // return $user;
        $chat = Chat::where(function ($q) use ($user) {
            $q->where('sender_id', Auth::user()->user_id)->where('receiver_id', $user->user_id);
        })->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->user_id)->where('receiver_id', Auth::user()->user_id);
        })
            ->paginate(15);

        return response()->json([
            'chat' => ChatsResource::collection($chat)->response()->getData(),
            'status' => 'success'
        ], Response::HTTP_OK);
    }
}
