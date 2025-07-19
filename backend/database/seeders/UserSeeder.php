<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crée des utilisateurs de démonstration, dont un administrateur et plusieurs utilisateurs répartis dans les équipes.
     */
    public function run()
    {
        // Création de l'administrateur de démo
        Utilisateur::create([
            'nom' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'password' => Hash::make('admin1234'),
            'equipe_id' => 1,
            'is_admin' => true,
        ]);

        // Liste de noms pour les utilisateurs classiques
        $noms = [
            'Alice Martin', 'Bob Dupont', 'Chloé Bernard', 'David Petit', 'Emma Leroy',
            'Félix Moreau', 'Gina Roux', 'Hugo Girard', 'Inès Lefevre', 'Julien Fabre',
            'Karim Blin', 'Léa Simon', 'Mickael Durand', 'Nina Perret'
        ];

        // Création des utilisateurs classiques répartis sur 5 équipes
        foreach ($noms as $i => $nom) {
            Utilisateur::create([
                'nom' => $nom,
                'email' => strtolower(str_replace(' ', '.', $nom)) . '@demo.com',
                'password' => Hash::make('password'), // Mot de passe par défaut
                'equipe_id' => ($i % 5) + 1, // Répartit sur 5 équipes
                'is_admin' => false,
            ]);
        }
    }
}