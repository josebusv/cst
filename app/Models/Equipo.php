<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'tipo_hoja',
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

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function clasificacionBiomedica(): BelongsTo
    {
        return $this->belongsTo(ClasificacionBiomedica::class, 'clasificacion_biomedica_id');
    }

    public function accesorios(): BelongsToMany
    {
        return $this->belongsToMany(Accesorio::class, 'accesorio_equipo', 'equipo_id', 'accesorio_id');
    }

    public function consumibles(): BelongsToMany
    {
        return $this->belongsToMany(Consumible::class, 'consumible_equipo', 'equipo_id', 'consumible_id')
            ->withPivot(['cantidad', 'observaciones'])
            ->withTimestamps();
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }

    public function cronogramas(): HasMany
    {
        return $this->hasMany(Cronograma::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoEquipo::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function hojaVida(): HasOne
    {
        return $this->hasOne(HojaVida::class);
    }
}
