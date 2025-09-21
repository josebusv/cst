<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Sede;

class SedeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $empresa = auth()->user()->empresa;

        if ($empresa->tipo === 'Cliente') {
            $sedes = Sede::with('empresa:id,nombre')
            ->select('id', 'nombre', 'direccion', 'telefono', 'empresa_id', 'principal')
            ->where('empresa_id', $empresa->id)
            ->paginate(15);
        }else {
            $sedes = Sede::with('empresa:id,nombre')
            ->select('id', 'nombre', 'direccion', 'telefono', 'empresa_id', 'principal')
            ->paginate(15);
        }

        $sedes->getCollection()->transform(function ($sede) {
            return [
                'id' => $sede->id,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'principal' => $sede->principal,
                'empresa' => $sede->empresa ? $sede->empresa->nombre : null,
            ];
        });

        return response()->json([
            'data' => $sedes->items(),
            'meta' => [
                'current_page' => $sedes->currentPage(),
                'last_page' => $sedes->lastPage(),
                'per_page' => $sedes->perPage(),
                'total' => $sedes->total(),
                'next_page_url' => $sedes->nextPageUrl(),
                'prev_page_url' => $sedes->previousPageUrl(),
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
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'municipio_id' => 'nullable|exists:municipios,id',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        //$empresa = auth()->user()->empresa->id;

        Sede::create($validated);

        return response()->json(['message' => 'Sede creada con éxito.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $empresa = auth()->user()->empresa;

        if ($empresa->tipo === 'Cliente') {
            $sede = Sede::with(['departamento:id,nombre', 'municipio:id,nombre'])
                ->where('empresa_id', $empresa->id)
                ->findOrFail($id);
        } else {
            $sede = Sede::with(['departamento:id,nombre', 'municipio:id,nombre'])
                ->findOrFail($id);
        }

        return response()->json([
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'direccion' => $sede->direccion,
            'telefono' => $sede->telefono,
            'email' => $sede->email,
            'principal' => $sede->principal,
            'empresa' => $sede->empresa ? $sede->empresa->nombre : null,
            'departamento_id' => $sede->departamento_id,
            'departamento' => $sede->departamento ? $sede->departamento->nombre : null,
            'municipio_id' => $sede->municipio_id,
            'municipio' => $sede->municipio ? $sede->municipio->nombre : null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'municipio_id' => 'nullable|exists:municipios,id',
        ]);

        $empresa = auth()->user()->empresa;

        if ($empresa->tipo === 'Cliente') {
            $sede = Sede::where('empresa_id', $empresa->id)->findOrFail($id);
        }else {
            $sede = Sede::findOrFail($id);
        }

        $sede->update($validated);

        return response()->json(['message' => 'Sede actualizada con éxito.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empresa = auth()->user()->empresa;

        if ($empresa->tipo === 'Cliente') {
            $sede = Sede::where('empresa_id', $empresa->id)->findOrFail($id);
        }else {
            $sede = Sede::findOrFail($id);
        }

        $sede->delete();

        return response()->json(['message' => 'Sede eliminada con éxito.']);
    }
}
