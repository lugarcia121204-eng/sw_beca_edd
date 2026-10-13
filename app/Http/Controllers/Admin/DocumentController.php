<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\ApplicationResource;
use App\Services\FileService;

class DocumentController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    public function index(Request $request)
    {
        $search = $request->input('search');
        $rows = $request->input('rows', 10);
        $order = $request->input('order', 'created_at');
        $direction = $request->input('direction', 'desc');

        $applications = \App\Models\Application::with(['user.institution', 'announcement']) // Solicitud -> Application
            ->withCount('documents') // documentos -> documents
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('announcement', function ($q) use ($search) { // convocatoria -> announcement
                    $q->where('name', 'like', "%{$search}%"); // nombre -> name
                })
                ->orWhere('id', 'like', "%{$search}%");
            })
            ->when($order, function ($query, $order) use ($direction) {
                if (in_array($order, ['id', 'created_at'])) {
                    $query->orderBy($order, $direction);
                }
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate($rows)
            ->withQueryString();

        return Inertia::render('Admin/Documents/Index', [
            'applications' => \App\Http\Resources\ApplicationResource::collection($applications),
            'filters' => $request->all(['search', 'rows', 'order', 'direction']),
        ]);
    }

    public function show($id)
    {
        $application = \App\Models\Application::with(['user.institution', 'user.priorityArea', 'user.subArea', 'announcement', 'documents'])
            ->findOrFail($id);

        return Inertia::render('Admin/Documents/Show', [
            'application' => (new \App\Http\Resources\ApplicationResource($application))->resolve(),
        ]);
    }

    public function download(\App\Models\Document $document) // Documento -> Document
    {
        return $this->fileService->download($document->file_path, $document->name);
    }

    public function stream(\App\Models\Document $document)
    {
        return $this->fileService->responseFile($document->file_path);
    }
}
