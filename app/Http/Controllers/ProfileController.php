<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Validation\ValidationException;


class ProfileController extends Controller
{
    public function me()
    {
        return response()->json([
            "user" => new UserResource(Auth::user())
        ]);
    }

    public function update(Request $request)
    {
        try {
            $user = User::where('id', auth()->user()->id)->firstOrFail();
            $request->validate([
                'name' => ['required', 'min:3', 'max:15', 'regex:/^[a-zA-Z\s]+$/', 'string'],
                'username' => ['required', 'min:3', 'max:13', 'regex:/^[A-Za-z0-9_.]+$/', Rule::unique('users', 'username')->ignore($user->id)],
                'image' => ['nullable', 'mimes:jpeg,png,jpg', 'max:2048']
            ]);

            if ($request->hasFile('image')) {
                if ($user->small_image && $user->big_image) {
                    Storage::delete($user->small_image);
                    Storage::delete($user->big_image);
                }
                $small_image = $request->file('image')->store('profile/small');
                $big_small = $request->file('image')->store('profile/big');
                $resizeSmall = Image::make(public_path("storage/{$small_image}"));
                $resizeSmall->resize(null, '155', function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resizeSmall->save(public_path("storage/{$small_image}"));

                $resizeBig = Image::make(public_path("storage/{$big_small}"));
                $resizeBig->resize(null, '450', function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resizeBig->save(public_path("storage/{$big_small}"));
            } else if ($user->small_image && $user->big_image) {
                $small_image = $user->small_image;
                $big_small = $user->big_image;
            } else {
                $small_image = null;
                $big_small = null;
            }

            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'small_image' => $small_image,
                'big_image' => $big_small,
            ]);

            return response()->json([
                'user' => new UserResource($user),
                'message' => "Profile updated successfully.",
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function username(String $username)
    {
        $result = User::where('username', $username);
        if ($result) {
            return 'ok';
        } else {
            return 'not ok';
        }
    }


    public function changepassword(Request $request)
    {
        try {
            $request->validate([
                'password_old' => ['required', 'string', 'min:8'],
                'password_new' => ['required', 'string', 'min:8', 'confirmed']
            ]);

            $auth = Auth::user();
            $key = 'change-password:' . $auth->id;
            $attempts = 3;
            $second = 21600;

            $executed = RateLimiter::attempt(
                $key,
                $attempts,
                function () use ($auth, $request) {
                    if (!Hash::check($request->password_old, $auth->password)) {
                        return 'incorrect_old_password';
                    } else {
                        User::where('id', $auth->id)->update([
                            'change_password_at' => now(),
                            'password' => bcrypt($request->password_new),
                        ]);
                        return 'success';
                    }
                },
                $second
            );

            if ($executed === 'incorrect_old_password') {
                $amount = $attempts - RateLimiter::attempts($key);

                return response()->json([
                    'status' => 'failed',
                    'errors' => [
                        'password_old' => ["Incorrect Old Password. You have {$amount} attempts remaining."],
                    ],
                ], Response::HTTP_BAD_REQUEST);
            } elseif ($executed === 'success') {
                RateLimiter::clear($key);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password changed successfully.',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'errors' => [
                        'password_old' => ['Exceeded 3 attempts limit. You can try again in 6 hours.'],
                    ],
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
                't' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
