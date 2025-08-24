<?php

namespace App\Policies;

use App\Models\User;
use

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('Listar Usuarios');
    }
}
