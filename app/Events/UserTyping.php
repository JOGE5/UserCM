<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;
    public int $userId;
    public string $userName;
    public bool $isTyping;

    public function __construct(int $chatId, int $userId, string $userName, bool $isTyping)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('chat.' . $this->chatId)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->userId,
            'user_name' => $this->userName,
            'is_typing' => $this->isTyping,
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserTyping';
    }
}
