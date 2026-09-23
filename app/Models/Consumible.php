<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Consumible extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function equipos(): BelongsToMany
    {
        return $this->belongsToMany(Equipo::class, 'consumible_equipo', 'consumible_id', 'equipo_id')
            ->withPivot(['cantidad', 'observaciones'])
            ->withTimestamps();
    }
}
