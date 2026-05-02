<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReaction extends Model
{
    protected $fillable = ['message_id', 'user_id', 'emoji'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
