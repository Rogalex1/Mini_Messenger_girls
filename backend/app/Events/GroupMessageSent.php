<?php
namespace App\Events;

use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcastNow //permet que le message soit instantanée
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GroupMessage $message,
        public User    $sender,
    ) {}

    // Canal privé des  participants(gere aussi la sécurité)
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("group.{$this->message->group_id}"),
        ];
    }

    // Nom de l'event côté frontend
    public function broadcastAs(): string
    {
        return 'group.message.sent';
    }

    // Données envoyées au frontend
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'group_id' => $this->message->group_id,
            'sender_id'       => $this->message->sender_id,
            'message'         => $this->message->message,
            'type'            => $this->message->type,
            'file_url'        => $this->message->file_url,
            'is_seen'         => $this->message->is_seen,
            'created_at'      => $this->message->created_at->toISOString(),
            'sender' => [
                'id'       => $this->sender->id,
                'username' => $this->sender->username,
                'avatar'   => $this->sender->profile?->profile_photo,
            ],
        ];
    }
}