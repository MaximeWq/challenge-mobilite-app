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

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|in:velo,marche_course',
            'distance_km' => 'required_if:type,velo|nullable|numeric|min:0',
            'pas' => 'required_if:type,marche_course|nullable|integer|min:0',
        ]);

        $utilisateur = Auth::user();

        $existingActivity = Activite::where('utilisateur_id', $utilisateur->id)
            ->where('date', $request->date)
            ->first();

        if ($existingActivity) {
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

        $data = [
            'utilisateur_id' => $utilisateur->id,
            'date' => $request->date,
            'type' => $request->type,
        ];

        if ($request->type === 'velo') {
            $data['distance_km'] = $request->distance_km;
            $data['pas'] = null;
        } else {
            $data['pas'] = $request->pas;
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

    public function show($id)
    {
        $activite = Activite::with(['utilisateur', 'utilisateur.equipe'])->findOrFail($id);

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

    public function update(Request $request, $id)
    {
        $activite = Activite::findOrFail($id);

        if ($activite->utilisateur_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès non autorisé à cette activité',
            ], 403);
        }

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

        if ($request->has('type')) {
            $data['type'] = $request->type;
        }

        if ($request->type === 'velo' || ($activite->type === 'velo' && !$request->has('type'))) {
            if ($request->has('distance_km')) {
                $data['distance_km'] = $request->distance_km;
                $data['pas'] = null;
            }
        } else {
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