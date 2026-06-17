<?php
namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\MessageRead;

use App\Events\MessageUpdated;
use App\Events\MessageDeleted;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // ─── Lister les messages d'une conversation ───────────────
    public function index(int $conversationId): JsonResponse
    {
        $user = auth()->user();
        $conv = $this->getConversation($conversationId, $user->id);

        $otherUserId = $conv->user_one === $user->id ? $conv->user_two : $conv->user_one;

        // Check if current user has blocked the other user
        $hasBlockedOther = \App\Models\BlockedUser::where([
            'blocker_id' => $user->id,
            'blocked_user_id' => $otherUserId,
        ])->exists();

        $messagesQuery = Message::with(['sender.profile', 'reactions.user', 'replyTo'])
            ->where('conversation_id', $conversationId);

        // If user has blocked the other, don't show messages from the blocked user
        if ($hasBlockedOther) {
            $messagesQuery->where('sender_id', $user->id);
        }

        $messages = $messagesQuery->orderBy('created_at')->paginate(50);

        // Marquer les messages reçus comme "vus"
        Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->update(['is_seen' => true, 'seen_at' => now()]);

        return response()->json([
            'messages' => MessageResource::collection($messages->items()),
            'meta'     => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    // ─── Envoyer un message texte ─────────────────────────────
    public function store(StoreMessageRequest $request, int $conversationId): JsonResponse
    {
        $user = auth()->user();
        $conv = $this->getConversation($conversationId, $user->id);

        // Vérifier que la conv n'est pas bloquée (only if user has blocked other)
        $this->checkNotBlocked($conv, $user->id);

        $otherUserId = $conv->user_one === $user->id ? $conv->user_two : $conv->user_one;

        // Check if friend request is accepted before saving message
        $friendRequest = \App\Models\FriendRequest::where(function($q) use ($user, $otherUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
        })->orWhere(function($q) use ($user, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
        })->first();

        // Only save message if friend request is accepted or doesn't exist (assumed accepted)
        if ($friendRequest && $friendRequest->status !== 'accepted') {
            // Return a response but don't save the message
            return response()->json([
                'message' => [
                    'id' => null,
                    'conversation_id' => $conversationId,
                    'sender_id' => $user->id,
                    'receiver_id' => $otherUserId,
                    'message' => $request->message,
                    'type' => $request->type,
                    'is_seen' => false,
                    'created_at' => now()->toISOString(),
                    'sender' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'profile' => $user->profile,
                    ],
                ],
            ], 200);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'receiver_id'     => $otherUserId,
            'message'         => $request->message,
            'type'            => $request->type,
            'is_single_view'  => $request->is_single_view ?? false,
            'reply_to_id'     => $request->reply_to_id,
        ]);

        $conv->update([
            'last_message_id' => $message->id,
            'updated_at'      => now(),
        ]);

        $message->load(['sender.profile', 'reactions.user', 'replyTo']);

        // 🔴 WebSocket — envoyer aux autres participants
        broadcast(new MessageSent($message, $user))->toOthers();

        return response()->json([
            'message' => new MessageResource($message),
        ], 201);
    }

    // ─── Upload d'un fichier média ────────────────────────────
    // ─── Supprimer un message ─────────────────────────────────

    // ─── Indicateur "est en train d'écrire" ───────────────────
    public function typing(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        broadcast(new UserTyping(
            conversationId: $conversationId,
            userId:         auth()->id(),
            isTyping:       $request->is_typing,
        ))->toOthers();

        return response()->json(['ok' => true]);
    }

    // ─── Helpers privés ───────────────────────────────────────
    private function getConversation(int $convId, int $userId): Conversation
    {
        $conv = Conversation::findOrFail($convId);

        if ($conv->user_one != $userId && $conv->user_two != $userId) {
            abort(403, "Accès refusé. Vous (ID: $userId) ne faites pas partie de cette conversation (ID: $convId) entre {$conv->user_one} et {$conv->user_two}.");
        }

        return $conv;
    }

    private function checkNotBlocked(Conversation $conv, int $userId): void
    {
        $otherUserId = $conv->user_one == $userId
            ? $conv->user_two
            : $conv->user_one;

        // Check if current user has blocked the other user (then they can't send messages)
        // If the other user has blocked current user, current user can still send messages
        $isBlocked = \App\Models\BlockedUser::where([
            'blocker_id' => $userId,
            'blocked_user_id' => $otherUserId,
        ])->exists();

        abort_if($isBlocked, 403, 'Cette conversation est bloquée.');
    }



    // Ajouter cette méthode dans MessageController

    public function markAsRead(int $conversationId): JsonResponse
    {
        $user = auth()->user();
        $this->getConversation($conversationId, $user->id);

        $messageIds = Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->pluck('id')
            ->toArray();

        if (empty($messageIds)) {
            return response()->json(['ok' => true]);
        }

        // Marquer comme lus
        Message::whereIn('id', $messageIds)->update([
            'is_seen' => true,
            'seen_at' => now(),
        ]);

        // 🔴 Notifier l'expéditeur via WebSocket
        broadcast(new MessageRead(
            conversationId: $conversationId,
            readerId:       $user->id,
            messageIds:     $messageIds,
        ))->toOthers();

        return response()->json([
            'ok'          => true,
            'message_ids' => $messageIds,
        ]);
    }



// Ajouter cette méthode dans MessageController

public function viewOnce(int $conversationId, int $messageId): JsonResponse
{
    $user    = auth()->user();
    $message = Message::where('conversation_id', $conversationId)
                      ->where('receiver_id', $user->id)
                      ->where('is_single_view', true)
                      ->findOrFail($messageId);

    // Supprimer le fichier après lecture
    if ($message->file_url) {
        \Storage::disk('public')->delete($message->file_url);
    }

    // Marquer comme supprimé après la vue
    $message->update([
        'is_deleted' => true,
        'file_url'   => null,
        'message'    => null,
    ]);
    $message->delete();

    return response()->json(['ok' => true]);
}



// ─── Modifier un message ──────────────────────────────
public function update(Request $request, int $conversationId, int $messageId): JsonResponse
{
    $request->validate([
        'message' => ['required', 'string', 'max:5000'],
    ]);

    $user    = auth()->user();
    $message = Message::where('conversation_id', $conversationId)
                      ->where('sender_id', $user->id)   // seul l'auteur peut modifier
                      ->where('type', 'text')            // on ne modifie que les textes
                      ->findOrFail($messageId);

    $message->update([
        'message' => $request->message,
    ]);

    // 🔴 WebSocket
    broadcast(new MessageUpdated($message))->toOthers();

    return response()->json([
        'message' => new MessageResource($message),
    ]);
}

// ─── Supprimer un message ─────────────────────────────
public function destroy(int $conversationId, int $messageId): JsonResponse
{
    $user    = auth()->user();
    $message = Message::where('conversation_id', $conversationId)
                      ->where('sender_id', $user->id)
                      ->findOrFail($messageId);

    // Supprimer le fichier si média
    if ($message->file_url) {
        Storage::disk('public')->delete($message->file_url);
    }

    $message->update([
        'message'    => null,
        'file_url'   => null,
    ]);
    $message->delete(); // soft delete

    // 🔴 WebSocket
    broadcast(new MessageDeleted($messageId, $conversationId))->toOthers();

    return response()->json(['message' => 'Message supprimé.']);
}

// ─── Upload fichier(s) ────────────────────────────────
    public function upload(Request $request, int $conversationId): JsonResponse
    {

        $request->validate([
            'file'           => ['required', 'file', 'max:51200'],  // 50MB
            'is_single_view' => ['boolean'],
        ]);

        $user = auth()->user();
        $conv = $this->getConversation($conversationId, $user->id);
        $this->checkNotBlocked($conv, $user->id);

        $otherUserId = $conv->user_one === $user->id ? $conv->user_two : $conv->user_one;

        // Check if friend request is accepted before saving message
        $friendRequest = \App\Models\FriendRequest::where(function($q) use ($user, $otherUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
        })->orWhere(function($q) use ($user, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
        })->first();

        // Only save message if friend request is accepted or doesn't exist (assumed accepted)
        if ($friendRequest && $friendRequest->status !== 'accepted') {
            return response()->json([
                'message' => 'Invitation not accepted yet',
            ], 403);
        }

        $file = $request->file('file');
        $mime = $file->getMimeType();

        // Déterminer le type automatiquement
        $type = match(true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            default                          => 'file',
        };

        $path = $file->store("conversations/{$conversationId}", 'public');

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'receiver_id'     => $otherUserId,
            'message'         => $file->getClientOriginalName(),
            'type'            => $type,
            'file_url'        => $path,
            'is_single_view'  => $request->is_single_view ?? false,
        ]);

        $conv->update([
            'last_message_id' => $message->id,
            'updated_at'      => now(),
        ]);

        $message->load(['sender.profile', 'reactions']);

        broadcast(new MessageSent($message, $user))->toOthers();

        return response()->json([
            'message' => new MessageResource($message),
        ], 201);
    }



}