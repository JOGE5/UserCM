<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublicationReported implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $publicacionId;
    public $message;
    public $ownerId;

    public function __construct($publicacionId, $ownerId, $message)
    {
        $this->publicacionId = $publicacionId;
        $this->ownerId = $ownerId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->ownerId);
    }

    public function broadcastWith()
    {
        return [
            'publicacion_id' => $this->publicacionId,
            'message' => $this->message,
        ];
    }
}
