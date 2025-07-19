<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Vérifie que l'utilisateur connecté est un administrateur.
     * Si ce n'est pas le cas, retourne une erreur 403 (forbidden).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès réservé aux administrateurs',
            ], 403);
        }
        return $next($request);
    }
}