<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a user is typing in a channel.
 * Clients show a "X is typing…" indicator while this event fires.
 */
class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $channelId,
        public User $user,
        public bool $isTyping
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('channel.' . $this->channelId)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->user->id,
            'user_name' => $this->user->name,
            'is_typing' => $this->isTyping,
        ];
    }
}
