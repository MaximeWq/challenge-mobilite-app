<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    use HasFactory;

    // Nom de la table associée
    protected $table = 'equipes';

    // Champs autorisés à l'assignation de masse
    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Relation : une équipe a plusieurs utilisateurs
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'equipe_id');
    }
}