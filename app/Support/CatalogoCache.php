<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caché versionada para catálogos y listas (ListaController).
 *
 * Todas las claves comparten una versión global: al escribir cualquier
 * entidad relacionada se incrementa la versión y las claves antiguas dejan
 * de usarse (el TTL actúa como respaldo).
 */
class CatalogoCache
{
    private const VERSION_KEY = 'lista.version';

    public static function remember(string $key, int $ttl, Closure $callback)
    {
        return Cache::remember(self::prefijo() . $key, $ttl, $callback);
    }

    public static function invalidar(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function prefijo(): string
    {
        return 'lista.v' . self::version() . '.';
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
