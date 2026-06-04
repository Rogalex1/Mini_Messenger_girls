<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\FriendRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs (pour ajouter des membres à un groupe par exemple)
     */
    public function index(Request $request)
    {
        $query = User::with('profile')->where('id', '!=', Auth::id());

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(20)->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }
}
     * Récupère la liste des utilisateurs qui ne sont pas encore en conversation.
     */
    public function getAllUsers(): JsonResponse
    {
        $currentUserId = Auth::id();

        // Récupérer les IDs des utilisateurs avec qui on a déjà une conversation
        $existingConversationUserIds = Conversation::where('user_one', $currentUserId)
            ->pluck('user_two')
            ->merge(
                Conversation::where('user_two', $currentUserId)->pluck('user_one')
            )
            ->unique();

        // Exclure soi-même, l'admin (role_id=1) et les utilisateurs déjà en conversation
        $users = User::where('id', '!=', $currentUserId)
            ->where('role_id', '!=', 1)
            ->whereNotIn('id', $existingConversationUserIds)
            ->with('profile')
            ->get();

        return response()->json([
            'success' => true,
            'users'   => UserResource::collection($users)
        ]);
    }

    /**
     * Récupère la liste des amis de l'utilisateur.
     */
    public function getFriends(): JsonResponse
    {
        $user = Auth::user();
        $conversations = Conversation::where('user_one', $user->id)
            ->orWhere('user_two', $user->id)
            ->with(['userOne.profile', 'userTwo.profile'])
            ->get();

        $friends = $conversations->map(function ($conv) use ($user) {
            return $conv->user_one === $user->id ? $conv->userTwo : $conv->userOne;
        })->unique('id')->values();

        return response()->json([
            'success' => true,
            'friends' => UserResource::collection($friends)
        ]);
    }

    /**
     * Récupère la liste des demandes d'amis reçues.
     */
    public function getFriendRequests(): JsonResponse
    {
        $user = Auth::user();
        $requests = FriendRequest::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender.profile')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests->map(function($req) {
                return [
                    'id' => $req->id,
                    'user' => new UserResource($req->sender),
                    'created_at' => $req->created_at
                ];
            })
        ]);
    }

    /**
     * Accepter ou refuser une demande d'ami.
     */
    public function handleFriendRequest(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $friendRequest = \App\Models\FriendRequest::where('id', $id)
            ->where('receiver_id', $user->id)
            ->firstOrFail();

        $action = $request->action; // 'accepted' or 'rejected'

        if ($action === 'accepted') {
            $friendRequest->update(['status' => 'accepted']);
            // La conversation est déjà créée lors de l'envoi de la demande dans ConversationController@store
            return response()->json(['success' => true, 'message' => 'Demande acceptée !']);
        } else {
            $friendRequest->delete();
            return response()->json(['success' => true, 'message' => 'Demande refusée.']);
        }
    }
}
