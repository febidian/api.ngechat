<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function store(Request $request, Chat $chat, User $user)
    {
        $chats = Chat::create([
            'sender_id' => Auth::user()->user_id,
            'receiver' => $user->user_id,
            'message' => $request->message,
        ]);
    }
}
