<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\User;
use App\Http\Resources\ClienteResource;
use App\Http\Resources\SedeResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use App\Support\EmpresaContext;
use App\Support\Pagination;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Clientes')->only('index');
        $this->middleware('can:Ver Clientes')->only('show');
        $this->middleware('can:Crear Clientes')->only('store');
        $this->middleware('can:Editar Clientes')->only('update');
        $this->middleware('can:Eliminar Clientes')->only('destroy');
        $this->middleware('can:Ver Técnicos')->only('tecnicos');
        $this->middleware('can:Asignar Operadores')->only(['asignarTecnico', 'removerTecnico']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::with('sedes');

        if (EmpresaContext::esRestringido()) {
            $query->where('id', EmpresaContext::empresaId() ?? 0);
        }

        return ClienteResource::collection($query->paginate(Pagination::perPage($request)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $cliente = new Cliente();
            $cliente->nombre = $validated['nombre'];
            $cliente->nit = $validated['nit'];
            $cliente->tipo = 'cliente'; // Asegurar que es un cliente

            if (isset($validated['logo'])) {
                $cliente->logo = $validated['logo']->store('logos', 'public');
            }
            $cliente->save();

            $cliente->sedes()->create([
                'nombre' => $validated['nombresede'],
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'],
                'email' => $validated['email'],
                'departamento_id' => $validated['departamento_id'],
                'municipio_id' => $validated['municipio_id'],
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Error al crear el cliente',
            ], 500);
        }

        $cliente->load('sedes');

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'data' => new ClienteResource($cliente),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        EmpresaContext::autorizarEmpresa($cliente->id);
        $cliente->load(['sedes.departamento', 'sedes.municipio']);
        return new ClienteResource($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        EmpresaContext::autorizarEmpresa($cliente->id);

        $validated = $request->validated();

        // Manejar el logo por separado para evitar sobreescribirlo
        if (isset($validated['logo'])) {
            // Eliminar el logo anterior si existe
            $cliente->deleteLogoFile();
            $cliente->logo = $validated['logo']->store('logos', 'public');
        }

        // Remover el logo de los datos validados para evitar conflictos
        unset($validated['logo']);

        $cliente->fill($validated);
        $cliente->save();

        $cliente->load('sedes');

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'data' => new ClienteResource($cliente),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        EmpresaContext::autorizarEmpresa($cliente->id);

        // Eliminar el archivo de logo si existe
        $cliente->deleteLogoFile();

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado con éxito']);
    }

    public function tecnicos($clienteId)
    {
        EmpresaContext::autorizarEmpresa($clienteId);

        $empresa = Empresa::findOrFail($clienteId);
        $tecnicos = $empresa->tecnicos()->with('roles')->get();
        return UserResource::collection($tecnicos);
    }

    public function asignarTecnico(Request $request, $clienteId)
    {
        EmpresaContext::autorizarEmpresa($clienteId);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $empresa = Empresa::findOrFail($clienteId);
        $empresa->tecnicos()->syncWithoutDetaching([$request->user_id]);

        return response()->json(['message' => 'Técnico asignado exitosamente']);
    }

    public function removerTecnico($clienteId, $userId)
    {
        EmpresaContext::autorizarEmpresa($clienteId);

        $empresa = Empresa::findOrFail($clienteId);
        $empresa->tecnicos()->detach($userId);

        return response()->json(['message' => 'Técnico removido exitosamente']);
    }
}
