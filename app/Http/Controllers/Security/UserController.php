<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\StoreUserRequest;
use App\Http\Requests\Security\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Inertia\Response;
use Inertia\Inertia;
use App\Traits\HasOrderableRelations;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Http\Controllers\SecurityController;

class UserController extends SecurityController
{
    use Filterable;
    use HasOrderableRelations;
    protected string $routeName;
    protected string $permissionPrefix;
    protected string $source;
    protected Model $model;

    public function __construct()
    {
        $this->routeName = "security.users.";
        $this->permissionPrefix = "users.";
        $this->source = "SuperAdmin/Security/Users/";
        $this->model = new User();

        $this->middleware("permission:{$this->permissionPrefix}index")->only(['index', 'show']);
        $this->middleware("permission:{$this->permissionPrefix}create")->only(['store', 'create']);
        $this->middleware("permission:{$this->permissionPrefix}edit")->only(['edit', 'update']);
        $this->middleware("permission:{$this->permissionPrefix}delete")->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $filters = $this->getFiltersBase($request->query());
        $roleFilter = $request->query('role_id');

        $query = $this->model->query()
            ->with('roles')
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'Super Admin'))
            ->buscarGlobal($filters->search)
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->whereHas('roles', function ($query) use ($roleFilter) {
                    $query->where('roles.id', $roleFilter);
                });
            })
            ->when($filters->withTrashed, fn($q) => $q->withTrashed());

        // Ordenamiento dinámico
        $sortField = $filters->sort_field ?: 'id';
        $sortDirection = $filters->sort_direction ?: 'desc';

        $users = $query->orderBy($sortField, $sortDirection)
            ->paginate($filters->rows)
            ->withQueryString()
            ->appends(['role_id' => $roleFilter]);

        $roles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();
        $rolesForImport = Role::where('name', 'Evaluador')->orderBy('name')->get();

        return Inertia::render("{$this->source}Index", [
            'users' => UserResource::collection($users),
            'title' => 'Gestión de Usuarios',
            'routeName' => $this->routeName,
            'filters' => $filters,
            'roles' => $roles,
            'rolesForImport' => $rolesForImport,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['Admin', 'Evaluador', 'Docente'])->orderBy('name')->get();
        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar Usuarios',
            'routeName' => $this->routeName,
            'roles' => $roles,
        ]);
    }


    public function store(StoreUserRequest $request)
    {
        $user = $this->model::create($request->validated());
        $user->syncRoles($request->roles);

        return redirect()->route("{$this->routeName}index")->with('success', 'Usuario creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['Admin', 'Evaluador', 'Docente'])->orderBy('name')->get();
        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar Usuarios',
            'routeName' => $this->routeName,
            'user' => new UserResource($user->load('roles')),
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($request->roles);
        return redirect()->route("{$this->routeName}index")->with('success', 'Usuario modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->forceDelete(); // Eliminación permanente
        return redirect()->route("{$this->routeName}index")->with('success', 'Usuario eliminado con éxito');
    }

    protected function getOrderableRelations(): array
    {
        return [
            'roles' => ['roles', 'id', 'name'],
        ];
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersExport, 'usuarios.xlsx');
    }

    public function template()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersTemplateExport, 'plantilla_usuarios.xlsx');
    }

    public function import(\App\Http\Requests\Security\ImportUsersRequest $request)
    {
        // Obtener automáticamente el rol de Evaluador
        $evaluadorRole = Role::where('name', 'Evaluador')->first();

        if (!$evaluadorRole) {
            return redirect()->back()->with('error', 'No se encontró el rol de Evaluador.');
        }

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UsersImport($evaluadorRole->id), $request->file('file'));

        return redirect()->back()->with('success', 'Usuarios importados correctamente.');
    }
}