<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message,
            'status_read' => $this->status_read,
            'created_at_hours' => $this->formattedCreatedAt(),
            'created_at' => $this->created_at->format('d/m/Y'),

        ];
    }

    protected function formattedCreatedAt()
    {

        $carbonCreatedAt = Carbon::parse($this->created_at);


        return $carbonCreatedAt->format('h:i A');
    }
}
