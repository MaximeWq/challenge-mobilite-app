<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        Utilisateur::create([
            'nom' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'password' => Hash::make('admin1234'),
            'equipe_id' => 1,
            'is_admin' => true,
        ]);

        // Utilisateurs classiques
        $noms = [
            'Alice Martin', 'Bob Dupont', 'Chloé Bernard', 'David Petit', 'Emma Leroy',
            'Félix Moreau', 'Gina Roux', 'Hugo Girard', 'Inès Lefevre', 'Julien Fabre',
            'Karim Blin', 'Léa Simon', 'Mickael Durand', 'Nina Perret'
        ];

        foreach ($noms as $i => $nom) {
            Utilisateur::create([
                'nom' => $nom,
                'email' => strtolower(str_replace(' ', '.', $nom)) . '@demo.com',
                'password' => Hash::make('password'),
                'equipe_id' => ($i % 5) + 1, // Répartit sur 5 équipes
                'is_admin' => false,
            ]);
        }
    }
}