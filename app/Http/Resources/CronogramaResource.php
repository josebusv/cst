<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CronogramaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'equipo_id' => $this->equipo_id,
            'equipo' => new EquipoResource($this->whenLoaded('equipo')),
            'year' => $this->year,
            'month' => $this->month,
            'clasificacion_biomedica_id' => $this->clasificacion_biomedica_id,
            'clasificacion_biomedica' => $this->whenLoaded('clasificacionBiomedica', function () {
                return ['id' => $this->clasificacionBiomedica->id, 'nombre' => $this->clasificacionBiomedica->nombre];
            }),
            'reporte_id' => $this->reporte_id,
            'reporte' => new ReporteResource($this->whenLoaded('reporte')),
            'estado' => $this->estado,
            'periodicidad' => $this->periodicidad,
            'fecha_programada' => $this->fecha_programada,
            'fecha_ejecucion' => $this->fecha_ejecucion,
            'tecnico_id' => $this->tecnico_id,
            'tecnico' => new UserResource($this->whenLoaded('tecnico')),
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
