<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\EmailVerification as MailEmailVerification;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class EmailVerificationController extends Controller
{
    public function verif($token)
    {
        $auth = Auth::user();
        $verif = EmailVerification::where('email', $auth->email)
            ->where('token', $token)->first();

        if ($verif) {
            if (Carbon::now()->diffInDays($verif->created_at) < 2) {
                User::where('email', $auth->email)->update([
                    'email_verified_at' => Carbon::now()
                ]);
                $verif->delete();
                $user = User::where('email', $auth->email)->first();
                return response()->json([
                    'user' => new UserResource($user),
                    'message' => 'Email Verification Successful',
                    'status' => 'success'
                ], Response::HTTP_OK);
            } else {
                $verif->delete();
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

    public function resend()
    {

        $auth = Auth::user();

        if (!$auth->email_verified_at) {
            EmailVerification::where('email', $auth->email)
                ->delete();
            $mail = EmailVerification::create([
                'email' => $auth->email,
                'token' => Str::random(60),
            ]);

            Mail::to($mail->email)->send(new MailEmailVerification($mail));

            return response()->json([
                'message' => "Verification email has been sent.",
                'status' => 'success'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => "Email has been verified",
                'status' => 'failed'
            ], Response::HTTP_OK);
        }
    }
}
