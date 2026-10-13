<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Evaluation;

class EvaluationController extends Controller
{
    public function show($id)
    {
        $evaluation = Evaluation::with([
            'application.announcement',
            'application.user.institution.state',
            'application.user.priorityArea',
            'application.documents'
        ])->findOrFail($id);
        
        // Check authorization
        if ($evaluation->evaluator_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Evaluator/Evaluations/Show', [
            'evaluation' => $evaluation,
            'application' => $evaluation->application,
            'teacher' => $evaluation->application->user,
        ]);
    }
}
