<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a new message is sent in a channel.
 * All members of the channel will receive this event in real time via Reverb.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        // Use a presence channel so we can track who is online
        return [new PresenceChannel('channel.' . $this->message->channel_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'body'            => $this->message->body,
            'attachment_url'  => $this->message->attachment_url,
            'attachment_type' => $this->message->attachment_type,
            'attachment_name' => $this->message->attachment_name,
            'created_at'      => $this->message->created_at->toISOString(),
            'user' => [
                'id'     => $this->message->user->id,
                'name'   => $this->message->user->name,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($this->message->user->name) . '&background=6366f1&color=fff',
            ],
        ];
    }
}
