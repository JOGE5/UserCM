<?php

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

/**
 * Here you may register all of the event broadcasting channels that your
 * application supports. The given channel authorization callbacks are
 * used to check if an authenticated user can listen to the channel.
 */

// Canal privado para mensajes
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $isMember = Chat::where('id', $chatId)
        ->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();

    if (! $isMember) {
        return false;
    }

    // Presence channel: retornar datos del usuario
    return ['id' => $user->id, 'name' => $user->name];
});
