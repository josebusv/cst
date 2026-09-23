<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\HojaVida */
class HojaVidaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'equipo_id' => $this->equipo_id,
            'mantenimiento_por' => $this->mantenimiento_por,
            'contacto_responsable' => $this->contacto_responsable,
            'telefono_responsable' => $this->telefono_responsable,
            'especificaciones_tecnicas' => $this->especificaciones_tecnicas,
            'fuentes_alimentacion' => $this->fuentes_alimentacion,
            'sistemas_consulta' => $this->sistemas_consulta,
            'accesorios' => $this->accesorios,
            'otros_consumibles' => $this->otros_consumibles,
            'uso' => $this->uso,
            'tipo_dispositivo' => $this->tipo_dispositivo,
            'clase_riesgo' => $this->clase_riesgo,
            'firma_realizo_user_id' => $this->firma_realizo_user_id,
            'firma_realizo' => $this->firma_realizo,
            'nombre_realizo' => $this->nombre_realizo,
            'cargo_realizo' => $this->cargo_realizo,
            'firma_aprobo_user_id' => $this->firma_aprobo_user_id,
            'firma_aprobo' => $this->firma_aprobo,
            'nombre_aprobo' => $this->nombre_aprobo,
            'cargo_aprobo' => $this->cargo_aprobo,
            'realizo_user' => new UserResource($this->whenLoaded('realizoUser')),
            'aprobo_user' => new UserResource($this->whenLoaded('aproboUser')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
