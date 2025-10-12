<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReporteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'equipo_id' => $this->equipo_id,
            'tipo_reporte' => $this->tipo_reporte,
            'correctivo' => $this->correctivo,
            'preventivo' => $this->preventivo,
            'fuera_servicio' => $this->fuera_servicio,
            'requerido_cliente' => $this->requerido_cliente,
            'falla_reportada' => $this->falla_reportada,
            'limpieza_interna' => $this->limpieza_interna,
            'limpieza_externa' => $this->limpieza_externa,
            'lubricacion' => $this->lubricacion,
            'ajuste_angulacion' => $this->ajuste_angulacion,
            'prueba_fugas' => $this->prueba_fugas,
            'ajuste_general' => $this->ajuste_general,
            'cable_paciente' => $this->cable_paciente,
            'verificacion_software' => $this->verificacion_software,
            'filtros' => $this->filtros,
            'verificacion_general' => $this->verificacion_general,
            'reemplazo_insumo' => $this->reemplazo_insumo,
            'baterias' => $this->baterias,
            'servicio_realizado' => $this->servicio_realizado,
            'observaciones' => $this->observaciones,
            'firma_tecnico' => $this->firma_tecnico,
            'nombre_prestador' => $this->nombre_prestador,
            'cargo_prestador' => $this->cargo_prestador,
            'firma_cliente' => $this->firma_cliente,
            'nombre_cliente' => $this->nombre_cliente,
            'cargo_cliente' => $this->cargo_cliente,
            'fecha_reporte' => $this->fecha_reporte,
            // Dynamic chequeo fields
            $this->mergeWhen($this->tipo_reporte === 'reporte_electronica', function () {
                $chequeos = [];
                for ($i = 1; $i <= 17; $i++) {
                    $chequeos["chequeo$i"] = $this->{"chequeo$i"};
                }
                $chequeos["chequeo18"] = $this->chequeo18;
                return $chequeos;
            }),
            $this->mergeWhen($this->tipo_reporte !== 'reporte_electronica', function () {
                $chequeos = [];
                for ($i = 1; $i <= 18; $i++) {
                    $chequeos["chequeo$i"] = $this->{"chequeo$i"};
                }
                return $chequeos;
            }),
            // Relationships
            'equipo' => new EquipoResource($this->whenLoaded('equipo')),
            'cliente' => $this->whenLoaded('equipo.sede.empresa', function () {
                return $this->equipo->sede->empresa->nombre ?? null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
