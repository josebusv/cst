<?php

namespace App\Policies;

use App\Models\User;

class EmpresaPolicy
{
    use HandlesAuthorization;

    public function viewAny(Empresa $empresa)
    {
        return $user->hasPermission('Listar Empresas');
    }
}
