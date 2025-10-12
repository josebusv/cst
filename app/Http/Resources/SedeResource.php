<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SedeResource extends JsonResource
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
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'tipo_sede' => $this->principal ? 'Principal' : 'Secundaria',
            'departamento' => $this->whenLoaded('departamento', function () {
                return [
                    'id' => $this->departamento->id,
                    'nombre' => $this->departamento->nombre,
                ];
            }),
            'municipio' => $this->whenLoaded('municipio', function () {
                return [
                    'id' => $this->municipio->id,
                    'nombre' => $this->municipio->nombre,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
