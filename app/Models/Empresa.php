<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nit',
        'nombre',
        'logo',
        'tipo',
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function setLogoAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            $this->attributes['logo'] = $value;
        } else {
            $this->attributes['logo'] = null;
        }
    }

    public function sedes()
    {
        return $this->hasMany(Sede::class);
    }

}
