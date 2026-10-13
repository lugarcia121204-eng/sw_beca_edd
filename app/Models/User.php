<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'curp',
        'name',
        'email',
        'password',
        'institution_id',
        'priority_area_id',
        'sub_area_id',
        'email_verification_code',
        'email_verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function evaluatorInstitution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'evaluator_institution_id');
    }

    public function evaluatorState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'evaluator_state_id');
    }

    public function isAuthorizedEvaluator(): bool
    {
        return $this->hasRole('Evaluador') && $this->email_verified_at
            && $this->evaluator_status === 'authorized'
            && match ($this->evaluator_level) {
                'local' => $this->evaluatorInstitution()->exists(),
                'state' => $this->evaluatorState()->exists(),
                'national' => true,
                default => false,
            };
    }

    public function canEvaluateApplication(Application $application): bool
    {
        if (!$this->isAuthorizedEvaluator() || $application->user_id === $this->id) {
            return false;
        }
        return $application->status === 'pending'
            && $application->review_stage === $this->evaluator_level
            && !$application->evaluations()->where('evaluator_id', $this->id)->whereNotNull('review_level')->where('review_level', '!=', $this->evaluator_level)->exists()
            && $application->newQuery()->whereKey($application->id)->withinEvaluatorScope($this)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function priorityArea(): BelongsTo
    {
        return $this->belongsTo(PriorityArea::class);
    }

    public function subArea(): BelongsTo
    {
        return $this->belongsTo(SubArea::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')
            ->withPivot('status', 'registration_date')
            ->withTimestamps();
    }

    public function getAllowedViews($module): Collection
    {
        return $this->getAllPermissions()->where('module_key', $module)->pluck('arg');
    }

    public function getRolesArray(): Collection
    {
        return $this->roles()->get()->mapWithKeys(function ($role) {
            return [$role['name'] => true];
        });
    }

    public function getPermissionArray(): Collection
    {
        return $this->getAllPermissions()->mapWithKeys(function ($permission) {
            return [$permission['name'] => true];
        });
    }

    /**
     * Get the primary role of the user
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles()->first()?->name;
    }

    /**
     * Get list of applicable role names (Admin, Evaluador, Docente)
     */
    public function getRolesList(): array
    {
        return $this->roles()
            ->whereIn('name', ['Admin', 'Evaluador', 'Docente'])
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get the layout name based on user role(s)
     */
    public function getLayoutName(): string
    {
        $applicableRoles = $this->getRolesList();

        // Multi-role: show unified sidebar with sections
        if (count($applicableRoles) > 1) {
            return 'MultiRoleLayout';
        }

        $role = $this->getPrimaryRole();

        return match ($role) {
            'Super Admin' => 'LayoutAuthenticated',
            'Admin' => 'AdminLayout',
            'Evaluador' => 'EvaluatorLayout',
            'Docente' => 'TeacherLayout',
            default => 'TeacherLayout',
        };
    }

    /**
     * Check if user has specific permission
     */
    /**
     * Check if user has specific permission
     */
    public function canPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeBuscarGlobal($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('users.name', 'LIKE', "%{$search}%")
                ->orWhere('users.email', 'LIKE', "%{$search}%")
                ->orWhereHas('roles', function ($qRole) use ($search) {
                    $qRole->where('roles.name', 'LIKE', "%{$search}%");
                });
        });
    }

    public function scopeOrdenado($query, $sortField = 'id', $sortDirection = 'desc')
    {
        return $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        \Illuminate\Support\Facades\Mail::to($this->email)
            ->queue(new \App\Mail\PasswordResetMail($resetUrl, $this->name));
    }
}

