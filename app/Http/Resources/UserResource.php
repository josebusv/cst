<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),
            'sede' => $this->whenLoaded('sede', function () {
                return [
                    'id' => $this->sede->id,
                    'nombre' => $this->sede->nombre,
                    'empresa' => $this->whenLoaded('sede.empresa', function() {
                        return [
                            'id' => optional($this->sede->empresa)->id,
                            'nombre' => optional($this->sede->empresa)->nombre,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
