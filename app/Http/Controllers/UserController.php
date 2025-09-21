<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $users = User::with('sede:id,nombre')
        ->select('id', 'name', 'email', 'telefono', 'sede_id')
        ->paginate(15);

        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'sede' => $user->sede ? $user->sede->nombre : null,
                'roles' => $user->getRoleNames(), // Agrega los roles aquí
            ];
        });

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ]);;

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:20',
            'sede_id' => 'nullable|exists:sedes,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Asignar el rol al usuario
        $role = \Spatie\Permission\Models\Role::find($validated['role_id']);
        if ($role) {
            $user->assignRole($role->name);
        }

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'sede' => $user->sede ? $user->sede->nombre : null,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        Log::info("Mostrando usuario con ID: $id");
        $user = User::with('sede:id,nombre')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'sede' => $user->sede ? $user->sede->nombre : null,
                'roles' => $user->getRoleNames(), // Agrega los roles aquí
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'telefono' => 'nullable|string|max:20',
            'sede_id' => 'nullable|exists:sedes,id',
            'role_id' => 'sometimes|exists:roles,id', // Asegúrate de validar el rol
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = \Hash::make($validated['password']);
        }

        $user->update($validated);

        // Asignar el nuevo rol si viene en la petición
        if ($request->filled('role_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'sede' => $user->sede ? $user->sede->nombre : null,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * List user by Cliente
     */
    public function usuariosPorEmpresa($empresaId)
    {
        $usuarios = \App\Models\User::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
        ->with(['sede' => function($q) {
            $q->select('id', 'nombre', 'empresa_id');
        }])
        ->select('id', 'name', 'email', 'telefono', 'sede_id')
        ->paginate(15);

        $usuarios->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'sede' => $user->sede ? $user->sede->nombre : null,
                'roles' => $user->getRoleNames(), // Agrega los roles aquí
            ];
        });

        return response()->json([
            'data' => $usuarios->items(),
            'meta' => [
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
                'per_page' => $usuarios->perPage(),
                'total' => $usuarios->total(),
                'next_page_url' => $usuarios->nextPageUrl(),
                'prev_page_url' => $usuarios->previousPageUrl(),
            ],
        ]);
    }
}

