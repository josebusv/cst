<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmpresaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('Listar Empresas');
    }
}
