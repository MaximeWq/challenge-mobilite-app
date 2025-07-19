<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activite extends Model
{
    use HasFactory;

    // Nom de la table associée
    protected $table = 'activites';

    // Champs autorisés à l'assignation de masse
    protected $fillable = [
        'utilisateur_id',
        'date',
        'type',
        'distance_km',
        'pas',
    ];

    // Casts automatiques des champs
    protected $casts = [
        'date' => 'date', // Champ date au format date
        'distance_km' => 'float',
        'pas' => 'integer',
    ];

    /**
     * Relation : une activité appartient à un utilisateur
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}