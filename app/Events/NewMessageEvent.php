<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender_user_id;
    public $sender_name;
    public $receiver_id;
    public $receiver_name;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($sender_user_id, $sender_name, $receiver_id, $receiver_name, $message)
    {
        $this->sender_user_id = $sender_user_id;
        $this->sender_name = $sender_name;
        $this->receiver_id = $receiver_id;
        $this->receiver_name = $receiver_name;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('show-notification');
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

}
