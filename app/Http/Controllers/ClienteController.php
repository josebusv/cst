<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Http\Resources\ClienteResource;
use App\Http\Resources\SedeResource;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Clientes')->only('index');
        $this->middleware('can:Ver Clientes')->only('show');
        $this->middleware('can:Eliminar Clientes')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::with('sedes')->paginate(15);
        return ClienteResource::collection($clientes);
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
            return response()->json([
                'error' => 'Error al crear el cliente',
                'message' => $e->getMessage(),
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
        $cliente->load(['sedes.departamento', 'sedes.municipio']);
        return new ClienteResource($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $validated = $request->validated();

        if (isset($validated['logo'])) {
            $cliente->logo = $validated['logo']->store('logos', 'public');
        }

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
        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado con éxito']);
    }
}
