<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'empresa_id',
        'activo',
        'created_by',
        'update_by',
        'deleted_by',
        'departamento_id',
        'municipio_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}
