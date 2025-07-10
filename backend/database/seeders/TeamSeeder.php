<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipe;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $equipes = [
            ['nom' => 'Les Rouleurs Verts', 'description' => 'Équipe vélo dynamique'],
            ['nom' => 'Les Marcheurs Urbains', 'description' => 'Fans de marche et de course'],
            ['nom' => 'Les Écolos Pressés', 'description' => 'Toujours en mouvement'],
            ['nom' => 'Les Sprinteurs du Midi', 'description' => 'Les rapides de la pause déjeuner'],
            ['nom' => 'Les Baladeurs', 'description' => 'Pour le plaisir de bouger'],
        ];

        foreach ($equipes as $equipe) {
            Equipe::create($equipe);
        }
    }
}