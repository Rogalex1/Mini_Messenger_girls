<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\PostController;

// ─── Routes publiques ─────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ─── Routes protégées ────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Statuts


    // Publications
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/my', [PostController::class, 'myPosts']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::post('/{id}/like', [PostController::class, 'like']);
        Route::post('/{id}/comment', [PostController::class, 'comment']);
        Route::delete('/{postId}/comments/{commentId}', [PostController::class, 'deleteComment']);
    });
    Route::prefix('statuses')->group(function () {
          Route::get('/', [StatusController::class, 'index']);
          Route::get('/my', [StatusController::class, 'myStatuses']);
          Route::post('/', [StatusController::class, 'store']);
          Route::get('/{id}', [StatusController::class, 'show']);
          Route::post('/{id}/view', [StatusController::class, 'markAsViewed']);
          Route::delete('/{id}', [StatusController::class, 'destroy']);
      });
});
