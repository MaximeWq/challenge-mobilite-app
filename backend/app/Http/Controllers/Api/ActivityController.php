<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activite;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Liste paginée des activités avec utilisateur et équipe associés.
     */
    public function index(Request $request)
    {
        $activites = Activite::with(['utilisateur', 'utilisateur.equipe'])
            ->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $activites->items(),
            'meta' => [
                'total' => $activites->total(),
                'page' => $activites->currentPage(),
                'per_page' => $activites->perPage(),
                'last_page' => $activites->lastPage(),
            ]
        ]);
    }

    /**
     * Enregistre une nouvelle activité pour l'utilisateur connecté.
     * Règle métier : un utilisateur ne peut déclarer qu'une seule activité par jour.
     * - Si une activité existe déjà pour la date donnée, retourne une erreur.
     * - Conversion automatique des pas en km pour la marche/course.
     */
    public function store(Request $request)
    {
        // Validation des données reçues
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|in:velo,marche_course',
            'distance_km' => 'required_if:type,velo|nullable|numeric|min:0',
            'pas' => 'required_if:type,marche_course|nullable|integer|min:0',
        ]);

        $utilisateur = Auth::user();

        // Vérifie si une activité existe déjà pour ce jour et cet utilisateur
        $existingActivity = Activite::where('utilisateur_id', $utilisateur->id)
            ->where('date', $request->date)
            ->first();

        if ($existingActivity) {
            // Règle métier : une seule activité par jour
            return response()->json([
                'status' => 'error',
                'message' => 'Une activité existe déjà pour cette date',
            ], 400);
        }

        if (Carbon::parse($request->date)->isAfter(Carbon::today())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de déclarer une activité pour une date future',
            ], 400);
        }

        // Prépare les données à enregistrer
        $data = [
            'utilisateur_id' => $utilisateur->id,
            'date' => $request->date,
            'type' => $request->type,
        ];

        // Gestion du type d'activité
        if ($request->type === 'velo') {
            $data['distance_km'] = $request->distance_km;
            $data['pas'] = null;
        } else {
            $data['pas'] = $request->pas;
            // Conversion automatique des pas en km
            $data['distance_km'] = round($request->pas / 1500, 2);
        }

        $activite = Activite::create($data);

        return response()->json([
            'status' => 'success',
            'data' => [
                'activite' => $activite->load(['utilisateur', 'utilisateur.equipe']),
            ],
            'meta' => null
        ], 201);
    }

    /**
     * Affiche une activité spécifique (accès restreint à l'utilisateur ou à l'admin).
     */
    public function show($id)
    {
        $activite = Activite::with(['utilisateur', 'utilisateur.equipe'])->findOrFail($id);

        // Seul l'utilisateur propriétaire ou un admin peut voir l'activité
        if ($activite->utilisateur_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès non autorisé à cette activité',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'activite' => $activite,
            ],
            'meta' => null
        ]);
    }

    /**
     * Met à jour une activité (seulement le jour même, accès restreint).
     */
    public function update(Request $request, $id)
    {
        $activite = Activite::findOrFail($id);

        // Seul l'utilisateur propriétaire ou un admin peut modifier
        if ($activite->utilisateur_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès non autorisé à cette activité',
            ], 403);
        }

        // Impossible de modifier une activité des jours précédents
        if (!Carbon::parse($activite->date)->isToday()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de modifier une activité des jours précédents',
            ], 400);
        }

        $request->validate([
            'type' => 'sometimes|in:velo,marche_course',
            'distance_km' => 'required_if:type,velo|nullable|numeric|min:0',
            'pas' => 'required_if:type,marche_course|nullable|integer|min:0',
        ]);

        $data = [];

        // Mise à jour des champs selon le type
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }

        if ($request->type === 'velo' || ($activite->type === 'velo' && !$request->has('type'))) {
            if ($request->has('distance_km')) {
                $data['distance_km'] = $request->distance_km;
                $data['pas'] = null;
            }
        
            if ($request->has('pas')) {
                $data['pas'] = $request->pas;
                $data['distance_km'] = round($request->pas / 1500, 2);
            }
        }

        $activite->update($data);

        return response()->json([
            'status' => 'success',
            'data' => [
                'activite' => $activite->load(['utilisateur', 'utilisateur.equipe']),
            ],
            'meta' => null
        ]);
    }

    /**
     * Supprime une activité (accès restreint à l'utilisateur ou à l'admin).
     */
    public function destroy($id)
    {
        $activite = Activite::findOrFail($id);

        if ($activite->utilisateur_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès non autorisé à cette activité',
            ], 403);
        }

        $activite->delete();

        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Activité supprimée avec succès',
            ],
            'meta' => null
        ]);
    }
}