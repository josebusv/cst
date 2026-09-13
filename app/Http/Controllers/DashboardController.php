<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Sede;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Reporte;
use App\Models\Cronograma;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function admin()
    {
        $totalClientes = Cliente::count();
        $totalEquipos = Equipo::count();
        $totalSedes = Sede::count();
        $totalUsuarios = User::count();

        $tickets = Ticket::selectRaw('estado, count(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado');

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $reportesMes = Reporte::whereBetween('created_at', [$inicioMes, $finMes])->count();

        $mantenimientosPendientes = Cronograma::where('estado', 'pendiente')->count();

        $equiposPorCliente = Cliente::withCount('sedes')
            ->with(['sedes' => function ($q) {
                $q->withCount('equipos');
            }])
            ->limit(10)
            ->get()
            ->map(function ($cliente) {
                $totalEquiposCliente = $cliente->sedes->sum('equipos_count');
                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'equipos' => $totalEquiposCliente,
                ];
            });

        $ultimosTickets = Ticket::with(['empresa', 'equipo'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $proximosMantenimientos = Cronograma::where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->orderBy('fecha_programada')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'total_clientes' => $totalClientes,
                'total_equipos' => $totalEquipos,
                'total_sedes' => $totalSedes,
                'total_usuarios' => $totalUsuarios,
                'tickets' => [
                    'abierto' => $tickets['abierto'] ?? 0,
                    'en_progreso' => $tickets['en_progreso'] ?? 0,
                    'cerrado' => $tickets['cerrado'] ?? 0,
                    'cancelado' => $tickets['cancelado'] ?? 0,
                ],
                'reportes_mes' => $reportesMes,
                'mantenimientos_pendientes' => $mantenimientosPendientes,
                'equipos_por_cliente' => $equiposPorCliente,
                'ultimos_tickets' => $ultimosTickets,
                'proximos_mantenimientos' => $proximosMantenimientos,
            ]
        ]);
    }

    public function cliente(Request $request)
    {
        $user = $request->user();
        $empresaId = optional(optional($user->sede)->empresa)->id;

        if (!$empresaId) {
            return response()->json(['data' => null], 200);
        }

        $sedeIds = Sede::where('empresa_id', $empresaId)->pluck('id');

        $totalEquipos = Equipo::whereIn('sede_id', $sedeIds)->count();
        $totalSedes = $sedeIds->count();
        $totalUsuarios = User::whereIn('sede_id', $sedeIds)->count();

        $tickets = Ticket::where('empresa_id', $empresaId)
            ->selectRaw('estado, count(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado');

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $reportesMes = Reporte::whereHas('equipo.sede', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        $mantenimientosPendientes = Cronograma::whereHas('equipo.sede', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })
            ->where('estado', 'pendiente')
            ->count();

        $proximoMantenimiento = Cronograma::whereHas('equipo.sede', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })
            ->where('estado', 'pendiente')
            ->orderBy('fecha_programada')
            ->first();

        $ultimosTickets = Ticket::where('empresa_id', $empresaId)
            ->with('equipo')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $ultimosReportes = Reporte::whereHas('equipo.sede', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'total_equipos' => $totalEquipos,
                'total_sedes' => $totalSedes,
                'total_usuarios' => $totalUsuarios,
                'tickets' => [
                    'abierto' => $tickets['abierto'] ?? 0,
                    'en_progreso' => $tickets['en_progreso'] ?? 0,
                    'cerrado' => $tickets['cerrado'] ?? 0,
                    'cancelado' => $tickets['cancelado'] ?? 0,
                ],
                'reportes_mes' => $reportesMes,
                'mantenimientos_pendientes' => $mantenimientosPendientes,
                'proximo_mantenimiento' => $proximoMantenimiento,
                'ultimos_tickets' => $ultimosTickets,
                'ultimos_reportes' => $ultimosReportes,
            ]
        ]);
    }
}
