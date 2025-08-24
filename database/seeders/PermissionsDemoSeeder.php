<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Firmar Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Imprimir Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Hoja De Vida']);
        Permission::create(['guard_name' => 'api', 'name' => 'Ver Hoja De Vida']);
        Permission::create(['guard_name' => 'api', 'name' => 'Imprimir Hoja De Vida']);

        $role3 = Role::create(['guard_name' => 'api', 'name' => 'Super-Admin']);
        gets all permissions via Gate::before rule; see AuthServiceProvider
        // $role3 = Role::first();

        $user = \App\Models\User::find(1);
        $user->assignRole($role3);
    }
}