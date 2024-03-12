<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ConfirmFriend extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $friend;
    public function __construct($friend)
    {
        $this->friend = $friend;
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
            "name" => $this->friend->name,
            'username' => $this->friend->username,
            'user_id' => $this->friend->user_id,
            'small_image' => $this->friend->small_image ? Storage::url($this->friend->small_image) : null,
            'big_image' => $this->friend->big_image ? Storage::url($this->friend->big_image) : null,
            "type" => "confirm-friend",
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
        return 'confirm-friend';
    }
}
