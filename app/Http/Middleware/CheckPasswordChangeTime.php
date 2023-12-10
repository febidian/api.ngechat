<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChangeTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();


        if ($user && $user->change_password_at && Carbon::now()->diffInDays($user->change_password_at) < 7) {
            return response()->json([
                'tes' => Carbon::now()->diffInDays($user->change_password_at),
                'errors' => [
                    'password_old' => "You've recently changed your password. Please wait 7 days.",
                ],
                'status' => 'failed',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $next($request);
    }
}
