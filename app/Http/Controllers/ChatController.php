<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Events\StatusEvent;
use App\Http\Resources\ChatsResource;
use App\Http\Resources\List_ChatResource;
use App\Models\Chat;
use App\Models\List_Chats;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mailer\Event\MessageEvents;

class ChatController extends Controller
{
    public function store(Request $request, $user_id)
    {
        $chats = Chat::create([
            'chat_id' => $request->chat_id,
            'sender_id' => Auth::user()->user_id,
            'receiver_id' => $user_id,
            'message' => $request->message,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ]);

        broadcast(new MessageEvent($chats))->toOthers();

        return response()->json([
            'status' => 'success',
        ], Response::HTTP_OK);
    }

    public function showChat(Chat $chat, $user_id)
    {
        $chat = Chat::where(function ($q) use ($user_id) {
            $q->where('sender_id', Auth::user()->user_id)->where('receiver_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', Auth::user()->user_id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(30);


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

    public function updateStatusRead(Request $request)
    {
        $idList = $request->input('idList');

        Chat::whereIn('chat_id', $idList)->update(['status_read' => true]);

        $chat = Chat::where('chat_id', end($idList))->first();

        broadcast(new StatusEvent($chat))->toOthers();

        return response()->json(['status' => 'success'], 200);
    }
}
