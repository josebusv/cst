<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateSedeRequest;
use App\Http\Requests\StoreSedeRequest;
use App\Models\Empresa;
use App\Models\Sede;
use App\Http\Resources\SedeResource;

class SedeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Sedes')->only(['index', 'show']);
        $this->middleware('can:Eliminar Sedes')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresa = auth()->user()->empresa;

        $query = Sede::with('empresa');

        if ($empresa->tipo === 'Cliente') {
            $query->where('empresa_id', $empresa->id);
        }

        $sedes = $query->paginate(15);

        return SedeResource::collection($sedes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSedeRequest $request)
    {
        $validated = $request->validated();

        $sede = Sede::create($validated);
        $sede->load(['departamento', 'municipio', 'empresa']);

        return response()->json([
            'message' => 'Sede creada con éxito.',
            'data' => new SedeResource($sede),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sede $sede)
    {
        $empresa = auth()->user()->empresa;

        // Ensure client users can only see their own sedes
        if ($empresa->tipo === 'Cliente' && $sede->empresa_id !== $empresa->id) {
            abort(403, 'Unauthorized action.');
        }

        $sede->load(['departamento', 'municipio', 'empresa']);
        return new SedeResource($sede);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSedeRequest $request, Sede $sede)
    {
        $empresa = auth()->user()->empresa;

        // Ensure client users can only update their own sedes
        if ($empresa->tipo === 'Cliente' && $sede->empresa_id !== $empresa->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        $sede->update($validated);
        $sede->load(['departamento', 'municipio', 'empresa']);

        return response()->json([
            'message' => 'Sede actualizada con éxito.',
            'data' => new SedeResource($sede),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sede $sede)
    {
        $empresa = auth()->user()->empresa;

        // Ensure client users can only delete their own sedes
        if ($empresa->tipo === 'Cliente' && $sede->empresa_id !== $empresa->id) {
            abort(403, 'Unauthorized action.');
        }

        $sede->delete();

        return response()->json(['message' => 'Sede eliminada con éxito.']);
    }
}
