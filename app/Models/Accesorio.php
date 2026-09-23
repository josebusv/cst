<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accesorio extends Model
{
    use HasFactory;

    protected $table = 'accesorios';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function equipos(): BelongsToMany
    {
        return $this->belongsToMany(Equipo::class, 'accesorio_equipo', 'accesorio_id', 'equipo_id')
                    ->withTimestamps();
    }

}
