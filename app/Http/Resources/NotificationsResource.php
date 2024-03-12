<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->getData($this->type, $this->data),
        ];
    }

    protected function getData($type, $data)
    {
        $type = $this->type;
        $result = is_array($data) ? $data : json_decode($data, true);
        // Pisahkan namespace dan nama kelas
        $typeParts = explode('\\', $type);
        $className = end($typeParts);

        switch ($className) {
            case 'AddFriend':
                return [
                    "name" => $result["name"],
                    "username" => $result["username"],
                    "small_image" => $result["small_image"],
                    "big_image" => $result["big_image"],
                    "status" => $result["status"],
                    "read_at" => $this->read_at,
                    "created_at_day" => $this->created_at->format('d/m/Y'),
                    "created_at_hours" => $this->created_at->format('h:i A'),
                    "type" => "add-friend"
                ];
            case 'ConfirmFriend':
                return [
                    "name" => $result["name"],
                    "username" => $result["username"],
                    "small_image" => $result["small_image"],
                    "big_image" => $result["big_image"],
                    "read_at" => $this->read_at,
                    "created_at_day" => $result["created_at_day"],
                    "created_at_hours" => $result["created_at_hours"],
                    "type" => "confirm-friend"
                ];

            default:
                return [];
        }
    }
}
