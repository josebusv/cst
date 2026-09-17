<?php

namespace App\Support;

use App\Models\User;

/**
 * Contexto de empresa para autorizacion multi-tenant.
 *
 * El rol "Cliente" (y cualquier usuario cuya empresa sea de tipo "cliente")
 * solo puede acceder a recursos de su propia empresa. El resto de roles
 * (Super-Admin, Administrador, Operador) gestionan todas las empresas.
 */
class EmpresaContext
{
    public static function user(): ?User
    {
        return auth()->user();
    }

    public static function esRestringido(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return true;
        }

        if ($user->hasRole('Super-Admin')) {
            return false;
        }

        return $user->hasRole('Cliente')
            || optional(optional($user->sede)->empresa)->tipo === 'cliente';
    }

    public static function empresaId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user || ! $user->sede) {
            return null;
        }

        $empresaId = $user->sede->empresa_id;

        return $empresaId !== null ? (int) $empresaId : null;
    }

    /**
     * Aborta 403 si un usuario restringido intenta acceder a otra empresa.
     */
    public static function autorizarEmpresa($empresaId): void
    {
        if (! self::esRestringido()) {
            return;
        }

        if ($empresaId === null || (int) $empresaId !== self::empresaId()) {
            abort(403, 'No autorizado para acceder a recursos de otra empresa.');
        }
    }

    /**
     * ID de empresa del usuario si esta restringido; null si tiene acceso global.
     */
    public static function empresaFiltro(?User $user = null): ?int
    {
        return self::esRestringido($user) ? self::empresaId($user) : null;
    }
}
