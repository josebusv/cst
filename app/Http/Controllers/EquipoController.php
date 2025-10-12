<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateEquipoRequest;
use App\Http\Requests\StoreEquipoRequest;
use App\Models\Equipo;
use App\Http\Resources\EquipoResource;

class EquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Equipos')->only(['index', 'show', 'equiposPorEmpresa']);
        $this->middleware('can:Eliminar Equipos')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipos = Equipo::with('sede')->paginate(15);
        return EquipoResource::collection($equipos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipoRequest $request)
    {
        $validated = $request->validated();

        $equipo = Equipo::create($validated);
        $equipo->load('sede');

        return response()->json([
            'message' => 'Equipo creado exitosamente',
            'data' => new EquipoResource($equipo),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipo $equipo)
    {
        $equipo->load('sede');
        return new EquipoResource($equipo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipoRequest $request, Equipo $equipo)
    {
        $validated = $request->validated();

        $equipo->update($validated);
        $equipo->load('sede');

        return response()->json([
            'message' => 'Equipo actualizado exitosamente',
            'data' => new EquipoResource($equipo),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipo $equipo)
    {
        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado exitosamente']);
    }

    /**
     * Display a listing of the resource by company.
     */
    public function equiposPorEmpresa($empresaId)
    {
        $equipos = Equipo::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with('sede')->paginate(15);

        return EquipoResource::collection($equipos);
    }
}
