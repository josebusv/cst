<?php

namespace App\Observers;

use App\Support\CatalogoCache;

/**
 * Invalida la caché de catálogos (ListaController) cuando cambia cualquier
 * entidad de la que dependen esas listas.
 */
class CatalogoObserver
{
    public function saved($model): void
    {
        CatalogoCache::invalidar();
    }

    public function deleted($model): void
    {
        CatalogoCache::invalidar();
    }
}
