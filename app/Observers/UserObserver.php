<?php

namespace App\Observers;

use App\Mail\EmailVerification as MailEmailVerification;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $mail = EmailVerification::create([
            'email' => $user->email,
            'token' => Str::random(60),
        ]);


        // $verificationLink = env('FRONTEND_URL') . "$mail->token";
        Mail::to($mail->email)->send(new MailEmailVerification($mail));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
