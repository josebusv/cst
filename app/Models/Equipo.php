<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'sede_id',
        'equipo',
        'marca',
        'modelo',
        'serie',
        'servicio',
        'codigo',
        'fabricante',
        'registro_invima',
        'pais_origen',
        'ubicacion',
        'inventario',
        'code_ecri',
        'imagen',
        'tipo_equipo_id',
        'clasificacion_biomedica_id',
        'estado',
        'fecha_adquisicion',
        'fecha_instalacion',
        'garantia_meses',
        'vida_util_meses',
    ];

    protected $table = 'equipos';

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'fecha_instalacion' => 'date',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function clasificacionBiomedica()
    {
        return $this->belongsTo(ClasificacionBiomedica::class, 'clasificacion_biomedica_id');
    }

    public function accesorios()
    {
        return $this->belongsToMany(Accesorio::class, 'accesorio_equipo', 'equipo_id', 'accesorio_id');
    }

    public function consumibles()
    {
        return $this->belongsToMany(Consumible::class, 'consumible_equipo', 'equipo_id', 'consumible_id')
            ->withPivot(['cantidad', 'observaciones'])
            ->withTimestamps();
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class);
    }

    public function cronogramas()
    {
        return $this->hasMany(Cronograma::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoEquipo::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function hojaVida()
    {
        return $this->hasOne(HojaVida::class);
    }
}
