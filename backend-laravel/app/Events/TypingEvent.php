<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $conversationId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user ,$conversationId)
    {
       $this->user = $user;
       $this->conversationId = $conversationId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        return new PresenceChannel('typing.'.$this->conversationId);
    }

    public function broadcastAs()
    {
        return 'typing';
    }
}
