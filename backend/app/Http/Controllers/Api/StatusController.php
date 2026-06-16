<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\StatusView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
   

    public function index()
    {
        $userId = auth()->id();

        // Récupérer les utilisateurs qui ont des statuts actifs
        $users = \App\Models\User::whereHas('statuses', function ($query) {
            $query->active();
        })
        ->with(['profile', 'statuses' => function ($query) {
            $query->active()->orderBy('created_at', 'asc');
        }, 'statuses.views' => function ($query) use ($userId) {
            $query->where('viewer_id', $userId);
        }])
        ->where('id', '!=', $userId)
        ->get();

        // Ajouter un flag has_unviewed et trier
        $users = $users->map(function ($user) use ($userId) {
            $hasUnviewed = $user->statuses->some(function ($status) {
                return $status->views->isEmpty();
            });
            $user->has_unviewed = $hasUnviewed;
            return $user;
        })->sortByDesc('has_unviewed')->values();

        return response()->json($users);
    }

    public function userStatuses($userId)
    {
        $user = \App\Models\User::with(['profile', 'statuses' => function ($query) {
            $query->active()->orderBy('created_at', 'asc');
        }])->findOrFail($userId);

        // Marquer automatiquement tous les statuts comme vus
        foreach ($user->statuses as $status) {
            if ($status->user_id !== auth()->id()) {
                StatusView::firstOrCreate(
                    ['status_id' => $status->id, 'viewer_id' => auth()->id()],
                    ['viewed_at' => now()]
                );
            }
        }

        return response()->json($user);
    }

    public function myStatuses()
    {
        $statuses = Status::where('user_id', auth()->id())
            ->with('views.viewer.profile')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($statuses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:text,image,video',
            'caption' => 'nullable|string|max:500',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'user_id' => auth()->id(),
            'type' => $request->type,
            'caption' => $request->caption,
            'expires_at' => now()->addHours(24),
        ];

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('statuses', 'public');
            $data['media_url'] = Storage::url($path);
        }

        $status = Status::create($data);
        $status->load('user.profile');

        return response()->json($status, 201);
    }

    public function show($id)
    {
        $status = Status::with('user.profile', 'views.viewer.profile')->findOrFail($id);
        return response()->json($status);
    }

    public function markAsViewed($id)
    {
        $status = Status::findOrFail($id);

        if ($status->user_id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas voir votre propre statut'], 403);
        }

        $view = StatusView::firstOrCreate(
            ['status_id' => $id, 'viewer_id' => auth()->id()],
            ['viewed_at' => now()]
        );

        return response()->json(['message' => 'Statut marqué comme vu', 'view' => $view]);
    }

    public function destroy($id)
    {
        $status = Status::where('user_id', auth()->id())->findOrFail($id);

        if ($status->media_url) {
            $path = str_replace('/storage/', '', $status->media_url);
            Storage::disk('public')->delete($path);
        }

        $status->delete();
        return response()->json(['message' => 'Statut supprimé']);
    }
}

