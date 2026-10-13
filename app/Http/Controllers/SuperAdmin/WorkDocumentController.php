<?php
namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller;
use App\Models\WorkDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
class WorkDocumentController extends Controller
{
    public function index(Request $request) {
        $filters = $request->validate(['rows' => 'nullable|integer|in:5,10,25,50']);
        $search = trim((string) $request->query('search', ''));
        return Inertia::render('SuperAdmin/Catalog/WorkDocuments/Index', [
            'documents' => WorkDocument::query()->when($search, fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%')))->latest()->paginate($filters['rows'] ?? 10)->withQueryString(),
            'filters' => ['search' => $search, 'rows' => $filters['rows'] ?? 10],
        ]);
    }
    private function validated(Request $request, bool $required): array {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['formatos', 'guias', 'convocatoria', 'privacidad'])],
            'published' => ['required', 'boolean'],
            'file' => [$required ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:20480'],
        ]);
    }
    private function fileData(Request $request): array {
        $file = $request->file('file');
        $path = $file->store('work-documents', 'local');
        abort_unless($path, 500, 'No se pudo guardar el archivo.');
        return ['file_path' => $path, 'file_name' => basename($file->getClientOriginalName()), 'file_size' => $file->getSize()];
    }
    public function store(Request $request) {
        $data = $this->validated($request, true); unset($data['file']);
        $file = $this->fileData($request);
        try { WorkDocument::create(array_merge($data, $file)); }
        catch (\Throwable $e) { Storage::disk('local')->delete($file['file_path']); throw $e; }
        return to_route('superadmin.work-documents.index')->with('success', 'Documento agregado.');
    }
    public function update(Request $request, WorkDocument $workDocument) {
        $data = $this->validated($request, false); unset($data['file']);
        $previous = $workDocument->file_path;
        $file = $request->hasFile('file') ? $this->fileData($request) : [];
        try { $workDocument->update(array_merge($data, $file)); }
        catch (\Throwable $e) { if ($file) Storage::disk('local')->delete($file['file_path']); throw $e; }
        if ($file) Storage::disk('local')->delete($previous);
        return back()->with('success', 'Documento actualizado.');
    }
    public function destroy(WorkDocument $workDocument) {
        $path = $workDocument->file_path; $workDocument->delete(); Storage::disk('local')->delete($path);
        return back()->with('success', 'Documento eliminado.');
    }
    public function download(WorkDocument $workDocument) {
        abort_unless(Storage::disk('local')->exists($workDocument->file_path), 404);
        return Storage::disk('local')->download($workDocument->file_path, $workDocument->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }
    public function publicDownload(WorkDocument $workDocument) {
        abort_unless($workDocument->published, 404);
        return $this->download($workDocument);
    }
    public function preview(WorkDocument $workDocument) {
        abort_unless($workDocument->published && strtolower(pathinfo($workDocument->file_name, PATHINFO_EXTENSION)) === 'pdf', 404);
        abort_unless(Storage::disk('local')->exists($workDocument->file_path), 404);
        return response()->file(Storage::disk('local')->path($workDocument->file_path), ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff']);
    }
}
