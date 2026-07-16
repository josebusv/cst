<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'empresa' => new ClienteResource($this->whenLoaded('empresa')),
            'equipo_id' => $this->equipo_id,
            'equipo' => new EquipoResource($this->whenLoaded('equipo')),
            'tecnico_id' => $this->tecnico_id,
            'tecnico' => new UserResource($this->whenLoaded('tecnico')),
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'prioridad' => $this->prioridad,
            'fecha_cierre' => $this->fecha_cierre,
            'created_by' => $this->created_by,
            'creador' => new UserResource($this->whenLoaded('creador')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
