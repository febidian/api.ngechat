<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $credentials = $request->validate([
            "name" => ['required'],
            "email" => ['required', 'email', 'unique:users,email'],
            "password" => ["required", "confirmed", "min:8"]
        ]);

        try {
            DB::table('users')->insert([
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'password' => bcrypt($credentials['password']),
                'chats_id' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully registered'
            ], Response::HTTP_CREATED);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
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
                'error' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ], Response::HTTP_OK);
    }
}
