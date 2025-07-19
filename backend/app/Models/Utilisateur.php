<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // Nom de la table associée
    protected $table = 'utilisateurs';

    // Champs autorisés à l'assignation de masse
    protected $fillable = [
        'nom',
        'email',
        'password',
        'equipe_id',
        'is_admin',
    ];

    // Champs cachés lors de la sérialisation
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts automatiques des champs
    protected $casts = [
        'is_admin' => 'boolean',
    ];

    /**
     * Relation : un utilisateur appartient à une équipe
     */
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    /**
     * Relation : un utilisateur a plusieurs activités
     */
    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class, 'utilisateur_id');
    }

    /**
     * Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}