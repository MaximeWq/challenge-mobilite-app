<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Utilisateur;
use App\Models\Equipe;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $utilisateur = Utilisateur::where('email', $request->email)->first();

        if (!$utilisateur || !Hash::check($request->password, $utilisateur->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        $token = $utilisateur->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->load('equipe'),
                'token' => $token,
            ],
            'meta' => null
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs',
            'password' => 'required|string|min:8|confirmed',
            'equipe_id' => 'required|exists:equipes,id',
        ]);

        $utilisateur = Utilisateur::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'equipe_id' => $request->equipe_id,
        ]);

        $token = $utilisateur->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->load('equipe'),
                'token' => $token,
            ],
            'meta' => null
        ], 201);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $request->user()->load('equipe'),
            ],
            'meta' => null
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Déconnexion réussie',
            ],
            'meta' => null
        ]);
    }

    public function getAllUsers(Request $request)
    {
        $utilisateurs = Utilisateur::with('equipe')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $utilisateurs->items(),
            'meta' => [
                'total' => $utilisateurs->total(),
                'page' => $utilisateurs->currentPage(),
                'per_page' => $utilisateurs->perPage(),
                'last_page' => $utilisateurs->lastPage(),
            ]
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $id,
            'equipe_id' => 'sometimes|exists:equipes,id',
            'is_admin' => 'sometimes|boolean',
        ]);

        $utilisateur->update($request->only(['nom', 'email', 'equipe_id', 'is_admin']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->load('equipe'),
            ],
            'meta' => null
        ]);
    }

    public function deleteUser($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        if ($utilisateur->is_admin && Utilisateur::where('is_admin', true)->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer le dernier administrateur',
            ], 400);
        }
        $utilisateur->delete();
        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Utilisateur supprimé avec succès',
            ],
            'meta' => null
        ]);
    }
}