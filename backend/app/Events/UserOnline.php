<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public bool $isOnline,
    ) {}

    // Presence channel = tout le monde voit qui est en ligne
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('online-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.status';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->user->id,
            'username'  => $this->user->username,
            'is_online' => $this->isOnline,
            'last_seen' => $this->user->last_seen?->toISOString(),
        ];
    }
}