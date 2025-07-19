<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\StatsController;

// Route de test pour vérifier que l'API répond
Route::get('/test', function() { return response()->json(['test' => 'ok']); });

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Ce fichier définit toutes les routes de l'API REST de l'application.
| Les routes sont organisées par fonctionnalité et protégées par middleware.
*/

// Routes d'authentification (publiques)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Statistiques générales du challenge (publique)
Route::get('/stats/general', [StatsController::class, 'general']);

// Routes protégées (nécessitent authentification via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Gestion des activités (CRUD)
    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/user/{id}', [ActivityController::class, 'getUserActivities']);
        Route::get('/{id}', [ActivityController::class, 'show']);
        Route::put('/{id}', [ActivityController::class, 'update']);
        Route::delete('/{id}', [ActivityController::class, 'destroy']);
    });

    // Statistiques et classements (protégés)
    Route::prefix('stats')->group(function () {
        Route::get('/general', [StatsController::class, 'general']);
        Route::get('/teams', [StatsController::class, 'teams']);
        Route::get('/users', [StatsController::class, 'users']);
        Route::get('/personal', [StatsController::class, 'personal']);
        Route::get('/export', [StatsController::class, 'export']);
    });

    // Routes admin (gestion des utilisateurs, protégées par le middleware 'admin')
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AuthController::class, 'getAllUsers']);
        Route::put('/admin/users/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/admin/users/{id}', [AuthController::class, 'deleteUser']);
    });
});