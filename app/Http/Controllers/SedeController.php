<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateSedeRequest;
use App\Http\Requests\StoreSedeRequest;
use App\Models\Empresa;
use App\Models\Sede;
use App\Http\Resources\SedeResource;
use App\Support\EmpresaContext;
use App\Support\Pagination;

class SedeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Sedes')->only(['index', 'show']);
        $this->middleware('can:Crear Sedes')->only('store');
        $this->middleware('can:Editar Sedes')->only('update');
        $this->middleware('can:Eliminar Sedes')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sede::with('empresa');

        if (EmpresaContext::esRestringido()) {
            $query->where('empresa_id', EmpresaContext::empresaId() ?? 0);
        }

        return SedeResource::collection($query->paginate(Pagination::perPage($request)));
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
        EmpresaContext::autorizarEmpresa($sede->empresa_id);

        $sede->load(['departamento', 'municipio', 'empresa']);
        return new SedeResource($sede);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSedeRequest $request, Sede $sede)
    {
        EmpresaContext::autorizarEmpresa($sede->empresa_id);

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
        EmpresaContext::autorizarEmpresa($sede->empresa_id);

        $sede->delete();

        return response()->json(['message' => 'Sede eliminada con éxito.']);
    }
}
