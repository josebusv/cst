<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions:id,name')->get();

        $rolesWithPermissions = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions
            ];
        });

        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $requestedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $requestedData['name']
        ]);

        if (isset($requestedData['permissions'])) {
            $role->syncPermissions($requestedData['permissions']);
        }

        return response()->json($role, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $roles = Role::with('permissions:id,name')->findOrFail($id);

        $rolesWithPermissions = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions
            ];
        });

        return response()->json($roles);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $requestedData = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $requestedData['name'];
        $role->save();

        if (isset($requestedData['permissions'])) {
            $role->syncPermissions($requestedData['permissions']);
        }

        return response()->json($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(null, 204);
    }
}
