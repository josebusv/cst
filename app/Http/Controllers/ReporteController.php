<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReporteRequest;
use App\Models\Reporte;
use App\Http\Resources\ReporteResource;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Reportes')->only(['index', 'show', 'reportesPorEquipo']);
        $this->middleware('can:Crear Reportes')->only('store');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Trae los reportes con equipo, sede y empresa (cliente), paginados
        $reportes = Reporte::with([
            'equipo.sede.empresa'
        ])->paginate(10);

        return ReporteResource::collection($reportes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReporteRequest $request)
    {
        $validated = $request->validated();

        $reporte = Reporte::create($validated);
        $reporte->load(['equipo.sede.empresa']);

        return response()->json([
            'message' => 'Reporte creado exitosamente',
            'data' => new ReporteResource($reporte),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reporte $reporte)
    {
        $reporte->load(['equipo.sede.empresa']);
        return new ReporteResource($reporte);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reporte $reporte)
    {
        abort(403, 'Operación no permitida. Los reportes no pueden ser modificados una vez creados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reporte $reporte)
    {
        abort(403, 'Operación no permitida. Los reportes no pueden ser eliminados.');
    }

    /**
     * Obtiene todos los reportes de un equipo específico.
     */
    public function reportesPorEquipo($equipoId)
    {
        $reportes = Reporte::where('equipo_id', $equipoId)
            ->with(['equipo.sede.empresa'])
            ->paginate(10);

        return ReporteResource::collection($reportes);
    }
}
