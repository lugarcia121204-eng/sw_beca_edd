<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\StorePermissionRequest;
use App\Http\Requests\Security\UpdatePermissionRequest;
use Illuminate\Http\Request;
use App\Http\Resources\PermissionResource;
use App\Models\Module;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Http\Controllers\SecurityController;

class PermissionController extends SecurityController
{
    use Filterable;
    protected string $routeName;
    protected string $permissionPrefix;
    protected string $source;
    protected Model $model;

    public function __construct()
    {
        $this->routeName = "security.permissions.";
        $this->permissionPrefix = "permissions.";
        $this->source    = "SuperAdmin/Security/Permissions/";
        $this->model     = new Permission();

        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['edit', 'update']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $filters = $this->getFiltersBase($request->query());
        
        $query = $this->model->query()
            ->buscarGlobal($filters->search);

        // Ordenamiento dinámico
        $sortField = $filters->sort_field ?: 'id';
        $sortDirection = $filters->sort_direction ?: 'desc';
        
        $permissions = $query->orderBy($sortField, $sortDirection)
            ->paginate($filters->rows)
            ->withQueryString();

        return Inertia::render("{$this->source}Index", [
            'permissions'      => PermissionResource::collection($permissions),
            'title'         => 'Gestión de Permisos',
            'routeName'     => $this->routeName,
            'filters'       => $filters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'     => 'Agregar Permisos',
            'routeName' => $this->routeName,
            'modules'   => Module::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->model::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Permiso creado con éxito!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission): Response
    {
        return Inertia::render("{$this->source}Edit", [
            'title'         => 'Editar Permisos',
            'routeName'     => $this->routeName,
            'modules'       => Module::orderBy('name')->get(),
            'permission'    => $permission,
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());
        Cache::forget('permissions');
        return redirect()->route("{$this->routeName}index")->with('success', 'Permiso modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->forceDelete(); // Eliminación permanente
        return redirect()->route("{$this->routeName}index")->with('success', 'Permiso eliminado con éxito');
    }
}