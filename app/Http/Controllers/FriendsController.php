<?php

namespace App\Http\Controllers;

use App\Http\Resources\SearchPeopleResource;
use App\Http\Resources\UserResource;
use App\Models\Friends;
use App\Models\User;
use App\Notifications\AddFriend;
use App\Notifications\ConfirmFriend;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendsController extends Controller
{
    public function index()
    {
        try {
            $friends = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('userFriends', function ($query) {
                        $query->where('friend_id', auth()->user()->user_id)->where('status', true);
                    });
                })
                ->orWhere(function ($query) {
                    $query->whereHas('friendOf', function ($query) {
                        $query->where('user_id', auth()->user()->user_id)->where('status', true);
                    });
                })
                ->orderBy('name', 'asc')
                ->paginate(30);

            return response()->json([
                'friends' => UserResource::collection($friends)->response()->getData(),
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function searchFriend(String $username = null)
    {
        try {
            $friends = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('userFriends', function ($query) {
                        $query->where('friend_id', auth()->user()->user_id)->where('status', true);
                    })->orWhereHas('friendOf', function ($query) {
                        $query->where('user_id', auth()->user()->user_id)->where('status', true);
                    });
                })
                ->where(function ($query) use ($username) {
                    $query->where('name', 'LIKE', '%' . $username . '%')
                        ->orWhere('username', 'LIKE', '%' . $username . '%');
                })
                ->orderBy('name', 'asc')
                ->limit(4)->get();

            return response()->json([
                'friends' => UserResource::collection($friends),
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function searchPeople(String $username = null)
    {
        try {
            $result = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('userFriends', function ($query) {
                        $query->where(function ($q) {
                            $q->where('status', false)
                                ->orWhere('status', true)->where('friend_id', '!=', auth()->user()->user_id);
                        });
                    })
                        ->orWhereDoesntHave('userFriends');
                })
                ->where(function ($query) {
                    $query->whereHas('friendOf', function ($query) {
                        $query->where(function ($q) {
                            $q->where('status', false)
                                ->orWhere('status', true)->where('user_id', '!=', auth()->user()->user_id);
                        });
                    })
                        ->orWhereDoesntHave('friendOf');
                })
                ->with(["userFriends" => function ($query) {
                    $query->where('friend_id', auth()->user()->user_id);
                }])
                ->with(["friendOf" => function ($query) {
                    $query->where('user_id', auth()->user()->user_id);
                }])
                ->where(function ($query) use ($username) {
                    $query->where('name', 'LIKE', '%' . $username . '%')
                        ->orWhere('username', 'LIKE', '%' . $username . '%');
                })
                ->limit('5')->get();

            return response()->json([
                'people' => SearchPeopleResource::collection($result),
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'message' => 'failed',
                'th' => $th
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

                    $addFriend = Friends::create([
                        'user_id' => auth()->user()->user_id,
                        'friend_id' => $user->user_id,
                        'status' => false
                    ]);

                    if ($addFriend) {
                        $me = Auth::user();
                        $user->notify(new AddFriend($me, $addFriend->status));
                    }

                    return response()->json([
                        'status' => 'success'
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'failed',
                    ], Response::HTTP_BAD_GATEWAY);
                }
            } catch (QueryException $th) {
                return response()->json([
                    'status' => 'failed',
                    'th' => $th
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function confirmFriendRequest(User $user, $id = null)
    {
        try {
            $select = Friends::where('user_id', $user->user_id)
                ->where('friend_id', auth()->user()->user_id)->firstOrFail();
            $select->update([
                'status' => true
            ]);

            if ($select) {
                if ($id) {
                    DB::table('notifications')->where('id', $id)
                        ->update([
                            'data' => DB::raw("JSON_SET(data, '$.status', true)"),
                        ]);
                }

                $me = Auth::user();
                $user->notify(new ConfirmFriend($me));
            }
            return response()->json([
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            if ($id) {
                DB::table('notifications')->where('id', $id)
                    ->update([
                        'data' => DB::raw("JSON_SET(data, '$.status', 'cancel')"),
                    ]);
            }
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancelFriendRequest(User $user, $id = null)
    {
        try {
            $result = Friends::where(function ($query) use ($user) {
                $query->where('user_id', auth()->user()->user_id)
                    ->where('friend_id', $user->user_id);
            })->orWhere(function ($query) use ($user) {
                $query->where('user_id',  $user->user_id)
                    ->where('friend_id', auth()->user()->user_id);
            })->firstOrFail();

            $result->delete();

            DB::table('notifications')
                ->where('type', 'App\Notifications\AddFriend')
                ->where('notifiable_id', $user->id)
                ->where('data->user_id', auth()->user()->user_id)->delete();

            if ($result) {
                if ($id) {
                    DB::table('notifications')->where('id', $id)
                        ->update([
                            'data' => DB::raw("JSON_SET(data, '$.status', null)"),
                        ]);
                }
            }
            return response()->json([
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            if ($id) {
                DB::table('notifications')->where('id', $id)
                    ->update([
                        'data' => DB::raw("JSON_SET(data, '$.status', 'cancel')"),
                    ]);
            }
            return response()->json([
                'status' => 'failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function friendProfile($user_id)
    {
        try {
            $friendProfile = User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) {
                    $query->whereHas('userFriends', function ($query) {
                        $query->where('friend_id', auth()->user()->user_id)->where('status', true);
                    })->orWhereHas('friendOf', function ($query) {
                        $query->where('user_id', auth()->user()->user_id)->where('status', true);
                    });
                })
                ->where('user_id', $user_id)
                ->first();

            return response()->json([
                'user' => new UserResource($friendProfile),
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
                'tg' => $th
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
