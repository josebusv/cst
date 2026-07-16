<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateEquipoRequest;
use App\Http\Requests\StoreEquipoRequest;
use App\Models\Equipo;
use App\Models\Empresa;
use App\Models\UnidadTecnica;
use App\Http\Resources\EquipoResource;
use App\Http\Resources\CronogramaResource;

class EquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Equipos')->only(['index', 'show', 'equiposPorEmpresa']);
        $this->middleware('can:Crear Equipos')->only('store');
        $this->middleware('can:Editar Equipos')->only('update');
        $this->middleware('can:Eliminar Equipos')->only('destroy');
        $this->middleware('can:Ver Hoja De Vida')->only('hojaVida');
    }

    public function index()
    {
        $equipos = Equipo::with('sede')->paginate(15);
        return EquipoResource::collection($equipos);
    }

    public function store(StoreEquipoRequest $request)
    {
        $validated = $request->validated();

        $equipo = Equipo::create($validated);
        $equipo->load('sede');

        // Crear hoja de vida vacía automáticamente
        $equipo->hojaVida()->create([
            'especificaciones_tecnicas' => [],
            'fuentes_alimentacion' => [],
            'sistemas_consulta' => [],
        ]);

        return response()->json([
            'message' => 'Equipo creado exitosamente',
            'data' => new EquipoResource($equipo),
        ], 201);
    }

    public function show(Equipo $equipo)
    {
        $equipo->load('sede');
        return new EquipoResource($equipo);
    }

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

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado exitosamente']);
    }

    public function equiposPorEmpresa($empresaId)
    {
        $equipos = Equipo::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with('sede')->paginate(15);

        return EquipoResource::collection($equipos);
    }

    public function hojaVida($id)
    {
        $equipo = Equipo::with([
            'sede.empresa',
            'sede.departamento',
            'sede.municipio',
            'tipoEquipo',
            'clasificacionBiomedica',
            'hojaVida.realizoUser',
            'hojaVida.aproboUser',
            'reportes' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'cronogramas' => function ($query) {
                $query->orderBy('year')->orderBy('month');
            },
            'cronogramas.tecnico',
            'cronogramas.reporte',
            'documentos',
            'accesorios',
            'consumibles',
            'tickets' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);

        $empresa = $equipo->sede?->empresa;
        $principal = Empresa::where('tipo', 'principal')->first();

        $data = [
            'logo_empresa' => $empresa?->logo_url,
            'nombre_empresa' => $empresa?->nombre,
            'empresa' => $empresa ? [
                'id' => $empresa->id,
                'nombre' => $empresa->nombre,
                'nit' => $empresa->nit,
                'logo_url' => $empresa->logo_url,
            ] : null,
            'sede' => $equipo->sede ? [
                'id' => $equipo->sede->id,
                'nombre' => $equipo->sede->nombre,
                'direccion' => $equipo->sede->direccion,
                'telefono' => $equipo->sede->telefono,
                'email' => $equipo->sede->email,
                'departamento' => $equipo->sede->departamento?->nombre,
                'municipio' => $equipo->sede->municipio?->nombre,
            ] : null,
            'mantenimiento_por' => $principal?->nombre ?? 'CST SAS',
            'telefono_mantenimiento' => $principal?->telefono ?? '',
            'equipo' => new EquipoResource($equipo),
            'hoja_vida' => $equipo->hojaVida,
            'consumibles' => $equipo->consumibles,
            'accesorios' => $equipo->accesorios,
            'reportes' => $equipo->reportes,
            'cronogramas' => CronogramaResource::collection($equipo->cronogramas),
            'documentos' => $equipo->documentos,
            'tickets' => $equipo->tickets,
            'unidades_tecnicas' => $this->unidadesTecnicasAgrupadas(),
        ];

        return response()->json(['data' => $data]);
    }

    private function unidadesTecnicasAgrupadas()
    {
        return UnidadTecnica::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria')
            ->map(fn ($items) => $items->map(fn ($u) => [
                'id' => $u->id,
                'nombre' => $u->nombre,
                'simbolo' => $u->simbolo,
            ])->values())
            ->toArray();
    }
}
