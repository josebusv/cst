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
        $this->middleware('can:No Permitido')->only(['update', 'destroy']); // These methods are not meant to be used
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
        $baseRules = [
            'equipo_id' => 'required|exists:equipos,id',
            'tipo_reporte' => 'required|string|max:20',
            'correctivo' => 'nullable|string',
            'preventivo' => 'nullable|string',
            'fuerea_servicio' => 'nullable|string',
            'requerido_cliente' => 'nullable|string',
            'falla_reportada' => 'nullable|string',
            'limpieza_interna' => 'sometimes|boolean',
            'limpieza_externa' => 'sometimes|boolean',
            'lubricacion' => 'sometimes|boolean',
            'ajuste_angulacion' => 'sometimes|boolean',
            'prueba_fugas' => 'sometimes|boolean',
            'ajuste_general' => 'sometimes|boolean',
            'cable_paciente' => 'sometimes|boolean',
            'verificacion_software' => 'sometimes|boolean',
            'filtros' => 'sometimes|boolean',
            'verificacion_general' => 'sometimes|boolean',
            'reemplazo_insumo' => 'sometimes|boolean',
            'baterias' => 'sometimes|boolean',
            'servicio_realizado' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'firma_tecnico' => 'nullable|string',
            'nombre_prestador' => 'nullable|string',
            'cargo_prestador' => 'nullable|string',
            'firma_cliente' => 'nullable|string',
            'nombre_cliente' => 'nullable|string',
            'cargo_cliente' => 'nullable|string',
            'fecha_reporte' => 'required|date',
        ];

        // Reglas dinámicas para los chequeos
        $tipo = $request->input('tipo_reporte');
        if ($tipo === 'reporte_electronica') {
            for ($i = 1; $i <= 17; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
            $baseRules["chequeo18"] = 'nullable|in:B,R,M,NA';
        } else {
            for ($i = 1; $i <= 18; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
        }

        $validate = $request->validate($baseRules);

        $reporte->update($validate);
        $reporte->load(['equipo.sede.empresa']);

        return response()->json([
            'message' => 'Reporte actualizado exitosamente',
            'data' => new ReporteResource($reporte),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reporte $reporte)
    {
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado'], 200);
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
