<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\GroupMessageSent;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class GroupMessageController extends Controller
{
    // ─── Lister les messages du groupe ───────────────────────
    public function index(int $groupId)
    {
        $group = Group::findOrFail($groupId);

        if (!$group->members->contains(Auth::id())) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $messages = GroupMessage::with('sender.profile')
            ->where('group_id', $groupId)
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json([
            'messages' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    // ─── Envoyer un message dans le groupe ───────────────────
    public function store(Request $request, int $groupId)
    {
        $group = Group::findOrFail($groupId);

        if (!$group->members->contains(Auth::id())) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $request->validate([
            'message' => 'required|string',
            'type'    => 'nullable|string|in:text,image,file',
        ]);

        $message = GroupMessage::create([
            'group_id'  => $groupId,
            'sender_id' => Auth::id(),
            'message'   => $request->message,
            'type'      => $request->type ?? 'text',
        ]);

        $message->load('sender.profile');

        broadcast(new GroupMessageSent($message, Auth::user()))->toOthers();

        return response()->json(['message' => $message], 201);
    }
}
