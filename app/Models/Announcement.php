<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
    ];

    protected $appends = ['current_stage'];

    protected $casts = [
        'status' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    /**
     * Obtiene los usuarios (docentes) asociados a esta convocatoria.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('status', 'registration_date')
            ->withTimestamps();
    }

    /**
     * Obtiene los documentos de catálogo requeridos para esta convocatoria.
     */
    public function catalogDocuments(): BelongsToMany
    {
        return $this->belongsToMany(CatalogDocument::class, 'announcement_document', 'announcement_id', 'catalog_document_id')
            ->withPivot('is_mandatory', 'via')
            ->withTimestamps();
    }

    /**
     * Obtiene el calendario asociado a esta convocatoria.
     */
    public function calendar(): HasOne
    {
        return $this->hasOne(Calendar::class);
    }

    /**
     * Obtiene las solicitudes de esta convocatoria.
     */
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Application::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    
    /**
     * Calcula la etapa actual de la convocatoria basado en el calendario.
     * Posibles retornos: 'publicacion', 'registro', 'evaluacion', 'resultados', 'terminada', 'invalida'
     *
     * Lógica en cascada: una etapa permanece activa hasta que comienza la siguiente,
     * evitando que huecos entre fechas den 'invalida'.
     */
    public function getCurrentStageAttribute(): string
    {
        if (!$this->calendar) {
            return 'invalida';
        }

        $now = \Carbon\Carbon::now()->startOfDay();
        $cal = $this->calendar;

        // Pasada la fecha de cierre de resultados → terminada
        if ($cal->results_end && $now->gt(\Carbon\Carbon::parse($cal->results_end)->endOfDay())) {
            return 'terminada';
        }

        // Cascada de mayor a menor prioridad: en cuanto se llega a la fecha
        // de inicio de una etapa, esa etapa es la activa aunque haya un hueco
        // desde el fin de la etapa anterior.

        if ($cal->results_start && $now->gte(\Carbon\Carbon::parse($cal->results_start)->startOfDay())) {
            return 'resultados';
        }

        if ($cal->evaluation_start && $now->gte(\Carbon\Carbon::parse($cal->evaluation_start)->startOfDay())) {
            return 'evaluacion';
        }

        if ($cal->registration_start && $now->gte(\Carbon\Carbon::parse($cal->registration_start)->startOfDay())) {
            return 'registro';
        }

        if ($cal->publication_start && $now->gte(\Carbon\Carbon::parse($cal->publication_start)->startOfDay())) {
            return 'publicacion';
        }

        // Antes de que comience la publicación
        return 'invalida';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope para búsqueda global en el modelo.
     */
    public function scopeBuscarGlobal(\Illuminate\Database\Eloquent\Builder $query, ?string $search): \Illuminate\Database\Eloquent\Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('announcements.name', 'LIKE', "%{$search}%")
                ->orWhere('announcements.description', 'LIKE', "%{$search}%")
                ->orWhere('announcements.status', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope para ordenar resultados.
     */
    public function scopeOrdenado(\Illuminate\Database\Eloquent\Builder $query, string $sortField = 'id', string $sortDirection = 'desc'): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Scope a query to exclude announcements the current user has already applied to.
     */
    public function scopeNotAppliedByCurrentUser($query)
    {
        $appliedAnnouncementIds = \App\Models\Application::forCurrentUser()->pluck('announcement_id');
        return $query->whereNotIn('id', $appliedAnnouncementIds);
    }

    /**
     * Scope a query to only include active announcements.
     */
    public function scopeActiva($query)
    {
        return $query->where('status', 'activa');
    }

    /**
     * Scope a query to only include closed announcements.
     */
    public function scopeCerrada($query)
    {
        return $query->where('status', 'cerrada');
    }

    /**
     * Scope a query to only include announcements currently in the 'resultados' stage.
     */
    public function scopeInResultadosStage($query)
    {
        return $query->whereHas('calendar', function ($q) {
            $q->whereNotNull('results_start')
              ->whereDate('results_start', '<=', now())
              ->where(function ($sub) {
                  $sub->whereNull('results_end')
                      ->orWhereDate('results_end', '>=', now());
              });
        });
    }
}
