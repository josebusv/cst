<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;
use App\Scopes\ClienteScope;

class Cliente extends Empresa
{
    use HasFactory;

    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope(new ClienteScope);
    }

    protected $table = 'empresas';
}
