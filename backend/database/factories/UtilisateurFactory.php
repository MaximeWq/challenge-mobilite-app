<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory pour générer des utilisateurs de test avec des données factices.
 * Utilisée dans les tests unitaires et les seeders.
 */
class UtilisateurFactory extends Factory
{
    // Modèle associé à la factory
    protected $model = Utilisateur::class;

    /**
     * Définit les valeurs par défaut pour un utilisateur de test.
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Mot de passe par défaut pour les tests
            'equipe_id' => 1, // Peut être adapté pour des équipes aléatoires
            'is_admin' => false,
        ];
    }
} 