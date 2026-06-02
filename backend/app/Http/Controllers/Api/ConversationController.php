<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // ─── Lister toutes les conversations de l'user connecté ──
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $conversations = Conversation::with([
            'userOne.profile',
            'userTwo.profile',
            'lastMessage',
        ])
        ->where('user_one', $user->id)
        ->orWhere('user_two', $user->id)
        ->orderByDesc('updated_at')
        ->get();

        return response()->json([
            'conversations' => ConversationResource::collection($conversations),
        ]);
    }

    // ─── Créer ou retrouver une conversation privée ───────────
    public function store(StoreConversationRequest $request): JsonResponse
    {
        $user       = auth()->user();
        $receiverId = $request->receiver_id;

        // Vérifier qu'on ne se parle pas à soi-même
        if ($receiverId === $user->id) {
            return response()->json(['message' => 'Action invalide.'], 422);
        }

        // Vérifier si une conversation existe déjà
        $existing = Conversation::where(function ($q) use ($user, $receiverId) {
            $q->where('user_one', $user->id)->where('user_two', $receiverId);
        })->orWhere(function ($q) use ($user, $receiverId) {
            $q->where('user_one', $receiverId)->where('user_two', $user->id);
        })->with(['userOne.profile', 'userTwo.profile', 'lastMessage'])
          ->first();

        if ($existing) {
            return response()->json([
                'conversation' => new ConversationResource($existing),
                'created'      => false,
            ]);
        }

        // Créer la nouvelle conversation
        $conversation = Conversation::create([
            'user_one'   => $user->id,
            'user_two'   => $receiverId,
        ]);

        // Créer une demande de contact en "pending"
        FriendRequest::firstOrCreate(
            ['sender_id' => $user->id, 'receiver_id' => $receiverId],
            ['status'    => 'pending']
        );

        $conversation->load(['userOne.profile', 'userTwo.profile', 'lastMessage']);

        return response()->json([
            'conversation' => new ConversationResource($conversation),
            'created'      => true,
        ], 201);
    }

    // ─── Voir une conversation ────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $conversation = Conversation::with([
            'userOne.profile',
            'userTwo.profile',
            'lastMessage',
        ])->findOrFail($id);

        abort_if(
            $conversation->user_one !== $user->id &&
            $conversation->user_two !== $user->id,
            403, 'Accès refusé.'
        );

        return response()->json([
            'conversation' => new ConversationResource($conversation),
        ]);
    }

    // ─── Accepter / refuser / bloquer ────────────────────────
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:accepted,refused,blocked'],
        ]);

        $user = auth()->user();

        // Trouver la demande entre les deux participants
        $conversation = Conversation::findOrFail($id);

        abort_if(
            $conversation->user_one !== $user->id &&
            $conversation->user_two !== $user->id,
            403
        );

        $otherUserId = $conversation->user_one === $user->id
            ? $conversation->user_two
            : $conversation->user_one;

        // Mettre à jour le statut dans friend_requests
        $friendRequest = FriendRequest::where(function ($q) use ($user, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $otherUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
        })->first();

        if ($friendRequest) {
            $friendRequest->update(['status' => $request->status]);
        }

        $label = match($request->status) {
            'accepted' => 'Conversation acceptée.',
            'refused'  => 'Conversation refusée.',
            'blocked'  => 'Utilisateur bloqué.',
        };

        return response()->json(['message' => $label]);
    }

    // ─── Supprimer une conversation ───────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $user         = auth()->user();
        $conversation = Conversation::findOrFail($id);

        abort_if(
            $conversation->user_one !== $user->id &&
            $conversation->user_two !== $user->id,
            403
        );

        $conversation->delete();

        return response()->json(['message' => 'Conversation supprimée.']);
    }
}