<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'tipo',
        'nombre',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users')
            ->withPivot('is_muted', 'is_hidden', 'last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
