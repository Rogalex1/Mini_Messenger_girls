<?php

use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn($req) => $req->user());
    Route::apiResource('/articles', ArticleController::class);
});