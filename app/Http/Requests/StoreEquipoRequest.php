<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('Crear Equipos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sede_id' => 'required|exists:sedes,id',
            'equipo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'required|string|max:255',
            'fabricante' => 'required|string|max:255',
            'registro_invima' => 'nullable|string|max:255',
            'pais_origen' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'inventario' => 'nullable|string|max:255',
            'code_ecri' => 'nullable|string|max:255',
        ];
    }
}
