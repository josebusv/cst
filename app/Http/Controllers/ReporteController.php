<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReporteRequest;
use App\Models\Reporte;
use App\Models\Equipo;
use App\Models\Cronograma;
use App\Http\Resources\ReporteResource;
use App\Support\EmpresaContext;
use App\Support\Pagination;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Reportes')->only(['index', 'show', 'reportesPorEquipo']);
        $this->middleware('can:Crear Reportes')->only('store');
        $this->middleware('can:Firmar Reportes')->only(['updateFirmaTecnico', 'updateFirmaCliente']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Trae los reportes con equipo, sede y empresa (cliente), paginados
        $query = Reporte::with([
            'equipo.sede.empresa'
        ]);

        if (EmpresaContext::esRestringido()) {
            $empresaId = EmpresaContext::empresaId() ?? 0;
            $query->whereHas('equipo.sede', fn ($q) => $q->where('empresa_id', $empresaId));
        }

        return ReporteResource::collection($query->paginate(Pagination::perPage($request, 10)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReporteRequest $request)
    {
        $validated = $request->validated();

        $equipo = Equipo::findOrFail($validated['equipo_id']);
        EmpresaContext::autorizarEmpresa(optional($equipo->sede)->empresa_id);

        // El "Servicio / Area" es un atributo del equipo: se actualiza al guardar el reporte.
        if (! empty($validated['servicio'])) {
            $equipo->update(['servicio' => $validated['servicio']]);
        }

        $reporte = Reporte::create(collect($validated)->except(['servicio', 'cronograma_id'])->toArray());

        // Cierre del cronograma: el mantenimiento programado se cumple con su reporte.
        if (! empty($validated['cronograma_id'])) {
            $cronograma = Cronograma::find($validated['cronograma_id']);
            if ($cronograma && (int) $cronograma->equipo_id === (int) $equipo->id) {
                $cronograma->update([
                    'reporte_id' => $reporte->id,
                    'estado' => 'completado',
                    'fecha_ejecucion' => $cronograma->fecha_ejecucion ?? now(),
                ]);
            }
        }

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
        EmpresaContext::autorizarEmpresa(optional(optional($reporte->equipo)->sede)->empresa_id);
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
     * Update the técnico signature for a report.
     */
    public function updateFirmaTecnico(Request $request, Reporte $reporte)
    {
        EmpresaContext::autorizarEmpresa(optional(optional($reporte->equipo)->sede)->empresa_id);

        $request->validate([
            'firma_tecnico' => 'required|string',
        ]);

        $reporte->update([
            'firma_tecnico' => $request->firma_tecnico,
        ]);

        $reporte->load(['equipo.sede.empresa']);

        return response()->json([
            'message' => 'Firma técnica actualizada exitosamente',
            'data' => new ReporteResource($reporte),
        ]);
    }

    /**
     * Update the cliente signature for a report.
     */
    public function updateFirmaCliente(Request $request, Reporte $reporte)
    {
        EmpresaContext::autorizarEmpresa(optional(optional($reporte->equipo)->sede)->empresa_id);

        $request->validate([
            'firma_cliente' => 'required|string',
        ]);

        $reporte->update([
            'firma_cliente' => $request->firma_cliente,
        ]);

        $reporte->load(['equipo.sede.empresa']);

        return response()->json([
            'message' => 'Firma de cliente actualizada exitosamente',
            'data' => new ReporteResource($reporte),
        ]);
    }

    /**
     * Obtiene todos los reportes de un equipo específico.
     */
    public function reportesPorEquipo(Request $request, $equipoId)
    {
        $equipo = Equipo::findOrFail($equipoId);
        EmpresaContext::autorizarEmpresa(optional($equipo->sede)->empresa_id);

        $reportes = Reporte::where('equipo_id', $equipoId)
            ->with(['equipo.sede.empresa'])
            ->paginate(Pagination::perPage($request, 10));

        return ReporteResource::collection($reportes);
    }
}
