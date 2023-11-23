<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FriendsController extends Controller
{
    public function index()
    {
        return 'ok';
    }
    public function addFriend(User $user)
    {
        if ($user->user_id != auth()->user()->user_id) {
            try {
                auth()->user()->friends()->create([
                    'friend_id' => $user->user_id,
                ]);

                return response()->json([
                    'status' => 'success'
                ], Response::HTTP_CREATED);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 'failed'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    public function searchPeople(String $username)
    {
        try {
            $result = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('friends', function ($query) {
                        $query->where('status', false);
                    })
                        ->orWhereDoesntHave('friends');
                })
                ->where(function ($query) use ($username) {
                    $query->where('name', 'LIKE', '%' . $username . '%')
                        ->orWhere('username', 'LIKE', '%' . $username . '%');
                })
                ->paginate(6);

            return response()->json([
                'data' => $result,
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
