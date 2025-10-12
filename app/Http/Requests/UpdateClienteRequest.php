<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('Editar Clientes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $clienteId = $this->route('cliente')->id;

        return [
            'nombre' => 'sometimes|required|string|max:255',
            'nit' => 'sometimes|required|integer|unique:empresas,nit,' . $clienteId,
            'logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
