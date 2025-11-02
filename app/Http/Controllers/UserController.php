<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Usuarios')->only(['index', 'show', 'usuariosPorEmpresa']);
        $this->middleware('can:Crear Usuarios')->only('store');
        $this->middleware('can:Editar Usuarios')->only('update');
        $this->middleware('can:Eliminar Usuarios')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['sede', 'roles'])->paginate(15);
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $role = \Spatie\Permission\Models\Role::find($validated['role_id']);
        if ($role) {
            $user->assignRole($role->name);
        }

        $user->load(['sede', 'roles']);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['sede', 'roles']);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('role_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        $user->load(['sede', 'roles']);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * List user by Cliente
     */
    public function usuariosPorEmpresa($empresaId)
    {
        $usuarios = User::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with(['sede', 'roles'])->paginate(15);

        return UserResource::collection($usuarios);
    }
}
