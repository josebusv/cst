<?php

namespace App\Http\Controllers;

use App\Models\Cronograma;
use App\Models\Equipo;
use App\Models\Empresa;
use App\Http\Resources\CronogramaResource;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Support\EmpresaContext;
use App\Support\Pagination;

class CronogramaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Ver Cronogramas')->only(['index', 'show', 'cronogramasPorEmpresa', 'cronogramasPorEquipo', 'cronogramasPorTecnico', 'calendario', 'anual']);
        $this->middleware('can:Crear Cronogramas')->only('store');
        $this->middleware('can:Editar Cronogramas')->only('update');
        $this->middleware('can:Eliminar Cronogramas')->only('destroy');
        $this->middleware('can:Generar Cronogramas')->only('generar');
    }

    public function index(Request $request)
    {
        $query = Cronograma::with(['equipo', 'tecnico', 'reporte']);

        if (EmpresaContext::esRestringido()) {
            $empresaId = EmpresaContext::empresaId() ?? 0;
            $query->whereHas('equipo.sede', fn ($q) => $q->where('empresa_id', $empresaId));
        }

        return CronogramaResource::collection($query->paginate(Pagination::perPage($request)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'year' => 'required|string|size:4',
            'month' => 'required|string|size:2',
            'clasificacion_biomedica_id' => 'nullable|exists:clasificaciones_biomedicas,id',
            'clase_riesgo' => 'nullable|in:clase_i,clase_iia,clase_iib,clase_iii',
            'ubicacion' => 'nullable|string|max:255',
            'reporte_id' => 'nullable|exists:reportes,id',
            'estado' => 'nullable|in:pendiente,completado,vencido',
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
            'tipo' => 'nullable|in:mantenimiento,metrologia',
            'fecha_programada' => 'nullable|date',
            'fecha_ejecucion' => 'nullable|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        $equipo = Equipo::findOrFail($validated['equipo_id']);
        EmpresaContext::autorizarEmpresa(optional($equipo->sede)->empresa_id);

        // La ubicacion es un atributo del equipo: se ajusta al programar el mantenimiento.
        if (! empty($validated['ubicacion'])) {
            $equipo->update(['ubicacion' => $validated['ubicacion']]);
        }

        $cronograma = Cronograma::create(collect($validated)->except('ubicacion')->toArray());
        $cronograma->load(['equipo', 'tecnico', 'reporte']);

        return response()->json([
            'message' => 'Cronograma creado exitosamente',
            'data' => new CronogramaResource($cronograma),
        ], 201);
    }

    public function show(Cronograma $cronograma)
    {
        EmpresaContext::autorizarEmpresa(optional(optional($cronograma->equipo)->sede)->empresa_id);
        $cronograma->load(['equipo', 'tecnico', 'reporte', 'clasificacionBiomedica']);
        return new CronogramaResource($cronograma);
    }

    public function update(Request $request, Cronograma $cronograma)
    {
        EmpresaContext::autorizarEmpresa(optional(optional($cronograma->equipo)->sede)->empresa_id);

        $validated = $request->validate([
            'equipo_id' => 'sometimes|exists:equipos,id',
            'year' => 'sometimes|string|size:4',
            'month' => 'sometimes|string|size:2',
            'clasificacion_biomedica_id' => 'nullable|exists:clasificaciones_biomedicas,id',
            'clase_riesgo' => 'nullable|in:clase_i,clase_iia,clase_iib,clase_iii',
            'ubicacion' => 'nullable|string|max:255',
            'reporte_id' => 'nullable|exists:reportes,id',
            'estado' => 'sometimes|in:pendiente,completado,vencido',
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
            'tipo' => 'sometimes|in:mantenimiento,metrologia',
            'fecha_programada' => 'nullable|date',
            'fecha_ejecucion' => 'nullable|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        if (isset($validated['estado']) && $validated['estado'] === 'completado' && !$cronograma->fecha_ejecucion) {
            $validated['fecha_ejecucion'] = now();
        }

        // Vincular un reporte cierra el cronograma automaticamente.
        if (array_key_exists('reporte_id', $validated) && $validated['reporte_id'] && ! isset($validated['estado'])) {
            $validated['estado'] = 'completado';
            $validated['fecha_ejecucion'] = $cronograma->fecha_ejecucion ?? now();
        }

        $reporteId = $validated['reporte_id'] ?? $cronograma->reporte_id;
        $estadoFinal = $validated['estado'] ?? $cronograma->estado;
        if ($estadoFinal === 'completado' && ! $reporteId) {
            abort(422, 'Para marcar el cronograma como completado debe vincular un reporte.');
        }

        if (! empty($validated['ubicacion'])) {
            $cronograma->equipo?->update(['ubicacion' => $validated['ubicacion']]);
        }

        $cronograma->update(collect($validated)->except('ubicacion')->toArray());
        $cronograma->load(['equipo', 'tecnico', 'reporte', 'clasificacionBiomedica']);

        return response()->json([
            'message' => 'Cronograma actualizado exitosamente',
            'data' => new CronogramaResource($cronograma),
        ]);
    }

    public function destroy(Cronograma $cronograma)
    {
        EmpresaContext::autorizarEmpresa(optional(optional($cronograma->equipo)->sede)->empresa_id);
        $cronograma->delete();
        return response()->json(['message' => 'Cronograma eliminado exitosamente']);
    }

    public function cronogramasPorEmpresa(Request $request, $empresaId)
    {
        EmpresaContext::autorizarEmpresa($empresaId);

        $cronogramas = Cronograma::whereHas('equipo.sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with(['equipo', 'tecnico', 'reporte'])->paginate(Pagination::perPage($request));

        return CronogramaResource::collection($cronogramas);
    }

    public function cronogramasPorEquipo(Request $request, $equipoId)
    {
        EmpresaContext::autorizarEmpresa(optional(Equipo::findOrFail($equipoId)->sede)->empresa_id);

        $cronogramas = Cronograma::where('equipo_id', $equipoId)
            ->with(['tecnico', 'reporte'])
            ->orderBy('year')
            ->orderBy('month')
            ->paginate(Pagination::perPage($request));

        return CronogramaResource::collection($cronogramas);
    }

    public function cronogramasPorTecnico(Request $request, $userId)
    {
        $cronogramas = Cronograma::where('tecnico_id', $userId)
            ->with(['equipo', 'reporte'])
            ->orderBy('year')
            ->orderBy('month')
            ->paginate(Pagination::perPage($request));

        return CronogramaResource::collection($cronogramas);
    }

    public function calendario(Request $request)
    {
        $request->validate([
            'year' => 'required|string|size:4',
            'month' => 'required|string|size:2',
            'empresa_id' => 'nullable|exists:empresas,id',
        ]);

        $query = Cronograma::where('year', $request->year)
            ->where('month', $request->month)
            ->with(['equipo.sede', 'tecnico', 'reporte']);

        $this->marcarVencidos(
            EmpresaContext::esRestringido() ? EmpresaContext::empresaId() : ($request->empresa_id ? (int) $request->empresa_id : null)
        );

        if (EmpresaContext::esRestringido()) {
            $query->whereHas('equipo.sede', function ($q) {
                $q->where('empresa_id', EmpresaContext::empresaId() ?? 0);
            });
        } elseif ($request->empresa_id) {
            $query->whereHas('equipo.sede', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        return CronogramaResource::collection($query->get());
    }

    /**
     * Cronograma anual por empresa: equipos con el estado de cada mes
     * (mantenimiento y metrología) para el reporte/PDF.
     */
    public function anual($empresaId, Request $request)
    {
        $request->validate(['year' => 'required|string|size:4']);

        EmpresaContext::autorizarEmpresa($empresaId);

        $this->marcarVencidos($empresaId);

        $empresa = Empresa::findOrFail($empresaId);

        $equipos = Equipo::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
            ->with(['sede.departamento', 'sede.municipio', 'clasificacionBiomedica', 'hojaVida'])
            ->orderBy('equipo')
            ->get();

        $cronogramas = Cronograma::where('year', $request->year)
            ->whereHas('equipo.sede', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->get()
            ->groupBy('equipo_id');

        return response()->json([
            'data' => [
                'empresa' => ['id' => $empresa->id, 'nombre' => $empresa->nombre],
                'year' => $request->year,
                'equipos' => $equipos->map(function ($equipo) use ($cronogramas) {
                    return [
                        'id' => $equipo->id,
                        'equipo' => $equipo->equipo,
                        'marca' => $equipo->marca,
                        'modelo' => $equipo->modelo,
                        'serie' => $equipo->serie,
                        'clasificacion_biomedica' => $equipo->clasificacionBiomedica?->nombre,
                        'clase_riesgo' => $cronogramas->get($equipo->id, collect())
                            ->pluck('clase_riesgo')->filter()->first()
                            ?? $equipo->hojaVida?->clase_riesgo,
                        'ubicacion' => $equipo->ubicacion ?: $this->ubicacionSede($equipo),
                        'sede' => $this->ubicacionSede($equipo),
                        'cronogramas' => $cronogramas->get($equipo->id, collect())
                            ->map(fn ($c) => [
                                'month' => $c->month,
                                'estado' => $c->estado,
                                'tipo' => $c->tipo ?? 'mantenimiento',
                                'periodicidad' => $c->periodicidad,
                            ])->values(),
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Marca como vencidos los pendientes cuya fecha programada ya paso.
     * Se ejecuta de forma perezosa al consultar (y tambien via comando diario).
     */
    private function marcarVencidos(?int $empresaId = null): void
    {
        Cronograma::where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereDate('fecha_programada', '<', now()->toDateString())
            ->when($empresaId, fn ($q) => $q->whereHas('equipo.sede', fn ($s) => $s->where('empresa_id', $empresaId)))
            ->update(['estado' => 'vencido']);
    }

    private function ubicacionSede(Equipo $equipo): ?string
    {
        $sede = $equipo->sede;
        if (!$sede) {
            return $equipo->ubicacion ?: null;
        }

        $partes = array_filter([$sede->municipio?->nombre, $sede->departamento?->nombre]);
        if ($partes) {
            return implode(' ', $partes);
        }

        return $sede->nombre ?: ($equipo->ubicacion ?: null);
    }

    public function generar(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'periodicidad' => 'required|in:mensual,bimestral,trimestral,semestral,anual',
            'clasificacion_biomedica_id' => 'required|exists:clasificaciones_biomedicas,id',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

        EmpresaContext::autorizarEmpresa(optional(Equipo::findOrFail($request->equipo_id)->sede)->empresa_id);

        $intervalos = [
            'mensual' => 1,
            'bimestral' => 2,
            'trimestral' => 3,
            'semestral' => 6,
            'anual' => 12,
        ];

        $meses = $intervalos[$request->periodicidad];
        $fecha = Carbon::now()->startOfMonth();
        $generados = [];

        for ($i = 0; $i < 12; $i += $meses) {
            $fechaProgramada = $fecha->copy()->addMonths($i);

            $month = str_pad((string) $fechaProgramada->month, 2, '0', STR_PAD_LEFT);

            $existe = Cronograma::where('equipo_id', $request->equipo_id)
                ->where('year', $fechaProgramada->year)
                ->where('month', $month)
                ->exists();

            if (!$existe) {
                $cronograma = Cronograma::create([
                    'equipo_id' => $request->equipo_id,
                    'year' => (string) $fechaProgramada->year,
                    'month' => $month,
                    'clasificacion_biomedica_id' => $request->clasificacion_biomedica_id,
                    'estado' => 'pendiente',
                    'periodicidad' => $request->periodicidad,
                    'fecha_programada' => $fechaProgramada->toDateString(),
                    'tecnico_id' => $request->tecnico_id,
                ]);

                $generados[] = $cronograma;
            }
        }

        return response()->json([
            'message' => count($generados) . ' cronogramas generados',
            'data' => CronogramaResource::collection(collect($generados)),
        ], 201);
    }
}
