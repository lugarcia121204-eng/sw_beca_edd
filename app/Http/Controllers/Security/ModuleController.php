<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\StoreModuleRequest;
use App\Http\Requests\Security\UpdateModuleRequest;
use App\Models\Module;
use App\Http\Resources\ModuleResource;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Http\Controllers\SecurityController;

class ModuleController extends SecurityController
{
    use Filterable;
    private Model $model;
    private string $source;
    private string $permissionPrefix;
    private string $routeName;

    public function __construct()
    {
        $this->source = 'SuperAdmin/Security/Modules/';
        $this->model = new Module();
        $this->routeName = 'security.modules.';
        $this->permissionPrefix = 'modules.';

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
        $filters = $this->getFiltersBase($request->query());
        
        $query = $this->model->query()
            ->buscarGlobal($filters->search);

        // Ordenamiento dinámico
        $sortField = $filters->sort_field ?: 'id';
        $sortDirection = $filters->sort_direction ?: 'desc';
        
        $modules = $query->orderBy($sortField, $sortDirection)
            ->paginate($filters->rows)
            ->withQueryString();

        return Inertia::render("{$this->source}Index", [
            'modules'   => ModuleResource::collection($modules),
            'title'     => 'Gestión de Módulos',
            'routeName' => $this->routeName,
            'filters'   => $filters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'          => 'Agregar Módulos',
            'routeName'      => $this->routeName
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModuleRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        Module::create($data);
        return redirect()->route("{$this->routeName}index")->with('success', 'Módulo creado con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Module $module)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'     => 'Editar Módulos',
            'routeName' => $this->routeName,
            'module'    => $module
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        $module->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Módulo modificado con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $module->forceDelete(); // Eliminación permanente
        return redirect()->route("{$this->routeName}index")->with('success', 'Módulo eliminado con éxito');
    }
}