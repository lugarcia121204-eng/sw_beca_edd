<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Application;
use App\Models\State;
use App\Exports\ApplicationsExport;
use App\Http\Resources\RequestControlSummaryResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RequestControlController extends Controller
{
    public function index(Request $request)
    {
        // Global Statistics
        $stats = [
            'total' => Application::count(),
            'pending' => Application::pending()->count(),
            'approved' => Application::approved()->count(),
            'rejected' => Application::rejected()->count(),
        ];

        // Main Table: Institutions with Approved/Rejected counts (filterable)
        $search = $request->input('search');
        $stateId = $request->input('state_id');
        $institutionId = $request->input('institution_id');
        $statusFilter = $request->input('status'); // Added status filter
        $rows = $request->input('rows', 10);
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $institutions = Institution::with('state')
            ->withCount([
                'applications as approved_count' => function ($query) {
                    $query->approved();
                },
                'applications as rejected_count' => function ($query) {
                    $query->rejected();
                },
                'applications as filtered_applications_count' => function ($query) use ($statusFilter) {
                    if ($statusFilter === 'pending') {
                        $query->pending();
                    } elseif ($statusFilter === 'approved') {
                        $query->approved();
                    } elseif ($statusFilter === 'rejected') {
                        $query->rejected();
                    }
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->when($stateId, fn($q) => $q->where('state_id', $stateId))
            ->when($institutionId, fn($q) => $q->where('id', $institutionId))
            ->whereHas('users.applications', function ($q) {
                $q->whereIn('status', ['approved', 'rejected']);
            })
            ->when($sortField === 'state', function ($query) use ($sortDirection) {
                $query->join('states', 'institutions.state_id', '=', 'states.id')
                    ->orderBy('states.name', $sortDirection)
                    ->select('institutions.*');
            }, function ($query) use ($sortField, $sortDirection) {
                $query->orderBy($sortField, $sortDirection);
            })
            ->paginate($rows)
            ->withQueryString();

        return Inertia::render('SuperAdmin/Applications/Index', [
            'stats' => $stats,
            'institutions' => RequestControlSummaryResource::collection($institutions),
            'states' => State::select('id', 'name')->orderBy('name')->get(),
            'allInstitutions' => Institution::select('id', 'name', 'state_id')->orderBy('name')->get(),
            'filters' => $request->all(['search', 'state_id', 'institution_id', 'rows', 'sort_field', 'sort_direction']),
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
            'control_solicitudes_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
