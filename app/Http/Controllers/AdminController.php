<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Application;
use App\Models\State;
use App\Exports\ApplicationsExport;
use App\Http\Resources\RequestControlSummaryResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Global Statistics
        $stats = [
            'total' => \App\Models\Application::count(),
            'pending' => \App\Models\Application::where('status', 'pending')->count(),
            'approved' => \App\Models\Application::where('status', 'approved')->count(),
            'rejected' => \App\Models\Application::where('status', 'rejected')->count(),
        ];

        // Top Institutions by Applications Count (for chart/progress bars)
        $topInstitutions = \App\Models\Institution::with('state')
            ->withCount('applications')
            ->having('applications_count', '>', 0)
            ->orderByDesc('applications_count')
            ->paginate(5, ['*'], 'top_page')
            ->withQueryString();

        // Main Table: Institutions with Approved/Rejected counts (filterable)
        $search = $request->input('search');
        $rows = $request->input('rows', 10);
        $stateId = $request->input('state_id');
        $institutionId = $request->input('institution_id');
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction', 'asc');

        $institutions = \App\Models\Institution::with('state')
            ->leftJoin('states', 'states.id', '=', 'institutions.state_id')
            ->select('institutions.*')
            ->withCount([
                'applications as approved_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'applications as rejected_count' => function ($query) {
                    $query->where('status', 'rejected');
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('institutions.name', 'like', "%{$search}%")
                        ->orWhere('institutions.id', 'like', "%{$search}%");
                });
            })
            ->when($stateId, fn($q) => $q->where('institutions.state_id', $stateId))
            ->when($institutionId, fn($q) => $q->where('institutions.id', $institutionId))
            ->whereHas('users.applications', function ($q) {
                $q->whereIn('status', ['approved', 'rejected']);
            })
            ->when($sortField, function ($query, $field) use ($sortDirection) {
                return match ($field) {
                    'name' => $query->orderBy('institutions.name', $sortDirection),
                    'state' => $query->orderBy('states.name', $sortDirection),
                    default => $query->orderBy('institutions.name', 'asc'),
                };
            }, function ($query) {
                $query->orderBy('institutions.name', 'asc');
            })
            ->paginate($rows, ['*'], 'table_page')
            ->withQueryString();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'topInstitutions' => $topInstitutions,
            'institutions' => RequestControlSummaryResource::collection($institutions),
            'states' => State::select('id', 'name')->orderBy('name')->get(),
            'allInstitutions' => Institution::select('id', 'name', 'state_id')->orderBy('name')->get(),
            'filters' => $request->all(['search', 'rows', 'state_id', 'institution_id', 'sort_field', 'sort_direction']),
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new ApplicationsExport(
                $request->input('search'),
                $request->input('institution_id'),
                $request->input('state_id'),
            ),
            'solicitudes_admin_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
