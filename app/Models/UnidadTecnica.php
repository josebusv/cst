<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadTecnica extends Model
{
    use HasFactory;

    protected $table = 'unidades_tecnicas';

    protected $fillable = [
        'categoria',
        'nombre',
        'simbolo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
