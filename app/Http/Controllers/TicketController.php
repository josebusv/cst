<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Resources\TicketResource;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Ver Tickets')->only(['index', 'show', 'ticketsPorEmpresa', 'ticketsPorTecnico']);
        $this->middleware('can:Crear Tickets')->only('store');
        $this->middleware('can:Editar Tickets')->only('update');
        $this->middleware('can:Eliminar Tickets')->only('destroy');
        $this->middleware('can:Cambiar Estado Tickets')->only('cambiarEstado');
    }

    public function index()
    {
        $tickets = Ticket::with(['empresa', 'equipo', 'tecnico', 'creador'])->paginate(15);
        return TicketResource::collection($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'equipo_id' => 'nullable|exists:equipos,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'nullable|in:alta,media,baja',
        ]);

        $validated['estado'] = 'abierto';
        $validated['created_by'] = auth()->id();
        $validated['prioridad'] = $validated['prioridad'] ?? 'media';

        $ticket = Ticket::create($validated);
        $ticket->load(['empresa', 'equipo', 'tecnico', 'creador']);

        return response()->json([
            'message' => 'Ticket creado exitosamente',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['empresa', 'equipo', 'tecnico', 'creador']);
        return new TicketResource($ticket);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'tecnico_id' => 'nullable|exists:users,id',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'estado' => 'sometimes|in:abierto,en_progreso,cerrado,cancelado',
            'prioridad' => 'sometimes|in:alta,media,baja',
            'equipo_id' => 'nullable|exists:equipos,id',
        ]);

        if (isset($validated['estado']) && in_array($validated['estado'], ['cerrado', 'cancelado'])) {
            $validated['fecha_cierre'] = now();
        }

        $ticket->update($validated);
        $ticket->load(['empresa', 'equipo', 'tecnico', 'creador']);

        return response()->json([
            'message' => 'Ticket actualizado exitosamente',
            'data' => new TicketResource($ticket),
        ]);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Ticket eliminado exitosamente']);
    }

    public function ticketsPorEmpresa($empresaId)
    {
        $tickets = Ticket::where('empresa_id', $empresaId)
            ->with(['equipo', 'tecnico', 'creador'])
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function ticketsPorTecnico($userId)
    {
        $tickets = Ticket::where('tecnico_id', $userId)
            ->with(['empresa', 'equipo', 'creador'])
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:abierto,en_progreso,cerrado,cancelado',
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($request->estado === 'cerrado' || $request->estado === 'cancelado') {
            $ticket->fecha_cierre = now();
        }

        $ticket->estado = $request->estado;
        $ticket->save();
        $ticket->load(['empresa', 'equipo', 'tecnico', 'creador']);

        return response()->json([
            'message' => 'Estado del ticket actualizado',
            'data' => new TicketResource($ticket),
        ]);
    }
}
