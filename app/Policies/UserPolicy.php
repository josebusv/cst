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
        return $user->can('Listar Usuarios');
    }

    public function update(User $user, User $targetUser)
    {
        return $user->can('Editar Usuarios');
    }

}
