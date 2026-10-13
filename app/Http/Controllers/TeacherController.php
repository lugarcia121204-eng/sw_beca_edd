<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    protected \App\Services\ApplicationService $applicationService;
    protected \App\Services\PdfGenerationService $pdfGenerationService;
    protected \App\Services\FileService $fileService;

    public function __construct(\App\Services\ApplicationService $applicationService, \App\Services\PdfGenerationService $pdfGenerationService, \App\Services\FileService $fileService)
    {
        $this->applicationService = $applicationService;
        $this->pdfGenerationService = $pdfGenerationService;
        $this->fileService = $fileService;
    }

    public function inicio(Request $request)
    {
        $query = \App\Models\Application::forCurrentUser()
            ->with(['announcement', 'positionType'])
            ->leftJoin('announcements', 'applications.announcement_id', '=', 'announcements.id')
            ->select('applications.*', 'announcements.name as announcement_name');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('applications.id', 'like', "%{$search}%")
                    ->orWhereHas('announcement', function ($qConv) use ($search) {
                        $qConv->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rows = $request->input('rows', 10);
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Map frontend fields to database columns
        $sortColumn = match ($sortField) {
            'id' => 'applications.id',
            'announcement' => 'announcement_name',
            'created_at' => 'applications.created_at',
            'status' => 'applications.status',
            default => 'applications.created_at',
        };

        $applications = $query->orderBy($sortColumn, $sortDirection)
            ->paginate($rows)
            ->withQueryString();

        return Inertia::render('Teacher/Dashboard', [
            'applications' => \App\Http\Resources\ApplicationResource::collection($applications)->additional([
                'meta' => [
                    'sort_field' => $sortField,
                    'sort_direction' => $sortDirection,
                ]
            ]),
            'filters' => $request->only(['search', 'rows', 'sort_field', 'sort_direction']),
        ]);
    }

    public function show($id)
    {
        $application = \App\Models\Application::forCurrentUser()
            ->with(['announcement', 'documents', 'user.institution.state', 'user.priorityArea', 'user.subArea', 'positionType'])
            ->findOrFail($id);

        return Inertia::render('Teacher/Applications/Show', [
            'application' => (new \App\Http\Resources\ApplicationResource($application))->resolve(),
        ]);
    }

    public function download($id)
    {
        $document = \App\Models\Document::findOrFail($id);

        // Check ownership via Application
        $application = \App\Models\Application::where('id', $document->application_id)
            ->forCurrentUser()
            ->firstOrFail();

        return $this->fileService->download($document->file_path, $document->name);
    }

    public function stream($id)
    {
        $document = \App\Models\Document::findOrFail($id);

        $application = \App\Models\Application::where('id', $document->application_id)
            ->forCurrentUser()
            ->firstOrFail();

        return $this->fileService->responseFile($document->file_path);
    }

    public function convocatorias()
    {
        // Fetch active announcements
        $userId = auth()->id();
        $announcements = \App\Models\Announcement::activa()
            ->with([
                'calendar',
                'applications' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Teacher/Announcements/Index', [
            'announcements' => \App\Http\Resources\Catalog\AnnouncementResource::collection($announcements),
            'has_active_application' => \App\Models\Application::forCurrentUser()
                ->pending()
                ->exists(),
        ]);
    }

    public function previewAnnouncement($id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $announcement = \App\Models\Announcement::activa()->findOrFail($id);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $path = $announcement->file_path;

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function solicitar($id)
    {
        $announcement = \App\Models\Announcement::with(['catalogDocuments', 'calendar'])->findOrFail($id);

        $eligibilityError = $this->applicationService->checkApplicationEligibility(auth()->id(), $id);

        if ($eligibilityError) {
            return redirect()->route('teacher.dashboard')->with('error', $eligibilityError);
        }


        // Get documents for this announcement (from catalog)
        $documents = $announcement->catalogDocuments()
            ->active()
            ->ordered()
            ->get();

        // Fallback: si la convocatoria no tiene documentos asignados específicamente,
        // usar todos los documentos activos del catálogo como requeridos.
        if ($documents->isEmpty()) {
            $documents = \App\Models\CatalogDocument::active()->ordered()->get();
        }

        // Get previous application documents for auto-fill
        // We look for the latest *submitted* application (pending/approved/rejected doesn't matter, as long as it has files)
        $previousApp = \App\Models\Application::forCurrentUser()
            ->whereNotNull('status') // ensuring it was submitted
            ->latest()
            ->with('documents')
            ->first();

        $previousDocuments = $previousApp ? $previousApp->documents : [];

        return Inertia::render('Teacher/Announcements/Apply', [
            'announcement' => (new \App\Http\Resources\Catalog\AnnouncementResource($announcement))->resolve(),
            'catalog_documents' => \App\Http\Resources\Catalog\DocumentResource::collection($documents)->resolve(),
            'previous_documents' => $previousDocuments,
            'position_types' => \App\Models\PositionType::all(['id', 'code', 'name']),
        ]);
    }

    public function storeApplication(\App\Http\Requests\StoreApplicationRequest $request)
    {
        try {
            $this->applicationService->createApplication(
                auth()->id(),
                $request->announcement_id,
                $request->position_type_id,
                $request->file('files'),
                $request->file_types,
                $request->reused_documents,
                $request->via
            );

            return redirect()->route('teacher.dashboard')->with('success', 'Solicitud enviada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['files' => $e->getMessage()]);
        }
    }
    public function downloadAcceptance($id)
    {
        $application = \App\Models\Application::forCurrentUser()
            ->where('id', $id)
            ->approved()
            ->with(['announcement.calendar', 'user'])
            ->firstOrFail();

        // Bloquear descarga si todavía no estamos en resultados
        if ($application->announcement) {
            $stage = $application->announcement->current_stage;
            if (!in_array($stage, ['resultados', 'terminada'])) {
                abort(403, 'Aún no es la etapa de resultados. No puedes descargar la carta de aceptación.');
            }
        }

        try {
            return $this->pdfGenerationService->generateAcceptanceLetter($application);
        } catch (\Exception $e) {
            return response()->view('errors.pdf_missing', ['message' => $e->getMessage()], 404);
        }
    }
}
