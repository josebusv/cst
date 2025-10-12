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
        // permisos de usuario
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Usuarios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Usuarios']);
        // permisos de cliente
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Ver Clientes']);
        // permisos de sede
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Sedes']);
        // permisos de equipo
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Equipos']);
        // permisos de reportes
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Firmar Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Reportes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Imprimir Reportes']);
        // permisos de hoja de vida
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Hoja De Vida']);
        Permission::create(['guard_name' => 'api', 'name' => 'Ver Hoja De Vida']);
        Permission::create(['guard_name' => 'api', 'name' => 'Imprimir Hoja De Vida']);
        //permisos de roles y permisos
        Permission::create(['guard_name' => 'api', 'name' => 'Crear Roles']);
        Permission::create(['guard_name' => 'api', 'name' => 'Editar Roles']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Roles']);
        Permission::create(['guard_name' => 'api', 'name' => 'Eliminar Roles']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Permisos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Asignar Permisos']);

        // permisos de listas
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Departamentos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Municipios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Accesorios']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Tipos Equipos']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Clientes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Sedes']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Roles']);
        Permission::create(['guard_name' => 'api', 'name' => 'Listar Permisos']);


        // roles
        $role3 = Role::create(['guard_name' => 'api', 'name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $user = \App\Models\User::find(1);
        $user->assignRole($role3);

        // Obtener el rol Super-Admin
        $role = Role::findByName('Super-Admin', 'api');

        // Obtener todos los permisos
        $permissions = Permission::all();

        // Asignar todos los permisos al rol
        $role->syncPermissions($permissions);
    }
}
