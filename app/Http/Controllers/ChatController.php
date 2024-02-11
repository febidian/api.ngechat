<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Http\Resources\ChatsResource;
use App\Http\Resources\List_ChatResource;
use App\Models\Chat;
use App\Models\List_Chats;
use App\Models\User;
use Illuminate\Database\QueryException;
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
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
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
            ->orderBy('created_at', 'desc')
            ->paginate(15);


        return response()->json([
            'chat' => ChatsResource::collection($chat)->response()->getData(),
            'status' => 'success'
        ], Response::HTTP_OK);
    }

    public function loadChat()
    {
        try {
            $listChats = List_Chats::select('friend_id', 'updated_at')
                ->where('user_id', Auth::user()->user_id)
                ->with('users')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            return response()->json([
                'list_chats' => List_ChatResource::collection($listChats)->response()->getData(),
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
