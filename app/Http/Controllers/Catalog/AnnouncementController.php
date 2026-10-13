<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogos\StoreAnnouncementRequest;
use App\Http\Requests\Catalogos\UpdateAnnouncementRequest;
use App\Http\Requests\Catalogos\SyncAnnouncementDocumentsRequest;
use App\Http\Resources\Catalog\AnnouncementResource;
use App\Models\Announcement;
use App\Models\CatalogDocument;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\FileService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnouncementController extends Controller
{
    use Filterable;

    private string $routeName;
    protected \App\Services\AnnouncementService $announcementService;
    protected FileService $fileService;

    public function __construct(\App\Services\AnnouncementService $announcementService, FileService $fileService)
    {
        $this->announcementService = $announcementService;
        $this->fileService = $fileService;
        $this->source = 'SuperAdmin/Announcements/';
        $this->model = new Announcement();
        $this->routeName = 'announcements.';
        $this->permissionPrefix = 'announcements.';

        // Middleware de permisos
        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['update', 'edit', 'updateDocumentos']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
        $this->middleware("permission:{$this->permissionPrefix}index")->only(['download', 'preview']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $this->getFiltersBase($request->query());

        $query = $this->model->query()
            ->with('calendar')
            ->buscarGlobal($filters->search);

        // Ordenamiento dinámico
        $announcements = $query->orderBy($filters->order, $filters->direction ?? 'desc')
            ->paginate($filters->rows)
            ->withQueryString();

        // Check restriction: active or pending
        $hasActiveOrPending = Announcement::whereIn('status', ['activa', 'pendiente'])->exists();

        return Inertia::render($this->source . 'Index', [
            'announcements' => AnnouncementResource::collection($announcements),
            'title' => 'Gestión de Convocatorias',
            'routeName' => $this->routeName,
            'filters' => $filters,
            'canCreate' => !$hasActiveOrPending && auth()->user()->can($this->permissionPrefix . 'create'),
            'restrictionMessage' => $hasActiveOrPending ? 'Ya existe una convocatoria Activa o Pendiente.' : 'No tienes permisos para crear convocatorias.',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Cargar documentos del catálogo disponibles
        $catalogDocuments = CatalogDocument::active()->ordered()->get();

        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar Convocatoria',
            'routeName' => $this->routeName,
            'requiredDocuments' => \App\Http\Resources\Catalog\DocumentResource::collection($catalogDocuments)->resolve(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->announcementService->createAnnouncement($request->validated(), $request);

        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement): Response
    {
        // Cargar documentos del catálogo y documentos vinculados
        $catalogDocuments = CatalogDocument::active()->ordered()->get();
        $linkedDocuments = $announcement->catalogDocuments->pluck('id')->toArray();

        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar Convocatoria',
            'routeName' => $this->routeName,
            'announcement' => new AnnouncementResource($announcement->load('calendar')),
            'requiredDocuments' => \App\Http\Resources\Catalog\DocumentResource::collection($catalogDocuments)->resolve(),
            'linkedDocuments' => $linkedDocuments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->announcementService->updateAnnouncement($announcement, $request->validated(), $request);

        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria actualizada con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->announcementService->deleteAnnouncement($announcement);

        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria eliminada con éxito');
    }

    /**
     * Download the announcement file.
     */
    public function preview(Request $request, Announcement $announcement): BinaryFileResponse
    {
        $path = $request->query('type') === 'image'
            ? $announcement->image_path
            : $announcement->file_path;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Announcement $announcement)
    {
        return $this->fileService->download(
            $announcement->file_path,
            $announcement->file_name
        );
    }

    /**
     * Update the documents linked to the announcement.
     */
    public function updateDocumentos(SyncAnnouncementDocumentsRequest $request, Announcement $announcement): RedirectResponse
    {
        // Sincronizar documentos vinculados
        $documents = $request->input('documents', []);
        $syncData = [];

        $catalogDocs = CatalogDocument::whereIn('id', $documents)->get(['id']);
        foreach ($catalogDocs as $doc) {
            $syncData[$doc->id] = ['is_mandatory' => true, 'via' => 'ambas']; // Por defecto obligatorio y ambas vías
        }

        $announcement->catalogDocuments()->sync($syncData);

        return redirect()->route("{$this->routeName}edit", $announcement->id)
            ->with('success', 'Documentos actualizados correctamente');
    }
}
