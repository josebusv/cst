<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HojaVida extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hojas_vida';

    protected $fillable = [
        'equipo_id',
        'contacto_responsable',
        'telefono_responsable',
        'especificaciones_tecnicas',
        'fuentes_alimentacion',
        'sistemas_consulta',
        'uso',
        'tipo_dispositivo',
        'clase_riesgo',
        'firma_realizo_user_id',
        'firma_realizo',
        'nombre_realizo',
        'cargo_realizo',
        'firma_aprobo_user_id',
        'firma_aprobo',
        'nombre_aprobo',
        'cargo_aprobo',
    ];

    protected $casts = [
        'especificaciones_tecnicas' => 'array',
        'fuentes_alimentacion' => 'array',
        'sistemas_consulta' => 'array',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function realizoUser()
    {
        return $this->belongsTo(User::class, 'firma_realizo_user_id');
    }

    public function aproboUser()
    {
        return $this->belongsTo(User::class, 'firma_aprobo_user_id');
    }
}
