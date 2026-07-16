<?php

namespace App\Http\Controllers;

use App\Models\Cronograma;
use App\Models\Equipo;
use App\Http\Resources\CronogramaResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CronogramaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Ver Cronogramas')->only(['index', 'show', 'cronogramasPorEmpresa', 'cronogramasPorEquipo', 'cronogramasPorTecnico', 'calendario']);
        $this->middleware('can:Crear Cronogramas')->only('store');
        $this->middleware('can:Editar Cronogramas')->only('update');
        $this->middleware('can:Eliminar Cronogramas')->only('destroy');
        $this->middleware('can:Generar Cronogramas')->only('generar');
    }

    public function index()
    {
        $cronogramas = Cronograma::with(['equipo', 'tecnico', 'reporte'])->paginate(15);
        return CronogramaResource::collection($cronogramas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'year' => 'required|string|size:4',
            'month' => 'required|string|size:2',
            'clasificacion_biomedica_id' => 'required|exists:clasificaciones_biomedicas,id',
            'reporte_id' => 'nullable|exists:reportes,id',
            'estado' => 'nullable|in:pendiente,completado,vencido',
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
            'fecha_programada' => 'nullable|date',
            'fecha_ejecucion' => 'nullable|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        $cronograma = Cronograma::create($validated);
        $cronograma->load(['equipo', 'tecnico', 'reporte']);

        return response()->json([
            'message' => 'Cronograma creado exitosamente',
            'data' => new CronogramaResource($cronograma),
        ], 201);
    }

    public function show(Cronograma $cronograma)
    {
        $cronograma->load(['equipo', 'tecnico', 'reporte', 'clasificacionBiomedica']);
        return new CronogramaResource($cronograma);
    }

    public function update(Request $request, Cronograma $cronograma)
    {
        $validated = $request->validate([
            'equipo_id' => 'sometimes|exists:equipos,id',
            'year' => 'sometimes|string|size:4',
            'month' => 'sometimes|string|size:2',
            'clasificacion_biomedica_id' => 'sometimes|exists:clasificaciones_biomedicas,id',
            'reporte_id' => 'nullable|exists:reportes,id',
            'estado' => 'sometimes|in:pendiente,completado,vencido',
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
            'fecha_programada' => 'nullable|date',
            'fecha_ejecucion' => 'nullable|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        if (isset($validated['estado']) && $validated['estado'] === 'completado' && !$cronograma->fecha_ejecucion) {
            $validated['fecha_ejecucion'] = now();
        }

        $cronograma->update($validated);
        $cronograma->load(['equipo', 'tecnico', 'reporte', 'clasificacionBiomedica']);

        return response()->json([
            'message' => 'Cronograma actualizado exitosamente',
            'data' => new CronogramaResource($cronograma),
        ]);
    }

    public function destroy(Cronograma $cronograma)
    {
        $cronograma->delete();
        return response()->json(['message' => 'Cronograma eliminado exitosamente']);
    }

    public function cronogramasPorEmpresa($empresaId)
    {
        $cronogramas = Cronograma::whereHas('equipo.sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with(['equipo', 'tecnico', 'reporte'])->paginate(15);

        return CronogramaResource::collection($cronogramas);
    }

    public function cronogramasPorEquipo($equipoId)
    {
        $cronogramas = Cronograma::where('equipo_id', $equipoId)
            ->with(['tecnico', 'reporte'])
            ->orderBy('year')
            ->orderBy('month')
            ->paginate(15);

        return CronogramaResource::collection($cronogramas);
    }

    public function cronogramasPorTecnico($userId)
    {
        $cronogramas = Cronograma::where('tecnico_id', $userId)
            ->with(['equipo', 'reporte'])
            ->orderBy('year')
            ->orderBy('month')
            ->paginate(15);

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

        if ($request->empresa_id) {
            $query->whereHas('equipo.sede', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        return CronogramaResource::collection($query->get());
    }

    public function generar(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'periodicidad' => 'required|in:mensual,bimestral,trimestral,semestral,anual',
            'clasificacion_biomedica_id' => 'required|exists:clasificaciones_biomedicas,id',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

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
