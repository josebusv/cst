<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipos = Equipo::with('sede:id,nombre')
            ->select('id', 'equipo', 'sede_id', 'modelo', 'marca', 'serie', 'fabricante', 'registro_invima', 'pais_origen', 'ubicacion', 'inventario', 'code_ecri')
            ->paginate(15);

        $equipos->getCollection()->transform(function ($equipo) {
            return [
                'id' => $equipo->id,
                'nombre' => $equipo->nombre,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'serie' => $equipo->serie,
                'fabricante' => $equipo->fabricante,
                'registro_invima' => $equipo->registro_invima,
                'pais_origen' => $equipo->pais_origen,
                'ubicacion' => $equipo->ubicacion,
                'inventario' => $equipo->inventario,
                'code_ecri' => $equipo->code_ecri,
                'sede' => $equipo->sede ? $equipo->sede->nombre : null,
            ];
        });

        return response()->json([
            'data' => $equipos->items(),
            'meta' => [
                'current_page' => $equipos->currentPage(),
                'last_page' => $equipos->lastPage(),
                'per_page' => $equipos->perPage(),
                'total' => $equipos->total(),
                'next_page_url' => $equipos->nextPageUrl(),
                'prev_page_url' => $equipos->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'equipo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'required|string|max:255',
            'fabricante' => 'required|string|max:255',
            'registro_invima' => 'nullable|string|max:255',
            'pais_origen' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'inventario' => 'nullable|string|max:255',
            'code_ecri' => 'nullable|string|max:255',
        ]);

        $equipo = Equipo::create($validated);

        return response()->json(['message' => 'Equipo creado exitosamente', 'data' => $equipo], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $equipo = Equipo::with('sede:id,nombre')->find($id);

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $equipo->id,
                'equipo' => $equipo->equipo,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'serie' => $equipo->serie,
                'servicio' => $equipo->servicio,
                'fabricante' => $equipo->fabricante,
                'registro_invima' => $equipo->registro_invima,
                'pais_origen' => $equipo->pais_origen,
                'ubicacion' => $equipo->ubicacion,
                'inventario' => $equipo->inventario,
                'code_ecri' => $equipo->code_ecri,
                'sede' => $equipo->sede ? $equipo->sede->nombre : null,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'equipo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'required|string|max:255',
            'servicio' => 'required|string|max:255',
            'fabricante' => 'required|string|max:255',
            'registro_invima' => 'nullable|string|max:255',
            'pais_origen' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'inventario' => 'nullable|string|max:255',
            'code_ecri' => 'nullable|string|max:255',
        ]);

        $equipo = Equipo::find($id);

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        $equipo->update($validated);

        return response()->json(['message' => 'Equipo actualizado exitosamente', 'data' => $equipo]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $equipo = Equipo::find($id);
        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado exitosamente']);
    }

    /**
     * Display a listing of the resource by company.
     */
    public function equiposPorEmpresa($empresaId)
    {
        $equipos = \App\Models\Equipo::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
        ->with(['sede' => function($q) {
            $q->select('id', 'nombre', 'empresa_id');
        }])
        ->paginate(15);

        $equipos->getCollection()->transform(function ($equipo) {
            return [
                'id' => $equipo->id,
                'equipo' => $equipo->equipo,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'serie' => $equipo->serie,
                'servicio' => $equipo->servicio,
                'fabricante' => $equipo->fabricante,
                'registro_invima' => $equipo->registro_invima,
                'pais_origen' => $equipo->pais_origen,
                'ubicacion' => $equipo->ubicacion,
                'inventario' => $equipo->inventario,
                'code_ecri' => $equipo->code_ecri,
                'sede' => $equipo->sede ? $equipo->sede->nombre : null,
            ];
        });

        return response()->json([
            'data' => $equipos->items(),
            'meta' => [
                'current_page' => $equipos->currentPage(),
                'last_page' => $equipos->lastPage(),
                'per_page' => $equipos->perPage(),
                'total' => $equipos->total(),
                'next_page_url' => $equipos->nextPageUrl(),
                'prev_page_url' => $equipos->previousPageUrl(),
            ],
        ]);
    }
}
