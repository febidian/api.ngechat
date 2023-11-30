<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Friends extends Model
{
    use HasFactory, HasEagerLimit;

    protected $fillable = [
        "user_id",
        "friend_id",
        "status"
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'friend_id', 'user_id');
    }
}
