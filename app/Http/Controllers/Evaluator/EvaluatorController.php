<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Evaluacion;
use App\Models\Application;

class EvaluatorController extends Controller
{
    public function inicio(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        // Statistics using advanced Eloquent relationship scopes (1 Query)
        $user->loadCount([
            'evaluations as total' => fn ($q) => $q->visibleTo($user),
            'evaluations as pendientes' => fn($q) => $q->visibleTo($user)->pending()->whereHas('application', fn($q2) => $q2->where('status', 'pending'))
        ]);
        
        $totalAsignadas = $user->total;
        $pendientes = $user->pendientes;
        $evaluadas = $totalAsignadas - $pendientes;

        // Pending Applications
        $applications = \App\Models\Evaluation::visibleTo($user)
            ->pending()
            ->whereHas('application', function ($q) {
                $q->where('status', 'pending');
            })
            ->with([
                'application.announcement',
                'application.user.subArea', 
                'application' => function ($query) {
                    $query->withCount('documents');
                }
            ])
            ->when($search, function ($query, $search) {
                $query->searchByTeacherOrAnnouncement($search);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('rows', 10))
            ->withQueryString();

        // Transform data for view
        $applications->getCollection()->transform(function ($evaluation) {
            return [
                'id' => $evaluation->application->id,
                'evaluation_id' => $evaluation->id,
                'announcement_name' => $evaluation->application->announcement->name,
                'teacher_name' => $evaluation->application->user->name,
                'teacher_degree' => $evaluation->application->user->subArea?->name ?? 'N/A',
                'application_date' => $evaluation->application->created_at->isoFormat('D [de] MMMM [de] YYYY'),
                'documents_count' => $evaluation->application->documents_count ?? 0,
                'status' => 'Pendiente', 
            ];
        });

        return Inertia::render('Evaluator/Dashboard', [
            'stats' => [
                'total' => $totalAsignadas,
                'pendientes' => $pendientes,
                'evaluadas' => $evaluadas,
            ],
            'filters' => $request->all(['search', 'rows']),
            'applications' => $applications,
        ]);
    }

    public function show($id)
    {
        $evaluation = \App\Models\Evaluation::where('id', $id)
            ->visibleTo(Auth::user())
            ->pending()
            ->whereHas('application', function ($q) {
                $q->where('status', 'pending');
            })
            ->with([
                'application.announcement',
                'application.user.subArea',
                'application.user.priorityArea',
                'application.user.institution',
                'application.documents',
                'application.positionType'
            ])
            ->firstOrFail();

        // Get Active Rubric — fallback to most recent if none is marked active
        $rubric = \App\Models\Rubric::where('is_active', true)
            ->with(['questions.options'])
            ->first()
            ?? \App\Models\Rubric::with(['questions.options'])
                ->latest()
                ->first();

        // Ensure all relationships are loaded
        $application = $evaluation->application;
        $application->load(['announcement', 'documents', 'user.institution', 'user.priorityArea', 'user.subArea', 'positionType']);

        return Inertia::render('Evaluator/Evaluation/Show', [
            'evaluation' => $evaluation,
            'application' => $application,
            'rubric' => $rubric,
            'teacher' => $application->user,
        ]);
    }

    public function evaluar(\App\Http\Requests\StoreEvaluationVerdictRequest $request, $id)
    {

        $evaluation = \App\Models\Evaluation::where('id', $id)
            ->visibleTo(Auth::user())
            ->pending()
            ->whereHas('application', function ($q) {
                $q->where('status', 'pending');
            })
            ->firstOrFail();

        app(\App\Services\ReviewWorkflowService::class)->submit((int) $id, $request->validated(), $request->user());

        return redirect()->route('evaluator.dashboard')->with('success', 'Solicitud evaluada exitosamente.');
    }

    public function streamDocument($id)
    {
        $document = \App\Models\Document::findOrFail($id);
        
        // Authorization: Check if the evaluator is assigned to the application this document belongs to
        $hasAccess = \App\Models\Evaluation::visibleTo(Auth::user())
            ->where('application_id', $document->application_id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $path = $document->file_path;
        return app(\App\Services\FileService::class)->responseFile($path);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        // Statistics using loadCount (1 Query instead of 4)
        $user->loadCount([
            'evaluations as total' => fn ($q) => $q->visibleTo($user),
            'evaluations as pendientes' => fn($q) => $q->visibleTo($user)->pending()->whereHas('application', fn($q2) => $q2->where('status', 'pending')),
            'evaluations as aprobadas' => fn($q) => $q->visibleTo($user)->approved(),
            'evaluations as rechazadas' => fn($q) => $q->visibleTo($user)->rejected(),
        ]);

        $totalAsignadas = $user->total;
        $pendientes = $user->pendientes;
        $evaluadas = $totalAsignadas - $pendientes;
        $aprobadas = $user->aprobadas;
        $rechazadas = $user->rechazadas;

        // Completed Applications (History)
        $query = \App\Models\Evaluation::visibleTo($user)
            ->where(function ($q) {
                $q->where('status', '!=', 'pending')->orWhereNull('review_level')
                  ->orWhereHas('application', function ($q2) {
                      $q2->where('status', '!=', 'pending');
                  });
            })
            ->with([
                'application.announcement',
                'application.user.subArea', 
                'application' => function ($query) {
                    $query->withCount('documents');
                }
            ])
            ->when($search, function ($query, $search) {
                $query->searchByTeacherOrAnnouncement($search);
            });

        if ($statusFilter && in_array($statusFilter, ['approved', 'rejected'])) {
            if ($statusFilter === 'approved') {
                $query->approved();
            } else {
                $query->rejected();
            }
        } elseif ($statusFilter === 'expired') {
            $query->where('status', 'expired');
        }

        $applications = $query
            ->orderBy('updated_at', 'desc')
            ->paginate($request->input('rows', 10))
            ->withQueryString();

        // Transform data
        $applications->getCollection()->transform(function ($evaluation) {
            $finalStatus = $evaluation->status;
            if ($finalStatus === 'pending' && $evaluation->application->status !== 'pending') {
                $finalStatus = 'evaluated_by_admin';
            }

            return [
                'id' => $evaluation->application->id,
                'evaluation_id' => $evaluation->id,
                'announcement_name' => $evaluation->application->announcement->name,
                'teacher_name' => $evaluation->application->user->name,
                'teacher_degree' => $evaluation->application->user->subArea?->name ?? 'N/A',
                'application_date' => $evaluation->application->created_at->isoFormat('D [de] MMMM [de] YYYY'),
                'evaluation_date' => $evaluation->updated_at->isoFormat('D [de] MMMM [de] YYYY'),
                'documents_count' => $evaluation->application->documents_count ?? 0,
                'status' => $finalStatus,
                'score' => $evaluation->score,
            ];
        });

        return Inertia::render('Evaluator/Evaluations/Index', [
            'stats' => [
                'total' => $totalAsignadas,
                'pendientes' => $pendientes,
                'evaluadas' => $evaluadas,
                'aprobadas' => $aprobadas,
                'rechazadas' => $rechazadas,
            ],
            'filters' => $request->all(['search', 'rows', 'status']),
            'applications' => $applications,
        ]);
    }

    public function showHistory($id)
    {
        $evaluation = \App\Models\Evaluation::where('id', $id)
            ->visibleTo(Auth::user())
            ->with([
                'application.announcement',
                'application.user.subArea',
                'application.user.priorityArea',
                'application.user.institution',
                'application.documents',
                'application.positionType'
            ])
            ->firstOrFail();

        // Get Active Rubric — fallback to most recent if none is marked active
        $rubric = \App\Models\Rubric::where('is_active', true)
            ->with(['questions.options'])
            ->first()
            ?? \App\Models\Rubric::with(['questions.options'])
                ->latest()
                ->first();

        $application = $evaluation->application;
        $application->load(['announcement', 'documents', 'user.institution', 'user.priorityArea', 'user.subArea', 'positionType']);

        return Inertia::render('Evaluator/Evaluations/Show', [
            'evaluation' => $evaluation,
            'application' => $application,
            'rubric' => $rubric,
            'teacher' => $application->user,
        ]);
    }
}
