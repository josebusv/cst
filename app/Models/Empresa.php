<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

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
        // Si el valor es un archivo (UploadedFile), lo convertimos a string mediante store()
        if ($value instanceof \Illuminate\Http\UploadedFile) {
            $this->attributes['logo'] = $value->store('logos', 'public');
        }
        // Si es una string válida (path del archivo), la guardamos
        elseif (is_string($value) && !empty($value)) {
            $this->attributes['logo'] = $value;
        }
        // Si es null y ya tenemos un logo, no lo cambiamos (preservar logo existente)
        elseif ($value === null && !array_key_exists('logo', $this->attributes)) {
            $this->attributes['logo'] = null;
        }
        // En otros casos, solo actualizamos si explícitamente se pasa null
        elseif ($value === null) {
            $this->attributes['logo'] = null;
        }
    }

    /**
     * Eliminar el archivo de logo del almacenamiento
     */
    public function deleteLogoFile()
    {
        if ($this->logo && file_exists(storage_path('app/public/' . $this->logo))) {
            unlink(storage_path('app/public/' . $this->logo));
        }
    }

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'empresa_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function tecnicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cliente_tecnico', 'empresa_id', 'user_id');
    }
}
