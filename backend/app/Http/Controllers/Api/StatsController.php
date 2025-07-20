<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activite;
use App\Models\Utilisateur;
use App\Models\Equipe;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Statistiques générales du challenge.
     * Retourne le total d'activités, de kilomètres, de pas, d'utilisateurs, d'équipes, etc.
     * Fournit aussi les activités par type et l'évolution sur les 30 derniers jours.
     */
    public function general()
    {
        $totalActivities = Activite::count();
        $totalDistance = Activite::sum('distance_km');
        $totalSteps = Activite::sum('pas');
        $totalUsers = Utilisateur::count();
        $totalTeams = Equipe::count();
        $activitiesThisWeek = Activite::where('date', '>=', Carbon::now()->startOfWeek())->count();
        $activitiesThisMonth = Activite::where('date', '>=', Carbon::now()->startOfMonth())->count();

        // Statistiques par type d'activité (vélo, marche/course)
        $activitiesByType = Activite::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(distance_km) as total_distance'))
            ->groupBy('type')
            ->get();

        // Statistiques d'évolution sur les 30 derniers jours
        $dailyActivities = Activite::select(
                'date',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(distance_km) as total_distance')
            )
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_activities' => $totalActivities,
                'total_distance_km' => round($totalDistance, 2),
                'total_steps' => $totalSteps,
                'total_users' => $totalUsers,
                'total_teams' => $totalTeams,
                'activities_this_week' => $activitiesThisWeek,
                'activities_this_month' => $activitiesThisMonth,
                'average_distance_per_activity' => $totalActivities > 0 ? round($totalDistance / $totalActivities, 2) : 0,
                'activities_by_type' => $activitiesByType,
                'daily_activities' => $dailyActivities,
            ],
            'meta' => null
        ]);
    }

    /**
     * Classement et statistiques par équipe.
     * Retourne la liste des équipes avec total, moyenne, nombre de membres, etc.
     * Classement par total de kilomètres parcourus.
     */
    public function teams()
    {
        $teams = Equipe::with('utilisateurs')
            ->leftJoin('utilisateurs', 'equipes.id', '=', 'utilisateurs.equipe_id')
            ->leftJoin('activites', 'utilisateurs.id', '=', 'activites.utilisateur_id')
            ->select(
                'equipes.id',
                'equipes.nom',
                'equipes.description',
                DB::raw('COUNT(DISTINCT utilisateurs.id) as members_count'),
                DB::raw('COUNT(activites.id) as total_activities'),
                DB::raw('SUM(activites.distance_km) as total_distance'),
                DB::raw('AVG(activites.distance_km) as avg_distance_per_activity')
            )
            ->groupBy('equipes.id', 'equipes.nom', 'equipes.description')
            ->orderBy('total_distance', 'desc')
            ->get();

        // Calcul de la moyenne de km par membre pour chaque équipe
        $teams = $teams->map(function ($team) {
            $team->avg_distance_per_member = $team->members_count > 0 
                ? round($team->total_distance / $team->members_count, 2) 
                : 0;
            $team->total_distance = round($team->total_distance, 2);
            $team->avg_distance_per_activity = round($team->avg_distance_per_activity, 2);
            return $team;
        });

        return response()->json([
            'status' => 'success',
            'data' => $teams,
            'meta' => [
                'total' => $teams->count(),
            ]
        ]);
    }

    /**
     * Classement des utilisateurs (top 10).
     * Retourne les 10 meilleurs utilisateurs selon la distance totale parcourue.
     */
    public function users()
    {
        $users = Utilisateur::with('equipe')
            ->leftJoin('activites', 'utilisateurs.id', '=', 'activites.utilisateur_id')
            ->select(
                'utilisateurs.id',
                'utilisateurs.nom',
                'utilisateurs.email',
                'utilisateurs.equipe_id',
                DB::raw('COUNT(activites.id) as total_activities'),
                DB::raw('SUM(activites.distance_km) as total_distance'),
                DB::raw('AVG(activites.distance_km) as avg_distance_per_activity')
            )
            ->groupBy('utilisateurs.id', 'utilisateurs.nom', 'utilisateurs.email', 'utilisateurs.equipe_id')
            ->orderBy('total_distance', 'desc')
            ->limit(10)
            ->get();

        // Ajoute le rang et arrondit les valeurs
        $users = $users->map(function ($user, $index) {
            $user->rank = $index + 1;
            $user->total_distance = round($user->total_distance, 2);
            $user->avg_distance_per_activity = round($user->avg_distance_per_activity, 2);
            return $user;
        });

        return response()->json([
            'status' => 'success',
            'data' => $users,
            'meta' => [
                'total' => $users->count(),
            ]
        ]);
    }

    /**
     * Statistiques personnelles de l'utilisateur connecté.
     * Retourne les stats, classements, évolution, etc. pour l'utilisateur courant.
     */
    public function personal()
    {
        $utilisateur = Auth::user();
        
        $activities = Activite::where('utilisateur_id', $utilisateur->id);
        
        $totalActivities = $activities->count();
        $totalDistance = $activities->sum('distance_km');
        $totalSteps = $activities->sum('pas');
        
        // Statistiques par type d'activité pour l'utilisateur
        $veloStats = Activite::where('utilisateur_id', $utilisateur->id)
            ->where('type', 'velo')
            ->selectRaw('COUNT(*) as count, SUM(distance_km) as total_distance')
            ->first();
            
        $marcheStats = Activite::where('utilisateur_id', $utilisateur->id)
            ->where('type', 'marche_course')
            ->selectRaw('COUNT(*) as count, SUM(distance_km) as total_distance, SUM(pas) as total_steps')
            ->first();

        // Calcul de la moyenne quotidienne
        $firstActivity = Activite::where('utilisateur_id', $utilisateur->id)
            ->orderBy('date')
            ->first();
            
        $daysSinceFirst = $firstActivity 
            ? Carbon::parse($firstActivity->date)->diffInDays(Carbon::now()) + 1 
            : 1;
            
        $dailyAverage = $daysSinceFirst > 0 ? round($totalDistance / $daysSinceFirst, 2) : 0;

        // Évolution sur 30 derniers jours pour l'utilisateur
        $last30Days = Activite::where('utilisateur_id', $utilisateur->id)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->select('date', 'distance_km', 'type')
            ->orderBy('date')
            ->get();

        // Calcul du rang général de l'utilisateur (classement général)
        $allUsers = Utilisateur::leftJoin('activites', 'utilisateurs.id', '=', 'activites.utilisateur_id')
            ->select('utilisateurs.id', DB::raw('COALESCE(SUM(activites.distance_km),0) as total_distance'))
            ->groupBy('utilisateurs.id')
            ->orderByDesc('total_distance')
            ->get();
        $userRank = 1;
        foreach ($allUsers as $u) {
            if ($u->id == $utilisateur->id) break;
            if ($u->total_distance > $totalDistance) $userRank++;
        }

        // Calcul du rang dans l'équipe
        $teamUsers = Utilisateur::where('equipe_id', $utilisateur->equipe_id)
            ->leftJoin('activites', 'utilisateurs.id', '=', 'activites.utilisateur_id')
            ->select('utilisateurs.id', DB::raw('COALESCE(SUM(activites.distance_km),0) as total_distance'))
            ->groupBy('utilisateurs.id')
            ->orderByDesc('total_distance')
            ->get();
        $teamRank = 1;
        foreach ($teamUsers as $u) {
            if ($u->id == $utilisateur->id) break;
            if ($u->total_distance > $totalDistance) $teamRank++;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->load('equipe'),
                'total_activities' => $totalActivities,
                'total_distance_km' => round($totalDistance, 2),
                'total_steps' => $totalSteps,
                'daily_average_km' => $dailyAverage,
                'velo_stats' => [
                    'count' => $veloStats->count ?? 0,
                    'total_distance' => round($veloStats->total_distance ?? 0, 2),
                ],
                'marche_stats' => [
                    'count' => $marcheStats->count ?? 0,
                    'total_distance' => round($marcheStats->total_distance ?? 0, 2),
                    'total_steps' => $marcheStats->total_steps ?? 0,
                ],
                'ranking' => [
                    'general' => $userRank,
                    'team' => $teamRank,
                ],
                'last_30_days' => $last30Days,
            ],
            'meta' => null
        ]);
    }

    /**
     * Export CSV des données globales du challenge.
     * Génère un fichier CSV avec toutes les activités, utilisateurs et équipes.
     */
    public function export()
    {
        $activities = Activite::with(['utilisateur', 'utilisateur.equipe'])
            ->orderBy('date', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = ['Utilisateur', 'Équipe', 'Type', 'Distance (km)', 'Pas'];

        foreach ($activities as $activity) {
            $csvData[] = [
                $activity->utilisateur->nom,
                $activity->utilisateur->equipe->nom,
                $activity->type === 'velo' ? 'Vélo' : 'Marche/Course',
                $activity->distance_km,
                $activity->pas ?? 0,
            ];
        }

        // Génère le contenu CSV avec ; comme séparateur
        $csvContent = '';
        foreach ($csvData as $row) {
            // Échappe les ";" et les "\n" dans les champs si besoin
            $escapedRow = array_map(function($field) {
                $field = str_replace('"', '""', $field); // double quote escaping
                if (strpos($field, ';') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                    $field = '"' . $field . '"';
                }
                return $field;
            }, $row);
            $csvContent .= implode(';', $escapedRow) . "\r\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="challenge-mobilite-export.csv"',
        ]);
    }
}