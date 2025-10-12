<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReporteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('Crear Reportes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $baseRules = [
            'equipo_id' => 'required|exists:equipos,id',
            'tipo_reporte' => 'required|string|max:20',
            'correctivo' => 'nullable|string',
            'preventivo' => 'nullable|string',
            'fuerea_servicio' => 'nullable|string',
            'requerido_cliente' => 'nullable|string',
            'falla_reportada' => 'nullable|string',
            'limpieza_interna' => 'sometimes|boolean',
            'limpieza_externa' => 'sometimes|boolean',
            'lubricacion' => 'sometimes|boolean',
            'ajuste_angulacion' => 'sometimes|boolean',
            'prueba_fugas' => 'sometimes|boolean',
            'ajuste_general' => 'sometimes|boolean',
            'cable_paciente' => 'sometimes|boolean',
            'verificacion_software' => 'sometimes|boolean',
            'filtros' => 'sometimes|boolean',
            'verificacion_general' => 'sometimes|boolean',
            'reemplazo_insumo' => 'sometimes|boolean',
            'baterias' => 'sometimes|boolean',
            'servicio_realizado' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'firma_tecnico' => 'nullable|string',
            'nombre_prestador' => 'nullable|string',
            'cargo_prestador' => 'nullable|string',
            'firma_cliente' => 'nullable|string',
            'nombre_cliente' => 'nullable|string',
            'cargo_cliente' => 'nullable|string',
            'fecha_reporte' => 'required|date',
        ];

        // Reglas dinámicas para los chequeos
        $tipo = $this->input('tipo_reporte');
        if ($tipo === 'reporte_electronica') {
            // chequeos 1-17 obligatorios, 18 opcional
            for ($i = 1; $i <= 17; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
            $baseRules["chequeo18"] = 'nullable|in:B,R,M,NA';
        } else {
            // chequeos 1-18 obligatorios
            for ($i = 1; $i <= 18; $i++) {
                $baseRules["chequeo$i"] = 'required|in:B,R,M,NA';
            }
        }

        return $baseRules;
    }
}
