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

        // Get friend request
        $friendRequest = \App\Models\FriendRequest::where(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $user->id)->where('receiver_id', $otherUser->id);
            })->orWhere(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $otherUser->id)->where('receiver_id', $user->id);
            })->first();

        // Determine status
        $status = 'accepted';
        if ($friendRequest) {
            if ($friendRequest->status === 'pending') {
                // If current user is the receiver, show pending
                if ($friendRequest->receiver_id === $user->id) {
                    $status = 'pending';
                }
                // If current user is the sender, show accepted (or nothing)
            } else {
                $status = $friendRequest->status;
            }
        }

        // Check if blocked
        $hasBlockedOther = \App\Models\BlockedUser::where([
            'blocker_id' => $user->id,
            'blocked_user_id' => $otherUser->id,
        ])->exists();

        // Get last message, but only from user if they've blocked the other
        $lastMessage = null;
        if ($this->lastMessage) {
            if (!$hasBlockedOther || $this->lastMessage->sender_id === $user->id) {
                $lastMessage = [
                    'id' => $this->lastMessage->id,
                    'message' => $this->lastMessage->is_deleted ? 'Message supprimé' : $this->lastMessage->message,
                    'type' => $this->lastMessage->type,
                    'sender_id' => $this->lastMessage->sender_id,
                    'is_seen' => $this->lastMessage->is_seen,
                    'created_at' => $this->lastMessage->created_at?->toISOString(),
                ];
            }
        }

        // Messages non lus
        $unreadCount = 0;
        if (!$hasBlockedOther) {
            $unreadCount = \App\Models\Message::where('conversation_id', $this->id)
                ->where('receiver_id', $user->id)
                ->where('is_seen', false)
                ->count();
        }

        return [
            'id'           => $this->id,
            'status'       => $status,
            'unread_count' => $unreadCount,
            'last_message' => $lastMessage,
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