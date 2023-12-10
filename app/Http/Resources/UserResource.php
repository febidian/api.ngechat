<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "email" => $this->email,
            'username' => $this->username,
            'user_id' => $this->user_id,
            'small_image' => $this->small_image ? Storage::url($this->small_image) : null,
            'big_image' => $this->big_image ? Storage::url($this->big_image) : null,
        ];
    }
}
