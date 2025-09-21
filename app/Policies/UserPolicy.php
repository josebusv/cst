<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    public function before(User $user, string $ability)
    {
        if ($user->hasRole('Super-Admin')) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->hasPermission('Listar Usuarios');
    }

    public function update(User $user, User $targetUser)
    {
        return $user->hasPermission('Editar Usuarios');
    }

}
