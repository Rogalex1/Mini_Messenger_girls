<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Affichage des groupes auxquels l'utilisateur appartient
     */
    public function index()
    {
        $groups = Auth::user()->groups()->with('creator')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }

    /**
     * Création d'un groupe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        $group = DB::transaction(function () use ($validated) {
            $groupData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'photo' => $validated['photo'] ?? null,
                'created_by' => Auth::id(),
            ];

            $group = Group::create($groupData);

            // Ajouter le créateur comme admin
            $group->members()->attach(Auth::id(), [
                'role' => 'admin',
                'joined_at' => now()
            ]);

            // Ajouter les autres membres si fournis
            if (!empty($validated['member_ids'])) {
                $members = array_unique($validated['member_ids']);
                // Filtrer pour ne pas ajouter le créateur deux fois
                $members = array_filter($members, fn($id) => $id != Auth::id());
                
                foreach ($members as $userId) {
                    $group->members()->attach($userId, [
                        'role' => 'member',
                        'joined_at' => now()
                    ]);
                }
            }

            return $group;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Groupe créé avec succès',
            'data' => $group->load(['members', 'creator'])
        ], 201);
    }

    /**
     * Affichage d'un groupe spécifique
     */
    public function show($id)
    {
        $group = Group::with(['members', 'creator'])->findOrFail($id);

        // Vérifier si l'utilisateur est membre
        if (!$group->members->contains(Auth::id())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas membre de ce groupe'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $group
        ]);
    }

    /**
     * Mise à jour d'un groupe
     */
    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        // Vérifier si l'utilisateur est admin du groupe
        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent modifier le groupe'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $group->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Groupe mis à jour avec succès',
            'data' => $group->load('members')
        ]);
    }

    /**
     * Suppression d'un groupe
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        // Vérifier si l'utilisateur est admin du groupe
        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent supprimer le groupe'
            ], 403);
        }

        $group->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Groupe supprimé avec succès'
        ]);
    }

    /**
     * Ajouter des membres au groupe
     */
    public function addMembers(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent ajouter des membres'
            ], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['user_ids'] as $userId) {
            if (!$group->members->contains($userId)) {
                $group->members()->attach($userId, [
                    'role' => 'member',
                    'joined_at' => now()
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Membres ajoutés avec succès',
            'data' => $group->load('members')
        ]);
    }

    /**
     * Supprimer un membre du groupe
     */
    public function removeMember($id, $userId)
    {
        $group = Group::findOrFail($id);

        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent supprimer des membres'
            ], 403);
        }

        // On ne peut pas supprimer le créateur ou soi-même (utiliser leave pour soi-même)
        if ($userId == Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas vous supprimer vous-même. Utilisez l\'option "Quitter le groupe".'
            ], 400);
        }

        $group->members()->detach($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'Membre supprimé avec succès',
            'data' => $group->load('members')
        ]);
    }

    /**
     * Promouvoir un membre en tant qu'administrateur
     */
    public function promoteToAdmin($id, $userId)
    {
        $group = Group::findOrFail($id);

        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent promouvoir d\'autres membres'
            ], 403);
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'admin']);

        return response()->json([
            'status' => 'success',
            'message' => 'Membre promu administrateur avec succès',
            'data' => $group->load('members')
        ]);
    }

    /**
     * Rétrograder un administrateur en membre simple
     */
    public function demoteFromAdmin($id, $userId)
    {
        $group = Group::findOrFail($id);

        if (!$this->isAdmin($group)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seuls les administrateurs peuvent rétrograder d\'autres administrateurs'
            ], 403);
        }

        // On ne peut pas rétrograder le créateur
        if ($userId == $group->created_by) {
            return response()->json([
                'status' => 'error',
                'message' => 'Le créateur du groupe ne peut pas être rétrogradé'
            ], 400);
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'member']);

        return response()->json([
            'status' => 'success',
            'message' => 'Administrateur rétrogradé avec succès',
            'data' => $group->load('members')
        ]);
    }

    /**
     * Quitter le groupe
     */
    public function leave($id)
    {
        $group = Group::findOrFail($id);

        // Si le créateur quitte, il doit promouvoir quelqu'un d'autre ou supprimer le groupe ?
        // Pour simplifier, on permet de quitter si on n'est pas le seul admin ou si on n'est pas le créateur
        if ($group->created_by == Auth::id()) {
             return response()->json([
                'status' => 'error',
                'message' => 'Le créateur ne peut pas quitter le groupe. Supprimez le groupe à la place.'
            ], 400);
        }

        $group->members()->detach(Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => 'Vous avez quitté le groupe'
        ]);
    }

    /**
     * Vérifier si l'utilisateur connecté est admin du groupe
     */
    private function isAdmin(Group $group)
    {
        return $group->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
