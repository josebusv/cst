<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccesorioEquipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipo_id',
        'accesorio_id',
        'nombre',
    ];

    protected $table = 'accesorio_equipo';

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
