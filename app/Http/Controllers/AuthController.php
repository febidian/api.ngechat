<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Jobs\EmailVerify;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            "name" => ['required', 'min:3', 'max:15', 'regex:/^[a-zA-Z\s]+$/', 'string'],
            "email" => ['required', 'email', 'unique:users,email'],
            "password" => ["required", "confirmed", "min:8"]
        ]);

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => Str::random(8),
                'password' => bcrypt($request->password),
                'user_id' => Str::uuid(),

            ]);
            return response()->json([
                'status' => 'success',
                'messages' => [
                    'registration' => 'Registration successful.',
                    'verification' => 'Check your email for verification email.',
                ]
            ], Response::HTTP_CREATED);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'error',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'success',
                'errors' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            "user" => new UserResource(Auth::user()),
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'message' => 'Your Login Success',
            'status' => 'success',
        ], Response::HTTP_OK);
    }
    public function refresh()
    {
        $token = auth()->refresh();
        try {
            return response()->json([
                'access_token' => $token,
                'expires_in' => auth()->factory()->getTTL() * 60,
                'message' => 'Token successfully updated.',
                'status' => 'success',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Token update failed'], 500);
        }
    }

    public function logout()
    {
        auth()->logout(true);

        return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
    }
}
