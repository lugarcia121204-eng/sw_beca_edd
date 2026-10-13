<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Catalogos\StorePriorityAreaRequest;
use App\Http\Requests\Catalogos\UpdatePriorityAreaRequest;
use App\Http\Requests\Catalogos\ImportPriorityAreasRequest;
use App\Http\Resources\Catalog\PriorityAreaResource;
use App\Models\PriorityArea;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PriorityAreaController extends Controller
{
    use Filterable;

    private Model $model;
    private string $source;
    private string $permissionPrefix;
    private string $routeName;

    public function __construct()
    {
        $this->source = 'SuperAdmin/Catalog/PriorityAreas/';
        $this->model = new PriorityArea();
        $this->routeName = 'catalog.priority-areas.';
        $this->permissionPrefix = 'priority_areas.';

        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['update', 'edit']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $filters = $this->getFiltersBase($request->query());

        // Campos permitidos para ordenamiento (el frontend puede enviar 'nombre' o 'name')
        $allowedFields = ['id', 'name', 'nombre'];
        $orderField = in_array($filters->order, $allowedFields) ? $filters->order : 'name';
        // Mapear alias frontend -> columna real en BD
        if ($orderField === 'nombre') {
            $orderField = 'name';
        }
        $orderDirection = in_array($filters->direction, ['asc', 'desc']) ? $filters->direction : 'asc';

        $query = $this->model->query()->buscarGlobal($filters->search);

        $priorityAreas = $query->orderBy($orderField, $orderDirection)
            ->paginate($filters->rows)
            ->withQueryString();

        return Inertia::render("{$this->source}Index", [
            'priorityAreas' => PriorityAreaResource::collection($priorityAreas),
            'title' => 'Áreas Prioritarias',
            'routeName' => $this->routeName,
            'filters' => $filters
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar Área Prioritaria',
            'routeName' => $this->routeName,
        ]);
    }

    public function store(StorePriorityAreaRequest $request): RedirectResponse
    {
        PriorityArea::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Área Prioritaria creada con éxito!');
    }

    public function show(PriorityArea $priorityArea)
    {
        abort(404);
    }

    public function edit(PriorityArea $priorityArea): Response
    {
        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar Área Prioritaria',
            'routeName' => $this->routeName,
            'priorityArea' => new PriorityAreaResource($priorityArea),
        ]);
    }

    public function update(UpdatePriorityAreaRequest $request, PriorityArea $priorityArea): RedirectResponse
    {
        $priorityArea->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Área Prioritaria actualizada con éxito!');
    }

    public function destroy($id): RedirectResponse
    {
        $priorityArea = PriorityArea::findOrFail($id);
        $priorityArea->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Área Prioritaria eliminada con éxito');
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PriorityAreasExport, 'areas-prioritarias.xlsx');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PriorityAreasTemplateExport, 'plantilla_areas_prioritarias.xlsx');
    }

    public function import(ImportPriorityAreasRequest $request)
    {
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\PriorityAreasImport, $request->file('file'));

        return redirect()->back()->with('success', 'Áreas Prioritarias importadas correctamente.');
    }
}
