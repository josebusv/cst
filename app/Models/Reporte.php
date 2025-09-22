<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';

    protected $fillable = [
        'equipo_id',
        'tipo_reporte',
        'correctivo',
        'preventivo',
        'fuerea_servicio',
        'requerido_cliente',
        'falla_reportada',
        'chequeo1',
        'chequeo2',
        'chequeo3',
        'chequeo4',
        'chequeo5',
        'chequeo6',
        'chequeo7',
        'chequeo8',
        'chequeo9',
        'chequeo10',
        'chequeo11',
        'chequeo12',
        'chequeo13',
        'chequeo14',
        'chequeo15',
        'chequeo16',
        'chequeo17',
        'chequeo18',
        'limpieza_interna',
        'limpieza_externa',
        'lubricacion',
        'ajuste_angulacion',
        'prueba_fugas',
        'ajuste_general',
        'cable_paciente',
        'verificacion_software',
        'filtros',
        'verificacion_general',
        'reemplazo_insumo',
        'baterias',
        'servicio_realizado',
        'observaciones',
        'firma_tecnico',
        'nombre_prestador',
        'cargo_prestador',
        'firma_cliente',
        'nombre_cliente',
        'cargo_cliente',
        'fecha_reporte',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    
}
