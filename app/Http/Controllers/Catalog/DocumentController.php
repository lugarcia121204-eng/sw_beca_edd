<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogos\StoreDocumentRequest; // Renamed
use App\Http\Requests\Catalogos\UpdateDocumentRequest; // Renamed
use App\Http\Resources\Catalog\DocumentResource;
use App\Models\Announcement;
use App\Models\CatalogDocument;
use App\Models\Application; // Use Application model
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\FileService;

class DocumentController extends Controller
{
    use Filterable;

    private Model $model;
    private string $source;
    private string $permissionPrefix;
    private string $routeName;
    private \App\Services\CatalogDocumentService $catalogDocumentService;
    protected FileService $fileService;

    public function __construct(\App\Services\CatalogDocumentService $catalogDocumentService, FileService $fileService)
    {
        $this->fileService = $fileService;
        $this->source = 'SuperAdmin/Catalog/Documents/';
        $this->model = new CatalogDocument();
        $this->routeName = 'catalog.documents.';
        $this->permissionPrefix = 'documents.';
        $this->catalogDocumentService = $catalogDocumentService;

        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['update', 'edit']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $documentDeletionLock = $this->getDocumentDeletionLockState();

        $filters = $this->getFiltersBase($request->query());
        $activeTab = $request->query('tab', 'requeridos');

        // Campos permitidos según la pestaña
        $allowedDocFields = ['id', 'name', 'description'];
        $allowedDocenteFields = ['id', 'user_name', 'institution_name', 'announcement_name', 'created_at'];
        $orderDirection = in_array($filters->direction, ['asc', 'desc']) ? $filters->direction : 'asc';

        // Datos para la pestaña de Documentos Requeridos
        if ($activeTab === 'requeridos') {
            $orderField = in_array($filters->order, $allowedDocFields) ? $filters->order : 'name';

            $query = $this->model->query()
                ->when($filters->search, function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters->search}%")
                    ->orWhere('description', 'LIKE', "%{$filters->search}%");
            });

            $documents = $query->orderBy($orderField, $orderDirection)
                ->paginate($filters->rows)
                ->withQueryString();

            $documentsData = [
                'data' => DocumentResource::collection($documents->items())->resolve(),
                'links' => $documents->linkCollection()->toArray(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ];
        }
        else {
            $documentsData = [
                'data' => [],
                'links' => [],
                'from' => 0,
                'to' => 0,
                'total' => 0,
            ];
        }

        // Datos para la pestaña de Documentos de Docentes
        if ($activeTab === 'docentes') {
            $orderField = in_array($filters->order, $allowedDocenteFields) ? $filters->order : 'user_name';

            // Mapa de campo virtual → columna real
            $dbFieldMap = [
                'user_name' => 'users.name',
                'institution_name' => 'institutions.name',
                'announcement_name' => 'announcements.name',
                'created_at' => 'applications.created_at',
                'id' => 'applications.id',
            ];
            $dbField = $dbFieldMap[$orderField] ?? 'users.name';

            $applicationsQuery = Application::withTeacherDetails()
                ->when($filters->search, function ($q) use ($filters) {
                    $q->searchByTeacherDetails($filters->search);
                });

            $applications = $applicationsQuery->orderBy($dbField, $orderDirection)
                ->paginate($filters->rows)
                ->withQueryString();

            $applicationsData = [
                'data' => \App\Http\Resources\ApplicationResource::collection($applications->items())->resolve(),
                'meta' => [
                    'links' => $applications->linkCollection()->toArray(),
                    'from' => $applications->firstItem(),
                    'to' => $applications->lastItem(),
                    'total' => $applications->total(),
                    'per_page' => $applications->perPage(),
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                ]
            ];
        }
        else {
            $applicationsData = [
                'data' => [],
                'meta' => [
                    'links' => [],
                    'from' => 0,
                    'to' => 0,
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1
                ]
            ];
        }

        return Inertia::render("{$this->source}Index", [
            'documents' => $documentsData,
            'applications' => $applicationsData,
            'title' => 'Documentos',
            'routeName' => $this->routeName,
            'filters' => $filters,
            'activeTab' => $activeTab,
            'canDeleteDocuments' => !$documentDeletionLock['blocked'],
            'deleteDocumentsBlockedReason' => $documentDeletionLock['reason'],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar Documento',
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->catalogDocumentService->createDocument($request->validated(), $request);

        return redirect()->route("{$this->routeName}index")->with('success', 'Documento creado con éxito!');
    }

    /**
     * Display the specified resource.
     * Shows details of a teacher's documents (Application)
     */
    public function show($id): Response
    {
        $application = Application::with([
            'user.institution.state', // Updated relationship name
            'user.priorityArea',
            'user.subArea',
            'announcement', // Updated relationship name
            'documents' // Updated relationship name
        ])->findOrFail($id);

        return Inertia::render("{$this->source}Show", [
            'application' => (new \App\Http\Resources\ApplicationResource($application))->resolve(),
            'title' => 'Detalles de Documentos del Profesor',
            'routeName' => $this->routeName
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CatalogDocument $document): Response // Typehint updated

    {
        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar Documento',
            'routeName' => $this->routeName,
            'document' => new DocumentResource($document),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, CatalogDocument $document): RedirectResponse
    {
        $this->catalogDocumentService->updateDocument($document, $request->validated(), $request);
        
        return redirect()->route("{$this->routeName}index")->with('success', 'Documento actualizado con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $documentDeletionLock = $this->getDocumentDeletionLockState();
        if ($documentDeletionLock['blocked']) {
            return back()->withErrors([
                'error' => $documentDeletionLock['reason'],
            ]);
        }

        $document = CatalogDocument::findOrFail($id);

        // Eliminar archivo si existe
        $this->fileService->delete($document->file_path);

        // Usar forceDelete para eliminar permanentemente de la base de datos
        $document->forceDelete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Documento eliminado con éxito');
    }

    /**
     * Determina si la eliminación de documentos debe bloquearse según la etapa
     * de la convocatoria activa.
     */
    private function getDocumentDeletionLockState(): array
    {
        $activeAnnouncement = Announcement::with('calendar')->activa()->latest('id')->first();

        if (!$activeAnnouncement) {
            return [
                'blocked' => false,
                'reason' => null,
            ];
        }

        $currentStage = $activeAnnouncement->current_stage;
        $blockedStages = ['registro', 'evaluacion', 'resultados'];

        if (!in_array($currentStage, $blockedStages, true)) {
            return [
                'blocked' => false,
                'reason' => null,
            ];
        }

        $stageLabel = match ($currentStage) {
            'registro' => 'Registro',
            'evaluacion' => 'Evaluación',
            'resultados' => 'Resultados',
            default => 'Actual',
        };

        return [
            'blocked' => true,
            'reason' => "No se pueden eliminar documentos mientras la convocatoria esté en etapa de {$stageLabel}. Solo es posible eliminarlos cuando la convocatoria esté cerrada.",
        ];
    }

    /**
     * Download the documento file.
     */
    public function download($id)
    {
        $document = CatalogDocument::findOrFail($id);

        return $this->fileService->download($document->file_path, $document->file_name);
    }

    /**
     * Toggle activo status.
     */
    public function toggleActive($id): RedirectResponse
    {
        $document = CatalogDocument::findOrFail($id);
        $newState = !$document->active;
        $document->update(['active' => $newState]);

        // Si se está activando el documento, vincularlo a todas las convocatorias activas
        if ($newState) {
            $activeAnnouncements = Announcement::where('status', 'activa')->pluck('id');
            if ($activeAnnouncements->isNotEmpty()) {
                $syncData = [];
                foreach ($activeAnnouncements as $convId) {
                    $syncData[$convId] = ['is_mandatory' => true];
                }
                $document->announcements()->syncWithoutDetaching($syncData);
            }
        }

        return back()->with('success', 'Estado actualizado correctamente');
    }

    /**
     * Update via status.
     */
    public function updateVia(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'via' => 'required|in:larga,corta,ambas',
        ]);

        $document = CatalogDocument::findOrFail($id);
        $document->update(['via' => $request->via]);

        // Si se cambia la vía, podríamos querer sincronizar el campo 'via' de las convocatorias activas para preservar consistencia
        // en la tabla pivote, pero el RequestController y AnnouncementController deben estar configurados.
        $activeAnnouncements = Announcement::where('status', 'activa')->pluck('id');
        if ($activeAnnouncements->isNotEmpty()) {
            $syncData = [];
            foreach ($activeAnnouncements as $convId) {
                $syncData[$convId] = ['is_mandatory' => true, 'via' => $request->via];
            }
            $document->announcements()->syncWithoutDetaching($syncData);
        }

        return back()->with('success', 'Vía de solicitud actualizada correctamente');
    }

    /**
     * Download a teacher's document (from Application/Solicitud).
     */
    public function downloadDocente(\App\Models\Document $document) // Typehint Document

    {
        return $this->fileService->download($document->file_path, $document->name);
    }

    /**
     * Stream a teacher's document for viewing (from Application/Solicitud).
     */
    public function streamDocente(\App\Models\Document $document)
    {
        return $this->fileService->responseFile($document->file_path);
    }
}
