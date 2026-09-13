<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'departamento_id', 'codigo'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
