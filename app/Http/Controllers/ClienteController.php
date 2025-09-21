<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::with('sedes')->select('id', 'nombre', 'logo')
            ->paginate(15);

        $clientes->getCollection()->transform(function ($cliente) {
            return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'logo' => $cliente->logo,
                    'sedes' => $cliente->sedes->map(function ($sede) {
                        return [
                                'id' => $sede->id,
                                'nombre' => $sede->nombre,
                                'direccion'=> $sede->direccion,
                                'telefono'=> $sede->telefono,
                                'email'=> $sede->email,
                                'tipo_sede' => $sede->principal ? 'Principal' : 'Secundaria',
                        ];
                    }),
                ];
            });
        
        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'next_page_url' => $clientes->nextPageUrl(),
                'prev_page_url' => $clientes->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'required|integer|unique:empresas,nit',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'nombresede' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'departamento_id'   =>  'required|integer|exists:departamentos,id',
            'municipio_id'      =>  'required|integer|exists:municipios,id',
        ]);
        DB::beginTransaction();
        try {
            $cliente = new Cliente();
            $cliente->nombre = $validated['nombre'];
            $cliente->nit = $validated['nit'];
            $cliente->logo = $validated['logo']->store('logos', 'public');
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

        return response()->json([
            'data' => $cliente,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with(['sedes.departamento', 'sedes.municipio'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'logo' => $cliente->logo ? asset('storage/' . $cliente->logo) : null,
                'sedes' => $cliente->sedes->map(function ($sede) {
                    return [
                        'id' => $sede->id,
                        'nombre' => $sede->nombre,
                        'direccion' => $sede->direccion,
                        'telefono' => $sede->telefono,
                        'email' => $sede->email,
                        'departamento' => $sede->departamento ? $sede->departamento->nombre : null,
                        'municipio' => $sede->municipio ? $sede->municipio->nombre : null,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'nit' => 'sometimes|required|integer|unique:empresas,nit,' . $cliente->id,
            'logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if (isset($validated['logo'])) {
            $cliente->logo = $validated['logo']->store('logos', 'public');
        }

        $cliente->fill($validated);
        $cliente->save();

        return response()->json([
            'data' => $cliente,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado con éxito']);
    }
}
