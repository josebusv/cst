<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Roles')->only(['index', 'show']);
        $this->middleware('can:Crear Roles')->only('store');
        $this->middleware('can:Editar Roles')->only('update');
        $this->middleware('can:Eliminar Roles')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $requestedData = $request->validated();

        $role = Role::create([
            'name' => $requestedData['name']
        ]);

        if (isset($requestedData['permissions'])) {
            $role->syncPermissions($requestedData['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'data' => new RoleResource($role),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $requestedData = $request->validated();

        $role->name = $requestedData['name'];
        $role->save();

        if (isset($requestedData['permissions'])) {
            $role->syncPermissions($requestedData['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Rol eliminado exitosamente'], 200);
    }
}
