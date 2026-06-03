<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MessageReactionController;
use App\Http\Controllers\Api\GroupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

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

    // Users
    Route::get('/users', [UserController::class, 'index']);

    // Groups
    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store']);
        Route::get('/{id}', [GroupController::class, 'show']);
        Route::put('/{id}', [GroupController::class, 'update']);
        Route::delete('/{id}', [GroupController::class, 'destroy']);
        Route::post('/{id}/members', [GroupController::class, 'addMembers']);
        Route::delete('/{id}/members/{userId}', [GroupController::class, 'removeMember']);
        Route::post('/{id}/members/{userId}/promote', [GroupController::class, 'promoteToAdmin']);
        Route::post('/{id}/members/{userId}/demote', [GroupController::class, 'demoteFromAdmin']);
        Route::post('/{id}/leave', [GroupController::class, 'leave']);
    });

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

    // Statuts

    Route::prefix('statuses')->group(function () {
          Route::get('/', [StatusController::class, 'index']);
          Route::get('/my', [StatusController::class, 'myStatuses']);
          Route::post('/', [StatusController::class, 'store']);
          Route::get('/{id}', [StatusController::class, 'show']);
          Route::post('/{id}/view', [StatusController::class, 'markAsViewed']);
          Route::delete('/{id}', [StatusController::class, 'destroy']);
      });



       // Conversations
    Route::get('/conversations',        [ConversationController::class, 'index']);
    Route::post('/conversations',       [ConversationController::class, 'store']);
    Route::delete('/conversations/{id}',[ConversationController::class, 'destroy']);
    Route::patch('/conversations/{id}/status', [ConversationController::class, 'updateStatus']);
    Route::post('/conversations/{id}/read', [MessageController::class, 'markAsRead']);


    // Messages
    Route::get('/conversations/{id}/messages',  [MessageController::class, 'index']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{id}/upload',   [MessageController::class, 'upload']);
    Route::post('/conversations/{id}/typing',   [MessageController::class, 'typing']);
    Route::post('/messages/{id}/reactions',    [MessageReactionController::class, 'store']);
    Route::delete('/messages/{id}/reactions',  [MessageReactionController::class, 'destroy']);
    Route::post('/conversations/{id}/messages/{msgId}/view', [MessageController::class, 'viewOnce']);


});
