<?php

namespace App\Http\Controllers;

use App\Mail\RisetPasswordMail;
use App\Models\RisetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class ForgotPasswordController extends Controller
{
    public function sendResetPassword(Request $request)
    {
        try {

            $request->validate([
                "email" => 'required',
            ]);

            $email = $request->email;

            $checkEmail = User::where('email', $email)->first();

            if (!$checkEmail) {
                return response()->json([
                    'errors' => [
                        'email' => ['These emails do not match our records.'],
                    ],
                    'status' => 'failed'
                ], Response::HTTP_BAD_REQUEST);
            } else {
                $checkEmailPassword = RisetPassword::where('email', $email)->first();
                $token = Str::random(60);
                if ($checkEmailPassword) {
                    $checkEmailPassword->delete();
                    $data = RisetPassword::create([
                        'email' => $email,
                        'token' => $token,
                        'created_at' => now()
                    ]);
                } else {
                    $data = RisetPassword::create([
                        'email' => $email,
                        'token' => $token,
                        'created_at' => now()
                    ]);
                }

                // return $data->token;

                Mail::to($email)->send(new RisetPasswordMail($token));

                return response()->json([
                    'message' => 'Password reset email sent.',
                    'status' => 'success'
                ], Response::HTTP_OK);
            }
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
                'th' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function confirmResetPassowrd(Request $request, $token)
    {
        try {

            $request->validate([
                'password' => ["required", "confirmed", "min:8"],
                'password_confirmation' => ["required", "min:8"]
            ]);

            $checkToken = RisetPassword::where('token', $token)->first();

            if ($checkToken) {
                if (Carbon::now()->diffInDays($checkToken->created_at) < 2) {
                    $user = User::where('email', $checkToken->email)->first()->update([
                        'password' => Hash::make($request->password)
                    ]);

                    $checkToken->delete();
                    return response()->json([
                        'message' => 'Password successfully changed.',
                        'status' => 'success'
                    ], Response::HTTP_OK);
                } else {
                    $checkToken->delete();
                    return response()->json([
                        'message' => 'Link Has Expired',
                        'status' => 'failed'
                    ], Response::HTTP_GONE);
                }
            } else {
                return response()->json([
                    'message' => 'Link Not Found',
                    'status' => 'failed'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkToken($token)
    {
        $checkToken = RisetPassword::where('token', $token)->first();

        if ($checkToken) {
            if (Carbon::now()->diffInDays($checkToken->created_at) < 2) {
                return response()->json([
                    'status' => 'success'
                ], Response::HTTP_OK);
            } else {
                $checkToken->delete();
                return response()->json([
                    'message' => 'Link Has Expired',
                    'status' => 'failed'
                ], Response::HTTP_GONE);
            }
        } else {
            return response()->json([
                'message' => 'Link Not Found',
                'status' => 'failed'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
