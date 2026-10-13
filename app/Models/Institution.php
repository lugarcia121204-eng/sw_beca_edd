<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// use App\Models\Estado; // Removed

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Institution extends Model
{
    use SoftDeletes;

    protected $table = 'institutions'; // Table renamed? Assuming yes.

    protected $fillable = [
        'name',
        'state_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene el estado al que pertenece la institución.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Obtiene los usuarios (docentes) de esta institución.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtiene las solicitudes a través de los usuarios de esta institución.
     */
    public function applications(): HasManyThrough
    {
        return $this->hasManyThrough(Application::class, User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope para búsqueda global en instituciones.
     */
    public function scopeBuscarGlobal(\Illuminate\Database\Eloquent\Builder $query, ?string $search): \Illuminate\Database\Eloquent\Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('institutions.name', 'LIKE', "%{$search}%")
                ->orWhereHas('state', function ($qState) use ($search) {
                    $qState->where('states.name', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Scope para ordenar resultados.
     */
    public function scopeOrdenado(\Illuminate\Database\Eloquent\Builder $query, string $sortField = 'id', string $sortDirection = 'desc'): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy($sortField, $sortDirection);
    }
}
