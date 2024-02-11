<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class List_Chats extends Model
{
    use HasFactory, BroadcastsEvents;


    protected $table = 'list_chats';

    protected $fillable = ['user_id', 'friend_id', 'updated_at'];

    public function users()
    {
        return $this->belongsTo(User::class, 'friend_id', 'user_id');
    }
}
