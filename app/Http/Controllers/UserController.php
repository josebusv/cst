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
use App\Support\EmpresaContext;
use App\Support\Pagination;

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
    public function index(Request $request)
    {
        $query = User::with(['sede', 'roles']);

        if (EmpresaContext::esRestringido()) {
            $empresaId = EmpresaContext::empresaId() ?? 0;
            $query->whereHas('sede', fn ($q) => $q->where('empresa_id', $empresaId));
        }

        return UserResource::collection($query->paginate(Pagination::perPage($request)));
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
        EmpresaContext::autorizarEmpresa(optional($user->sede)->empresa_id);
        $user->load(['sede', 'roles']);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        EmpresaContext::autorizarEmpresa(optional($user->sede)->empresa_id);

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('role_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role) {
                $actor = auth()->user();

                if ($role->name === 'Super-Admin' && ! $actor->hasRole('Super-Admin')) {
                    abort(403, 'Solo un Super-Admin puede asignar el rol Super-Admin.');
                }

                if ($user->id === $actor->id) {
                    abort(403, 'No puedes cambiar tus propios roles.');
                }

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
        EmpresaContext::autorizarEmpresa(optional($user->sede)->empresa_id);

        $actor = auth()->user();

        if ($user->id === $actor->id) {
            abort(403, 'No puedes eliminar tu propia cuenta.');
        }

        if ($user->hasRole('Super-Admin') && User::role('Super-Admin')->count() <= 1) {
            abort(403, 'No se puede eliminar al último Super-Admin.');
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * List user by Cliente
     */
    public function usuariosPorEmpresa(Request $request, $empresaId)
    {
        EmpresaContext::autorizarEmpresa($empresaId);

        $usuarios = User::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with(['sede', 'roles'])->paginate(Pagination::perPage($request));

        return UserResource::collection($usuarios);
    }
}
