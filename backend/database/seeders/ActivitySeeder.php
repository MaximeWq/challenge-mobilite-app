<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activite;
use App\Models\Utilisateur;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        $utilisateurs = Utilisateur::all();
        $types = ['velo', 'marche_course'];

        foreach ($utilisateurs as $utilisateur) {
            $nbActivites = rand(2, 5);
            $dates = [];
            while (count($dates) < $nbActivites) {
                $date = Carbon::now()->subDays(rand(1, 30))->format('Y-m-d');
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
            }
            foreach ($dates as $date) {
                $type = $types[array_rand($types)];
                if ($type === 'velo') {
                    $distance = rand(2, 20);
                    $pas = null;
                } else {
                    $pas = rand(2000, 15000);
                    $distance = round($pas / 1500, 2);
                }
                Activite::create([
                    'utilisateur_id' => $utilisateur->id,
                    'date' => $date,
                    'type' => $type,
                    'distance_km' => $distance,
                    'pas' => $pas,
                ]);
            }
        }
    }
}