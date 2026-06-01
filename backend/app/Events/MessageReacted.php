<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Message $message,
        public int     $userId,
        public string  $reaction,
        public string  $action,   // 'added' ou 'removed'
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->message->conversation_id}")];
    }

    public function broadcastAs(): string { return 'message.reacted'; }

    public function broadcastWith(): array
    {
        return [
            'message_id'      => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id'         => $this->userId,
            'reaction'        => $this->reaction,
            'action'          => $this->action,
        ];
    }
}