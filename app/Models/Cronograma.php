<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cronograma extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'equipo_id',
        'year',
        'month',
        'clasificacion_biomedica_id',
        'clase_riesgo',
        'reporte_id',
        'estado',
        'periodicidad',
        'tipo',
        'fecha_programada',
        'fecha_ejecucion',
        'tecnico_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_ejecucion' => 'date',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function clasificacionBiomedica()
    {
        return $this->belongsTo(ClasificacionBiomedica::class, 'clasificacion_biomedica_id');
    }

    public function reporte()
    {
        return $this->belongsTo(Reporte::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}
