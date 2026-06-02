<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'receiver_id'     => $this->receiver_id,
            'message'         => $this->is_deleted ? 'Message supprimé' : $this->message,
            'type'            => $this->type,
            'file_url'        => $this->file_url ? asset('storage/' . $this->file_url) : null,
            'is_seen'         => $this->is_seen,
            'seen_at'         => $this->seen_at?->toISOString(),
            'is_single_view'  => $this->is_single_view,
            'is_deleted'      => $this->is_deleted,
            'reply_to'        => $this->whenLoaded('replyTo', fn() => [
                'id'      => $this->replyTo->id,
                'message' => $this->replyTo->message,
                'type'    => $this->replyTo->type,
            ]),
            'reactions'       => $this->whenLoaded('reactions', fn() =>
                $this->reactions->map(fn($r) => [
                    'id'       => $r->id,
                    'reaction' => $r->reaction,
                    'user_id'  => $r->user_id,
                    'username' => $r->user?->username,
                ])
            ),
            'sender'          => [
                'id'       => $this->sender->id,
                'username' => $this->sender->username,
                'avatar'   => $this->sender->profile?->profile_photo
                                ? asset('storage/' . $this->sender->profile->profile_photo)
                                : null,
            ],
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}