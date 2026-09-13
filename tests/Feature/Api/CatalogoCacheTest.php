<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Support\CatalogoCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalidar_renueva_el_valor_cacheado(): void
    {
        $this->assertSame('a', CatalogoCache::remember('prueba', 300, fn () => 'a'));
        $this->assertSame('a', CatalogoCache::remember('prueba', 300, fn () => 'b'));

        CatalogoCache::invalidar();

        $this->assertSame('b', CatalogoCache::remember('prueba', 300, fn () => 'b'));
    }

    public function test_guardar_empresa_invalida_el_cache_por_observer(): void
    {
        $contar = fn () => Empresa::count();

        $this->assertSame(0, CatalogoCache::remember('clientes_count', 300, $contar));

        Empresa::create(['nit' => '900000010', 'nombre' => 'Nueva', 'tipo' => 'cliente']);

        $this->assertSame(1, CatalogoCache::remember('clientes_count', 300, $contar));
    }
}
