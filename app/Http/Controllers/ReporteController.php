<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Trae los reportes con equipo, sede y empresa (cliente), paginados
        $reportes = Reporte::with([
            'equipo.sede.empresa'
        ])->paginate(10);

        return response()->json([
            'data' => $reportes->items(),
            'meta' => [
                'current_page' => $reportes->currentPage(),
                'last_page' => $reportes->lastPage(),
                'per_page' => $reportes->perPage(),
                'total' => $reportes->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
            // chequeos 1-17 obligatorios, 18 opcional
            for ($i = 1; $i <= 17; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
            $baseRules["chequeo18"] = 'nullable|in:B,R,M,NA';
        } else {
            // chequeos 1-18 obligatorios
            for ($i = 1; $i <= 18; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
        }

        $validate = $request->validate($baseRules);

        $reporte = Reporte::create($validate);
        return response()->json(['data' => $reporte], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reporte = Reporte::with([
            'equipo.sede.empresa'
        ])->find($id);

        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        return response()->json(['data' => $reporte], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

        $reporte = Reporte::find($id);
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        $reporte->update($validate);
        return response()->json(['data' => $reporte], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reporte = Reporte::find($id);
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

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

        // Formatear la respuesta para incluir datos del equipo y nombre del cliente (empresa)
        $data = $reportes->map(function ($reporte) {
            return [
                'id' => $reporte->id,
                'tipo_reporte' => $reporte->tipo_reporte,
                'fecha_reporte' => $reporte->fecha_reporte,
                'equipo' => $reporte->equipo?->equipo ?? null, // Todos los datos del equipo
                'marca' => $reporte->equipo?->marca ?? null,
                'modelo' => $reporte->equipo?->modelo ?? null,
                'serie' => $reporte->equipo?->serial ?? null,
                'inventario' => $reporte->equipo?->inventario ?? 'N/R',
                'ubicacion' => $reporte->equipo?->ubicacion ?? null, // Nombre de la sede/ubicación
                'servicio' => $reporte->equipo?->servicio ?? null,
                'cliente' => $reporte->equipo?->sede?->empresa?->nombre ?? null,
                'correctivo' => $reporte->correctivo ?? null,
                'preventivo' => $reporte->preventivo ?? null,
                'fuera_servicio' => $reporte->fuera_servicio ?? null,
                'requerido_cliente' => $reporte->requerido_cliente ?? null,
                'falla_reportada' => $reporte->falla_reportada ?? null,
                'chequeo1' => $reporte->chequeo1 ?? null,
                'chequeo2' => $reporte->chequeo2 ?? null,
                'chequeo3' => $reporte->chequeo3 ?? null,
                'chequeo4' => $reporte->chequeo4 ?? null,
                'chequeo5' => $reporte->chequeo5 ?? null,
                'chequeo6' => $reporte->chequeo6 ?? null,
                'chequeo7' => $reporte->chequeo7 ?? null,
                'chequeo8' => $reporte->chequeo8 ?? null,
                'chequeo9' => $reporte->chequeo9 ?? null,
                'chequeo10' => $reporte->chequeo10 ?? null,
                'chequeo11' => $reporte->chequeo11 ?? null,
                'chequeo12' => $reporte->chequeo12 ?? null,
                'chequeo13' => $reporte->chequeo13 ?? null,
                'chequeo14' => $reporte->chequeo14 ?? null,
                'chequeo15' => $reporte->chequeo15 ?? null,
                'chequeo16' => $reporte->chequeo16 ?? null,
                'chequeo17' => $reporte->chequeo17 ?? null,
                'chequeo18' => $reporte->chequeo18 ?? null,
                'limpieza_interna' => $reporte->limpieza_interna ?? null,
                'limpieza_externa' => $reporte->limpieza_externa ?? null,
                'lubricacion' => $reporte->lubricacion ?? null,
                'ajuste_angulacion' => $reporte->ajuste_angulacion ?? null,
                'prueba_fugas' => $reporte->prueba_fugas ?? null,
                'ajuste_general' => $reporte->ajuste_general ?? null,
                'cable_paciente' => $reporte->cable_paciente ?? null,
                'verificacion_software' => $reporte->verificacion_software ?? null,
                'filtros' => $reporte->filtros ?? null,
                'verificacion_general' => $reporte->verificacion_general ?? null,
                'reemplazo_insumo' => $reporte->reemplazo_insumo ?? null,
                'baterias' => $reporte->baterias ?? null,
                'servicio_realizado' => $reporte->servicio_realizado ?? null,
                'observaciones' => $reporte->observaciones ?? null,
                'firma_tecnico' => $reporte->firma_tecnico ?? null,
                'nombre_prestador' => $reporte->nombre_prestador ?? null,
                'cargo_prestador' => $reporte->cargo_prestador ?? null,
                'firma_cliente' => $reporte->firma_cliente ?? null,
                'nombre_cliente' => $reporte->nombre_cliente ?? null,
                'cargo_cliente' => $reporte->cargo_cliente ?? null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $reportes->currentPage(),
                'last_page' => $reportes->lastPage(),
                'per_page' => $reportes->perPage(),
                'total' => $reportes->total(),
            ],
        ]);
    }
}
