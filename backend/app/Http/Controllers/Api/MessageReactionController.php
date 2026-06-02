<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageReactionController extends Controller
{
    // Ajouter ou changer sa réaction
    public function store(Request $request, int $messageId): JsonResponse
    {
        $request->validate([
            'reaction' => ['required', 'string', 'max:20'],
        ]);

        $user    = auth()->user();
        $message = Message::findOrFail($messageId);

        // Upsert — une seule réaction par user par message
        $reaction = MessageReaction::updateOrCreate(
            ['message_id' => $messageId, 'user_id' => $user->id],
            ['reaction'   => $request->reaction],
        );

        // Broadcast aux autres participants
        broadcast(new \App\Events\MessageReacted(
            message:   $message,
            userId:    $user->id,
            reaction:  $request->reaction,
            action:    'added',
        ))->toOthers();

        return response()->json(['reaction' => $reaction], 201);
    }

    // Supprimer sa réaction
    public function destroy(int $messageId): JsonResponse
    {
        $user = auth()->user();

        MessageReaction::where('message_id', $messageId)
            ->where('user_id', $user->id)
            ->delete();

        $message = Message::findOrFail($messageId);

        broadcast(new \App\Events\MessageReacted(
            message:  $message,
            userId:   $user->id,
            reaction: '',
            action:   'removed',
        ))->toOthers();

        return response()->json(['ok' => true]);
    }
}