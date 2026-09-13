<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EquipoResource extends JsonResource
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
            'equipo' => $this->equipo,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'servicio' => $this->servicio,
            'codigo' => $this->codigo,
            'fabricante' => $this->fabricante,
            'registro_invima' => $this->registro_invima,
            'pais_origen' => $this->pais_origen,
            'ubicacion' => $this->ubicacion,
            'inventario' => $this->inventario,
            'code_ecri' => $this->code_ecri,
            'imagen' => $this->imagen ? asset('storage/' . $this->imagen) : null,
            'estado' => $this->estado,
            'tipo_hoja' => $this->tipo_hoja,
            'fecha_adquisicion' => $this->fecha_adquisicion?->format('Y-m-d'),
            'fecha_instalacion' => $this->fecha_instalacion?->format('Y-m-d'),
            'garantia_meses' => $this->garantia_meses,
            'vida_util_meses' => $this->vida_util_meses,
            'tipo_equipo' => $this->whenLoaded('tipoEquipo', fn () => $this->tipoEquipo->tipo),
            'clasificacion_biomedica' => $this->whenLoaded('clasificacionBiomedica', fn () => [
                'id' => $this->clasificacionBiomedica->id,
                'nombre' => $this->clasificacionBiomedica->nombre,
            ]),
            'sede' => new SedeResource($this->whenLoaded('sede')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
