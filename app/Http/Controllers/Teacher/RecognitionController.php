<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Recognition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PdfGenerationService;

class RecognitionController extends Controller
{
    protected PdfGenerationService $pdfGenerationService;

    public function __construct(PdfGenerationService $pdfGenerationService)
    {
        $this->pdfGenerationService = $pdfGenerationService;

        $this->middleware("permission:teacher.recognitions.index")->only(['index']);
    }

    /**
     * Display a listing of the teacher's recognitions.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sortField = $request->input('sort_field', 'sent_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $sortColumn = match ($sortField) {
            'id' => 'recognitions.id',
            'announcement' => 'announcements.name',
            'sent_at' => 'sent_at',
            default => 'sent_at',
        };

        // Obtener los reconocimientos del docente actual, donde activos
        $recognitionsQuery = Recognition::with('announcement.calendar')
            ->leftJoin('announcements', 'recognitions.announcement_id', '=', 'announcements.id')
            ->select('recognitions.*')
            ->where('user_id', $user->id)
            ->where('recognitions.type', 'postulante')
            ->where('recognitions.active', true)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('applications')
                    ->whereColumn('applications.user_id', 'recognitions.user_id')
                    ->whereColumn('applications.announcement_id', 'recognitions.announcement_id')
                    ->where('applications.status', 'approved');
            })
            ->orderBy($sortColumn, $sortDirection)
            ->get();

        // Filtrar en memoria: permitir reconocimientos de convocatorias que
        // tengan definida una etapa de resultados (incluye actuales, pasadas y planificadas)
        $filteredRecognitions = $recognitionsQuery->filter(function ($recognition) {
            if (!$recognition->announcement || !$recognition->announcement->calendar) return false;
            return !is_null($recognition->announcement->calendar->results_start);
        });

        // Paginación manual del collection ya que Inertia espera paginado
        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredRecognitions->forPage($page, $perPage)->values(),
            $filteredRecognitions->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Teacher/Recognitions/Index', [
            'recognitions' => $paginatedItems,
            'filters' => $request->all(['page', 'search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Download the specified recognition for the teacher.
     */
    public function download(Request $request, Recognition $recognition)
    {
        $user = $request->user();

        // Asegurar que el reconocimiento pertenece al docente actual y es de tipo postulante
        if ($recognition->user_id !== $user->id || !$recognition->active || $recognition->type !== 'postulante') {
            abort(403, 'No tienes permiso para descargar este reconocimiento, no está activo, o es de evaluador.');
        }

        // Ensure relations are loaded
        $recognition->loadMissing('announcement.calendar');

        if ($recognition->announcement) {
            $stage = $recognition->announcement->current_stage;
            if (!in_array($stage, ['resultados', 'terminada'])) {
                abort(403, 'Aún no es la etapa de resultados. No puedes descargar el reconocimiento.');
            }
        }

        $isApproved = Application::where('user_id', $recognition->user_id)
            ->where('announcement_id', $recognition->announcement_id)
            ->where('status', 'approved')
            ->exists();

        if (!$isApproved) {
            abort(403, 'Solo los docentes aprobados pueden descargar este reconocimiento.');
        }

        try {
            return $this->pdfGenerationService->generateTeacherRecognitionPdf($recognition, $user);
        } catch (\Throwable $e) {
            return response()->view('errors.pdf_missing', ['message' => $e->getMessage()], 404);
        }
    }
}
