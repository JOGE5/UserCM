<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message->load('sender');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->message->chat_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id'              => $this->message->id,
                'chat_id'         => $this->message->chat_id,
                'sender_id'       => $this->message->sender_id,
                'sender'          => [
                    'id'   => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                ],
                'contenido'       => $this->message->contenido,
                'type'            => $this->message->type ?? 'text',
                'metadata'        => $this->message->metadata,
                'attachment_path' => $this->message->attachment_path,
                'attachment_name' => $this->message->attachment_name,
                'attachment_type' => $this->message->attachment_type,
                'created_at'      => $this->message->created_at,
            ],
        ];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
