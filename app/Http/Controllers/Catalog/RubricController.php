<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogos\StoreRubricRequest;
use App\Http\Requests\Catalogos\UpdateRubricRequest;
use App\Http\Resources\Catalog\RubricResource;
use App\Models\Announcement;
use App\Models\Rubric;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RubricController extends Controller
{
    private string $permissionPrefix;
    private string $routeName;

    public function __construct()
    {
        $this->permissionPrefix = 'rubrics.';
        $this->routeName = 'catalog.rubrics.';

        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['update', 'edit', 'toggleActive']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $rows = $request->input('rows', 10);
        $order = $request->input('order', 'id');
        $direction = $request->input('direction', 'desc');

        $rubrics = Rubric::withCount('questions')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy($order, $direction)
            ->paginate($rows)
            ->withQueryString();

        $today = now()->startOfDay();
        $evaluacionEnCurso = Announcement::whereHas('calendar', function ($q) use ($today) {
            $q->whereDate('evaluation_start', '<=', $today)
              ->where(function ($q2) use ($today) {
                  $q2->whereNull('results_start')
                     ->orWhereDate('results_start', '>', $today);
              })
              ->where(function ($q3) use ($today) {
                  $q3->whereNull('results_end')
                     ->orWhereDate('results_end', '>=', $today);
              });
        })->exists();

        return Inertia::render('SuperAdmin/Catalog/Rubrics/Index', [
            'rubrics' => $rubrics,
            'filters' => $request->all(['search', 'rows', 'order', 'direction']),
            'evaluacionEnCurso' => $evaluacionEnCurso,
        ]);
    }

    public function create()
    {
        return Inertia::render('SuperAdmin/Catalog/Rubrics/Create', [
            'title' => 'Agregar Rúbrica',
            'routeName' => 'catalog.rubrics.',
        ]);
    }

    public function store(StoreRubricRequest $request)
    {
        // Check for active rubric if trying to activate this one
        if ($request->is_active) {
            if (Rubric::where('is_active', true)->exists()) {
                // Return error if one is already active
                return back()->withErrors(['is_active' => 'Ya existe una rúbrica activa. Desactívala primero.']);
            }
        }

        DB::transaction(function () use ($request) {
            $rubric = Rubric::create([
                'title' => $request->title,
                'is_active' => $request->is_active ?? false,
            ]);

            if ($request->questions) {
                foreach ($request->questions as $qData) {
                    $question = $rubric->questions()->create(['text' => $qData['text']]);
                    if (isset($qData['options'])) {
                        foreach ($qData['options'] as $oData) {
                            $question->options()->create([
                                'text' => $oData['text'],
                                'score' => $oData['score'],
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('catalog.rubrics.index')->with('success', 'Rúbrica creada correctamente.');
    }

    public function edit(Rubric $rubric)
    {
        $rubric->load('questions.options');

        return Inertia::render('SuperAdmin/Catalog/Rubrics/Edit', [
            'title' => 'Editar Rúbrica',
            'routeName' => 'catalog.rubrics.',
            'rubric' => new RubricResource($rubric),
        ]);
    }

    public function update(UpdateRubricRequest $request, Rubric $rubric)
    {
        // Check for active rubric if trying to activate this one AND it wasn't already active
        if ($request->is_active && !$rubric->is_active) {
            if (Rubric::where('is_active', true)->where('id', '!=', $rubric->id)->exists()) {
                 return back()->withErrors(['is_active' => 'Ya existe una rúbrica activa. Desactívala primero.']);
            }
        }

        DB::transaction(function () use ($request, $rubric) {
            $rubric->update([
                'title' => $request->title,
                'is_active' => $request->is_active,
            ]);

            // Sync Questions (Simple approach: Delete all and recreate. 
            // Better approach: Update existing, Create new, Delete missing.
            // Let's go with the better approach for ID stability if needed, 
            // but for simplicity and robustness given nested structure, delete/recreate 
            // is safer for ensuring consistency unless ID stability is critical.)
            // User requested "actions... edit... delete... add question... add answer".
            // Since we receive the full structure, sync is best.

            // Get IDs of current questions to track deletions
            $currentQuestionIds = $rubric->questions()->pluck('id')->toArray();
            $incomingQuestionIds = [];

            if ($request->questions) {
                foreach ($request->questions as $qData) {
                    if (isset($qData['id'])) {
                        $incomingQuestionIds[] = $qData['id'];
                        $question = $rubric->questions()->find($qData['id']);
                        if ($question) {
                            $question->update(['text' => $qData['text']]);
                            
                            // Sync Options
                            $currentOptionIds = $question->options()->pluck('id')->toArray();
                            $incomingOptionIds = [];

                            if (isset($qData['options'])) {
                                foreach ($qData['options'] as $oData) {
                                    if (isset($oData['id'])) {
                                        $incomingOptionIds[] = $oData['id'];
                                        $question->options()->where('id', $oData['id'])->update([
                                            'text' => $oData['text'],
                                            'score' => $oData['score'],
                                        ]);
                                    } else {
                                        $newOption = $question->options()->create([
                                            'text' => $oData['text'],
                                            'score' => $oData['score'],
                                        ]);
                                        $incomingOptionIds[] = $newOption->id;
                                    }
                                }
                            }
                            // Delete removed options
                            $question->options()->whereNotIn('id', $incomingOptionIds)->delete();
                        }
                    } else {
                        // New Question
                        $question = $rubric->questions()->create(['text' => $qData['text']]);
                        $incomingQuestionIds[] = $question->id;

                        if (isset($qData['options'])) {
                            foreach ($qData['options'] as $oData) {
                                $question->options()->create([
                                    'text' => $oData['text'],
                                    'score' => $oData['score'],
                                ]);
                            }
                        }
                    }
                }
            }
            // Delete removed questions
            $rubric->questions()->whereNotIn('id', $incomingQuestionIds)->delete();
        });

        return redirect()->route('catalog.rubrics.index')->with('success', 'Rúbrica actualizada correctamente.');
    }

    public function destroy(Rubric $rubric)
    {
        $rubric->delete();
        return redirect()->route('catalog.rubrics.index')->with('success', 'Rúbrica eliminada correctamente.');
    }

    public function toggleActive(Rubric $rubric)
    {
        // Block any rubric switch while an announcement is in evaluation stage
        $today = now()->startOfDay();
        $evaluacionEnCurso = Announcement::whereHas('calendar', function ($q) use ($today) {
            $q->whereDate('evaluation_start', '<=', $today)
              ->where(function ($q2) use ($today) {
                  $q2->whereNull('results_start')
                     ->orWhereDate('results_start', '>', $today);
              })
              ->where(function ($q3) use ($today) {
                  $q3->whereNull('results_end')
                     ->orWhereDate('results_end', '>=', $today);
              });
        })->exists();

        if ($evaluacionEnCurso) {
            // Only block if there's already an active rubric — switching is not allowed mid-evaluation.
            // Activating one when none is active is still permitted.
            if (Rubric::where('is_active', true)->exists()) {
                return back()->withErrors(['error' => 'No se puede cambiar la rúbrica activa mientras hay una convocatoria en etapa de Evaluación. Espera a que concluya la evaluación.']);
            }
        }

        if (!$rubric->is_active) {
            if (Rubric::where('is_active', true)->exists()) {
                return back()->withErrors(['error' => 'Ya existe una rúbrica activa. Desactívala primero para activar esta.']);
            }
            $rubric->update(['is_active' => true]);
        } else {
            $rubric->update(['is_active' => false]);
        }

        return back();
    }
}
