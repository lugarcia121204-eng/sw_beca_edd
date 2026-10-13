<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Institution;
use App\Models\Application;
use App\Exports\ApplicationsExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $institutionId = $request->input('institution_id');
        $stateId = $request->input('state_id');
        $statusFilter = $request->input('status');

        // Base Query for Stats
        $query = Application::query();

        // Apply Filters
        if ($search) {
            $query->whereHas('user.institution', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('state', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($institutionId) {
            $query->whereHas('user', function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });
        }

        if ($stateId) {
            $query->whereHas('user.institution', function ($q) use ($stateId) {
                $q->where('state_id', $stateId);
            });
        }

        if ($statusFilter && in_array($statusFilter, ['approved', 'rejected', 'expired'])) {
            $query->where('applications.status', $statusFilter);
        }

        // --- Stats Cards ---
        // Clone query for each count to avoid interference
        $totalSolicitudes = (clone $query)->count();
        $totalAprobadas = (clone $query)->where('applications.status', 'approved')->count();
        $totalRechazadas = (clone $query)->where('applications.status', 'rejected')->count();

        // --- Chart Data ---
        // Instead of DB::raw("SUM(CASE WHEN applications.status = 'approved' THEN 1 ELSE 0 END)"),
        // We will query directly from Institutions to leverage withCount, while applying the same filters

        $institutionsQuery = Institution::query()
            ->withCount([
                'applications as aprobadas' => function ($q) use ($statusFilter) {
                    $q->where('status', 'approved');
                    if ($statusFilter && in_array($statusFilter, ['approved', 'rejected', 'expired'])) {
                         $q->where('status', $statusFilter);
                    }
                },
                'applications as rechazadas' => function ($q) use ($statusFilter) {
                    $q->where('status', 'rejected');
                    if ($statusFilter && in_array($statusFilter, ['approved', 'rejected', 'expired'])) {
                         $q->where('status', $statusFilter);
                    }
                }
            ]);

        // Apply same filters to the institutions pool before aggregating
        if ($institutionId) {
            $institutionsQuery->where('id', $institutionId);
        }
        if ($stateId) {
            $institutionsQuery->where('state_id', $stateId);
        }
        if ($search) {
            $institutionsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('state', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Only retrieve institutions that actually have any applications matching the filters
        $chartData = $institutionsQuery->having('aprobadas', '>', 0)
                                     ->orHaving('rechazadas', '>', 0)
                                     ->orderBy('name')
                                     ->get();

        // Format for Chart.js
        $labels = $chartData->pluck('name');
        $dataAprobadas = $chartData->pluck('aprobadas');
        $dataRechazadas = $chartData->pluck('rechazadas');


        return Inertia::render('SuperAdmin/Dashboard/Index', [
            'stats' => [
                'total' => $totalSolicitudes,
                'approved' => $totalAprobadas,
                'rejected' => $totalRechazadas,
            ],
            'chart' => [
                'labels' => $labels,
                'approved' => $dataAprobadas,
                'rejected' => $dataRechazadas,
            ],
            'filters' => [
                'search' => $search,
                'institution_id' => $institutionId,
                'state_id' => $stateId,
                'status' => $statusFilter,
            ],
            'institutions' => Institution::select('id', 'name', 'state_id')->orderBy('name')->get(),
            'states' => State::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new ApplicationsExport(
                $request->input('search'),
                $request->input('institution_id'),
                $request->input('state_id'),
                $request->input('status')
            ),
            'solicitudes_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
