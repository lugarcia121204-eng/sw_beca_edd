<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestControlSummaryResource;
use App\Http\Resources\ApplicationResource;
use App\Models\Institution;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequestControlController extends Controller
{
    /**
     * Display a listing of institutions with approved/rejected counts.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $state = $request->input('state');
        $rows = $request->input('rows', 10);
        
        $institutions = \App\Models\Institution::with('state')
            ->withCount([
                'users as approved_count' => function ($query) {
                    $query->whereHas('applications', function ($q) {
                        $q->where('status', 'approved');
                    });
                },
                'users as rejected_count' => function ($query) {
                    $query->whereHas('applications', function ($q) {
                        $q->where('status', 'rejected');
                    });
                }
            ])
            ->when($search, function ($query, $search) {
                 $query->where('name', 'like', "%{$search}%")
                       ->orWhere('id', 'like', "%{$search}%");
            })
            ->when($state, function ($query, $state) {
                $query->whereHas('state', function ($q) use ($state) {
                    $q->where('name', $state);
                });
            })
            ->whereHas('users.applications', function($q) {
                $q->whereIn('status', ['approved', 'rejected']);
            })
            ->paginate($rows)
            ->withQueryString();

        // Get unique states for filter dropdown
        $states = \App\Models\State::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/RequestControl/Index', [
            'institutions' => RequestControlSummaryResource::collection($institutions),
            'states' => $states,
            'filters' => $request->all(['search', 'state', 'rows']),
        ]);
    }

    /**
     * Display the specified resource details (list of requests for a campus).
     */
    public function show($id)
    {
        $institution = \App\Models\Institution::with('state')->findOrFail($id);

        $applications = \App\Models\Application::whereHas('user', function ($q) use ($id) {
                $q->where('institution_id', $id);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['user', 'announcement'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Admin/RequestControl/Show', [
            'institution' => $institution,
            'applications' => \App\Http\Resources\ApplicationResource::collection($applications),
        ]);
    }
}
