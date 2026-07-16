<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasificacionBiomedica extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones_biomedicas';

    protected $fillable = [
        'nombre',
        'activo',
    ];
}
