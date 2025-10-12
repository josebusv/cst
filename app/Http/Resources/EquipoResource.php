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
            'fabricante' => $this->fabricante,
            'registro_invima' => $this->registro_invima,
            'pais_origen' => $this->pais_origen,
            'ubicacion' => $this->ubicacion,
            'inventario' => $this->inventario,
            'code_ecri' => $this->code_ecri,
            'sede' => new SedeResource($this->whenLoaded('sede')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
