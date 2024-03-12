<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class AddFriend extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $me, $status;
    public function __construct($me, $status)
    {
        $this->me = $me;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "name" => $this->me->name,
            'username' => $this->me->username,
            'user_id' => $this->me->user_id,
            'small_image' => $this->me->small_image ? Storage::url($this->me->small_image) : null,
            'big_image' => $this->me->big_image ? Storage::url($this->me->big_image) : null,
            "status" => $this->status,
            "type" => "add-friend",
            "created_at_day" => Carbon::now()->format('d/m/Y'),
            "created_at_hours" => Carbon::now()->format('h:i A'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType()
    {
        return 'add-friend';
    }
}
