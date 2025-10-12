<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('Editar Equipos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sede_id' => 'sometimes|required|exists:sedes,id',
            'equipo' => 'sometimes|required|string|max:255',
            'marca' => 'sometimes|required|string|max:255',
            'modelo' => 'sometimes|required|string|max:255',
            'serie' => 'sometimes|required|string|max:255',
            'fabricante' => 'sometimes|required|string|max:255',
            'registro_invima' => 'nullable|string|max:255',
            'pais_origen' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'inventario' => 'nullable|string|max:255',
            'code_ecri' => 'nullable|string|max:255',
        ];
    }
}
