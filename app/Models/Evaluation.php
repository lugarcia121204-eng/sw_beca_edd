<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluations';

    protected $fillable = [
        'application_id',
        'evaluator_id',
        'status',
        'score',
        'answers',
        'comment',
        'deadline_at',
        'review_level',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'deadline_at' => 'datetime',
    ];

    public function scopeVisibleTo($query, User $evaluator)
    {
        return $query->where('evaluator_id', $evaluator->id)
            ->whereHas('application', fn ($q) => $q->withinEvaluatorScope($evaluator));
    }

    // Relaciones

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Scope to get the advanced joining data for administrative Recognitions View.
     */
    public function scopeWithRecognitionDetails($query)
    {
        return $query->join('users', 'evaluations.evaluator_id', '=', 'users.id')
            ->join('applications', 'evaluations.application_id', '=', 'applications.id')
            ->join('announcements', 'applications.announcement_id', '=', 'announcements.id')
            ->leftJoin('recognitions', function ($join) {
                $join->on('recognitions.user_id', '=', 'users.id')
                    ->on('recognitions.announcement_id', '=', 'announcements.id')
                    ->where('recognitions.type', '=', 'evaluator');
            })
            ->select(
                'users.id as evaluator_id',
                'users.name as evaluator_name',
                'announcements.id as announcement_id',
                'announcements.name as announcement_name',
                'announcements.created_at as announcement_date',
                \Illuminate\Support\Facades\DB::raw("COUNT(DISTINCT CASE WHEN evaluations.status != 'pending' THEN evaluations.id END) as applications_reviewed"),
                'recognitions.id as recognition_id',
                \Illuminate\Support\Facades\DB::raw('COALESCE(recognitions.active, 0) as active'),
                'recognitions.sent_at'
            )
            ->groupBy(
                'users.id',
                'users.name',
                'announcements.id',
                'announcements.name',
                'announcements.created_at',
                'recognitions.id',
                'recognitions.active',
                'recognitions.sent_at'
            )
            ->when(request('sort_field'), function ($q, $field) {
                $dir = request('sort_direction', 'asc');
                match ($field) {
                    'evaluator_name' => $q->orderBy('users.name', $dir),
                    'announcement_name' => $q->orderBy('announcements.name', $dir),
                    default => $q->orderBy('announcements.created_at', 'desc')
                };
            }, function ($q) {
                $q->orderBy('announcements.created_at', 'desc')->orderBy('users.name', 'asc');
            });
    }

    /**
     * Scope to search evaluations based on teacher name or announcement name.
     */
    public function scopeSearchByTeacherOrAnnouncement($query, $search)
    {
        return $query->whereHas('application', function ($q) use ($search) {
            $q->whereHas('announcement', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })
                ->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Scope a query to only include pending evaluations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->whereHas('application', fn ($q) => $q->whereColumn('applications.review_stage', 'evaluations.review_level')->where('applications.status', 'pending'));
    }

    /**
     * Scope a query to only include approved evaluations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected evaluations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
