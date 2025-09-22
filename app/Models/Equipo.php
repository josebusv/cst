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
        'fabricante',
        'registro_invima',
        'pais_origen',
        'ubicacion',
        'inventario',
        'code_ecri'
    ];

    protected $table = 'equipos';

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function accesorios()
    {
        return $this->belongsToMany(Accesorio::class, 'accesorio_equipo', 'equipo_id', 'accesorio_id');
    }
}
