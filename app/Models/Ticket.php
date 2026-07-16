<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'equipo_id',
        'tecnico_id',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'fecha_cierre',
        'created_by',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
