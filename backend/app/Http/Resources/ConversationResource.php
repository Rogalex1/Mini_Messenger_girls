<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        $user      = auth()->user();
        $otherUser = $this->user_one === $user->id
            ? $this->userTwo
            : $this->userOne;

        // Statut de la conversation pour cet user
        $status = \App\Models\FriendRequest::where(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $user->id)->where('receiver_id', $otherUser->id);
            })->orWhere(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $otherUser->id)->where('receiver_id', $user->id);
            })->value('status');

        // Messages non lus
        $unreadCount = \App\Models\Message::where('conversation_id', $this->id)
            ->where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->count();

        return [
            'id'           => $this->id,
            'status'       => $status ?? 'accepted',
            'unread_count' => $unreadCount,
            'last_message' => $this->lastMessage ? [
                'id'         => $this->lastMessage->id,
                'message'    => $this->lastMessage->is_deleted
                                    ? 'Message supprimé'
                                    : $this->lastMessage->message,
                'type'       => $this->lastMessage->type,
                'sender_id'  => $this->lastMessage->sender_id,
                'is_seen'    => $this->lastMessage->is_seen,
                'created_at' => $this->lastMessage->created_at?->toISOString(),
            ] : null,
            'other_user'   => [
                'id'        => $otherUser->id,
                'username'  => $otherUser->username,
                'is_online' => $otherUser->is_online,
                'last_seen' => $otherUser->last_seen?->toISOString(),
                'profile'   => [
                    'first_name'    => $otherUser->profile?->first_name,
                    'last_name'     => $otherUser->profile?->last_name,
                    'profile_photo' => $otherUser->profile?->profile_photo
                                        ? asset('storage/' . $otherUser->profile->profile_photo)
                                        : null,
                ],
            ],
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}