<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ─── Register ────────────────────────────────────
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'role_id'  => 2,  // user par défaut
            'username' => $request->username,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Créer le profil vide automatiquement
        $user->profile()->create([]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => new UserResource($user->load('profile', 'role')),
            'token'   => $token,
        ], 201);
    }

    // ─── Login ───────────────────────────────────────
    public function login(LoginRequest $request): JsonResponse
    {
        // Vérifier les identifiants
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        $user = User::with('profile', 'role')
                    ->where('email', $request->email)
                    ->firstOrFail();

        // Mettre en ligne
        $user->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);

        // Révoquer les anciens tokens + créer un nouveau
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }

    // ─── Logout ──────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Mettre hors ligne
        $user->update([
            'is_online' => false,
            'last_seen' => now(),
        ]);

        // Révoquer le token courant
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    // ─── Me (utilisateur connecté) ───────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('profile', 'role')),
        ]);
    }
}