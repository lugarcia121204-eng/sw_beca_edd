<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'applications';

    protected $fillable = [
        'user_id',
        'announcement_id',
        'status',
        'admin_comment',
        'position_type_id',
        'template_id',
        'identifier',
        'digital_seal',
        'snapshot_data',
        'via',
        'review_stage',
    ];

    protected $appends = ['position_full_type'];

    protected $attributes = ['review_stage' => 'local', 'status' => 'pending'];

    public function scopeWithinEvaluatorScope($query, User $evaluator)
    {
        $query->where('user_id', '!=', $evaluator->id);
        if (!$evaluator->isAuthorizedEvaluator()) {
            return $query->whereRaw('1 = 0');
        }
        return match ($evaluator->evaluator_level) {
            'local' => $query->whereHas('user', fn ($q) => $q->where('institution_id', $evaluator->evaluator_institution_id)),
            'state' => $query->whereHas('user.institution', fn ($q) => $q->where('state_id', $evaluator->evaluator_state_id)),
            'national' => $query,
            default => $query->whereRaw('1 = 0'),
        };
    }

    // Relaciones

    public function positionType(): BelongsTo
    {
        return $this->belongsTo(PositionType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function getPositionFullTypeAttribute(): ?string
    {
        if ($this->positionType) {
            return "{$this->positionType->code} - {$this->positionType->name}";
        }
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeValidByIdentifier($query, $identifier)
    {
        return $query->where('identifier', $identifier)->with('user', 'announcement');
    }

    /**
     * Scope para búsqueda global en solicitudes.
     */
    public function scopeBuscarGlobal(\Illuminate\Database\Eloquent\Builder $query, ?string $search): \Illuminate\Database\Eloquent\Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($subQ) use ($search) {
                $subQ->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('institution', function ($instQ) use ($search) {
                        $instQ->where('institutions.name', 'like', "%{$search}%");
                    });
            })->orWhere('applications.id', 'like', "%{$search}%");
        });
    }

    public function scopePorEstatus($query, $status)
    {
        if (empty($status)) {
            return $query;
        }
        return $query->where('status', $status);
    }

    public function scopeOrdenado($query, $sortField = 'id', $sortDirection = 'desc')
    {
        if (empty($sortField)) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        }

        return match ($sortField) {
            'user_name' => $query->join('users', 'users.id', '=', 'applications.user_id')
                ->orderBy('users.name', $sortDirection)
                ->select('applications.*'),
            'campus' => $query->join('users', 'users.id', '=', 'applications.user_id')
                ->join('institutions', 'institutions.id', '=', 'users.institution_id')
                ->orderBy('institutions.name', $sortDirection)
                ->select('applications.*'),
            default => $query->orderBy('applications.' . $sortField, $sortDirection),
        };
    }

    /**
     * Scope a query to only include applications of a given user.
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to load teacher details with nested relationships and counts for Document Controller
     */
    public function scopeWithTeacherDetails($query)
    {
        return $query->select('applications.*')
            ->join('users', 'users.id', '=', 'applications.user_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'users.institution_id')
            ->leftJoin('announcements', 'announcements.id', '=', 'applications.announcement_id')
            ->with([
                'user.institution.state',
                'user.priorityArea',
                'announcement' => function ($q) {
                    $q->withTrashed();
                }
            ])
            ->withCount('documents');
    }

    /**
     * Scope to search by teacher details (Name, Email, Institution, Announcement)
     */
    public function scopeSearchByTeacherDetails($query, $search)
    {
        return $query->where('users.name', 'LIKE', "%{$search}%")
            ->orWhere('users.email', 'LIKE', "%{$search}%")
            ->orWhere('institutions.name', 'LIKE', "%{$search}%")
            ->orWhere('announcements.name', 'LIKE', "%{$search}%")
            ->orWhere('applications.id', 'LIKE', "%{$search}%");
    }
}
