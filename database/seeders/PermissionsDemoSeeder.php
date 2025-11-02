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

        // crear roles específicos
        $superAdminRole = Role::create(['guard_name' => 'api', 'name' => 'Super-Admin']);
        $adminRole = Role::create(['guard_name' => 'api', 'name' => 'Administrador']);
        $operadorRole = Role::create(['guard_name' => 'api', 'name' => 'Operador']);
        $clienteRole = Role::create(['guard_name' => 'api', 'name' => 'Cliente']);

        // Asignar usuario al rol Super-Admin
        $user = \App\Models\User::find(1);
        if ($user) {
            $user->assignRole($superAdminRole);
        }

        // Obtener todos los permisos
        $allPermissions = Permission::all();

        // Super-Admin tiene todos los permisos
        $superAdminRole->syncPermissions($allPermissions);

        // Administrador: puede gestionar usuarios, clientes, sedes, equipos
        $adminRole->syncPermissions([
            'Crear Usuarios',
            'Editar Usuarios',
            'Listar Usuarios',
            'Eliminar Usuarios',
            'Crear Clientes',
            'Editar Clientes',
            'Listar Clientes',
            'Eliminar Clientes',
            'Ver Clientes',
            'Crear Sedes',
            'Editar Sedes',
            'Listar Sedes',
            'Eliminar Sedes',
            'Crear Equipos',
            'Editar Equipos',
            'Listar Equipos',
            'Eliminar Equipos',
            'Listar Reportes',
            'Ver Hoja De Vida',
            'Imprimir Hoja De Vida',
            'Listar Departamentos',
            'Listar Municipios',
            'Listar Accesorios',
            'Listar Tipos Equipos'
        ]);

        // Operador: puede gestionar equipos y reportes
        $operadorRole->syncPermissions([
            'Listar Usuarios',
            'Listar Clientes',
            'Ver Clientes',
            'Listar Sedes',
            'Crear Equipos',
            'Editar Equipos',
            'Listar Equipos',
            'Crear Reportes',
            'Firmar Reportes',
            'Listar Reportes',
            'Imprimir Reportes',
            'Crear Hoja De Vida',
            'Ver Hoja De Vida',
            'Imprimir Hoja De Vida',
            'Listar Departamentos',
            'Listar Municipios',
            'Listar Accesorios',
            'Listar Tipos Equipos'
        ]);

        // Cliente: solo puede ver información relacionada con sus equipos
        $clienteRole->syncPermissions([
            'Ver Clientes',
            'Listar Sedes',
            'Listar Equipos',
            'Listar Reportes',
            'Ver Hoja De Vida',
            'Imprimir Hoja De Vida',
            'Imprimir Reportes'
        ]);
    }
}
