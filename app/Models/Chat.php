<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory, BroadcastsEvents;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'receiver_id',
        'message',
        'created_at',
        'updated_at',
    ];

    // protected static function booted()
    // {
    //     static::creating(function ($chat) {
    //         $chat->chat_id = Str::uuid();
    //     });
    // }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'user_id');
    }
}
