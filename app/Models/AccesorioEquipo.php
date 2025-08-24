<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesorioEquipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipo_id',
        'accesorio_id',
        'nombre',
    ];

    protected $table = 'accesorio_equipo';

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
