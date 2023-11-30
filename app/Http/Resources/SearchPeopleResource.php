<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchPeopleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $arrayData = [
            "name" => $this->name,
            "email" => $this->email,
            'username' => $this->username,
            'status_one' => new FriendsResource($this->whenLoaded("userFriends")),
            'status_two' => new FriendsResource($this->whenLoaded("friendOf"))

        ];

        return $arrayData;
    }
}
