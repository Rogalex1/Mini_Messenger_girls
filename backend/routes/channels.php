<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

// Canal privé d'une conversation
// Seuls les 2 participants peuvent s'y abonner
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) return false;

    return $conversation->user_one === $user->id
        || $conversation->user_two === $user->id;
});

// Canal presence = liste des utilisateurs en ligne
Broadcast::channel('online-users', function ($user) {
    return [
        'id'       => $user->id,
        'username' => $user->username,
        'avatar'   => $user->profile?->profile_photo,
    ];
});