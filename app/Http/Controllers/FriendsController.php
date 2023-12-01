<?php

namespace App\Http\Controllers;

use App\Http\Resources\SearchPeopleResource;
use App\Http\Resources\UserResource;
use App\Models\Friends;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FriendsController extends Controller
{
    public function index()
    {
        return 'ok';
    }
    public function searchPeople(String $username = null)
    {

        try {
            $result = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('userFriends', function ($query) {
                        $query->where('status', false);
                    })
                        ->orWhereDoesntHave('userFriends');
                })
                ->where(function ($query) {
                    $query->whereHas('friendOf', function ($query) {
                        $query->where('status', false);
                    })
                        ->orWhereDoesntHave('friendOf');
                })
                ->with(["userFriends" => function ($query) {
                    $query->where('friend_id', auth()->user()->user_id);
                }])
                ->with(["friendOf" => function ($query) {
                    $query->where('user_id', auth()->user()->user_id)->get();
                }])
                ->where(function ($query) use ($username) {
                    $query->where('name', 'LIKE', '%' . $username . '%')
                        ->orWhere('username', 'LIKE', '%' . $username . '%');
                })
                ->paginate(6);

            return response()->json([
                'people' => SearchPeopleResource::collection($result)->response()->getData(),
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'message' => 'failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendFriendRequest(User $user)
    {
        if ($user->user_id !== auth()->user()->user_id) {
            try {
                $check = Friends::where(function ($query) use ($user) {
                    $query->where('user_id', auth()->user()->user_id)
                        ->where('friend_id', $user->user_id);
                })->orWhere(function ($query) use ($user) {
                    $query->where('user_id',  $user->user_id)
                        ->where('friend_id', auth()->user()->user_id);
                })->exists();

                if (!$check) {

                    Friends::create([
                        'user_id' => auth()->user()->user_id,
                        'friend_id' => $user->user_id,
                    ]);
                    return response()->json([
                        'status' => 'success'
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'failed',
                    ], Response::HTTP_BAD_GATEWAY);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 'failed',
                    't' => $th
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function confirmFriendRequest(User $user)
    {
        try {
            Friends::where('user_id', $user->user_id)
                ->where('friend_id', auth()->user()->user_id)
                ->update([
                    'status' => true
                ]);
            return response()->json([
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancelFriendRequest(User $user)
    {
        try {
            Friends::where(function ($query) use ($user) {
                $query->where('user_id', auth()->user()->user_id)
                    ->where('friend_id', $user->user_id);
            })->orWhere(function ($query) use ($user) {
                $query->where('user_id',  $user->user_id)
                    ->where('friend_id', auth()->user()->user_id);
            })->delete();
            return response()->json([
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
