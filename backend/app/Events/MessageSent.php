<?php
namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public User    $sender,
    ) {}

    // Canal privé entre les 2 participants
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
        ];
    }

    // Nom de l'event côté frontend
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    // Données envoyées au frontend
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'receiver_id'     => $this->message->receiver_id,
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