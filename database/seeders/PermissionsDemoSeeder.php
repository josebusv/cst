<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'Crear Usuarios', 'Editar Usuarios', 'Listar Usuarios', 'Eliminar Usuarios',
            'Crear Clientes', 'Editar Clientes', 'Listar Clientes', 'Eliminar Clientes', 'Ver Clientes',
            'Crear Sedes', 'Editar Sedes', 'Listar Sedes', 'Eliminar Sedes',
            'Crear Equipos', 'Editar Equipos', 'Listar Equipos', 'Eliminar Equipos',
            'Crear Reportes', 'Firmar Reportes', 'Listar Reportes', 'Imprimir Reportes',
            'Crear Hoja De Vida', 'Editar Hoja De Vida', 'Ver Hoja De Vida', 'Imprimir Hoja De Vida', 'Firmar Hoja De Vida',
            'Crear Roles', 'Editar Roles', 'Listar Roles', 'Eliminar Roles', 'Listar Permisos', 'Asignar Permisos',
            'Listar Departamentos', 'Listar Municipios', 'Listar Accesorios', 'Listar Tipos Equipos',
            'Ver Tickets', 'Crear Tickets', 'Editar Tickets', 'Eliminar Tickets', 'Cambiar Estado Tickets',
            'Ver Cronogramas', 'Crear Cronogramas', 'Editar Cronogramas', 'Eliminar Cronogramas', 'Generar Cronogramas',
            'Ver Técnicos', 'Asignar Operadores',
            'Listar Empresas',
            'Ver Dashboard',
            'Importar',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['guard_name' => 'api', 'name' => $perm]);
        }

        $superAdminRole = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Super-Admin']);
        $adminRole = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Administrador']);
        $operadorRole = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Operador']);
        $clienteRole = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Cliente']);

        $user = \App\Models\User::find(1);
        if ($user && !$user->hasRole('Super-Admin')) {
            $user->assignRole($superAdminRole);
        }

        $allPermissions = Permission::all();

        $superAdminRole->syncPermissions($allPermissions);

        $adminRole->syncPermissions([
            'Crear Usuarios', 'Editar Usuarios', 'Listar Usuarios', 'Eliminar Usuarios',
            'Crear Clientes', 'Editar Clientes', 'Listar Clientes', 'Eliminar Clientes', 'Ver Clientes',
            'Crear Sedes', 'Editar Sedes', 'Listar Sedes', 'Eliminar Sedes',
            'Crear Equipos', 'Editar Equipos', 'Listar Equipos', 'Eliminar Equipos',
            'Listar Reportes',
            'Ver Hoja De Vida', 'Imprimir Hoja De Vida',
            'Listar Departamentos', 'Listar Municipios', 'Listar Accesorios', 'Listar Tipos Equipos',
            'Ver Tickets', 'Crear Tickets', 'Editar Tickets', 'Eliminar Tickets', 'Cambiar Estado Tickets',
            'Ver Cronogramas', 'Crear Cronogramas', 'Editar Cronogramas', 'Eliminar Cronogramas', 'Generar Cronogramas',
            'Ver Técnicos', 'Asignar Operadores',
            'Listar Empresas',
            'Ver Dashboard',
        ]);

        $operadorRole->syncPermissions([
            'Listar Usuarios', 'Listar Clientes', 'Ver Clientes',
            'Listar Sedes',
            'Crear Equipos', 'Editar Equipos', 'Listar Equipos',
            'Crear Reportes', 'Firmar Reportes', 'Listar Reportes', 'Imprimir Reportes',
            'Crear Hoja De Vida', 'Editar Hoja De Vida', 'Ver Hoja De Vida', 'Imprimir Hoja De Vida', 'Firmar Hoja De Vida',
            'Listar Departamentos', 'Listar Municipios', 'Listar Accesorios', 'Listar Tipos Equipos',
            'Ver Tickets', 'Crear Tickets', 'Editar Tickets', 'Cambiar Estado Tickets',
            'Ver Cronogramas', 'Editar Cronogramas',
            'Ver Técnicos',
            'Listar Empresas',
            'Ver Dashboard',
        ]);

        $clienteRole->syncPermissions([
            'Ver Clientes',
            'Listar Sedes', 'Listar Equipos', 'Listar Reportes',
            'Ver Hoja De Vida', 'Imprimir Hoja De Vida', 'Imprimir Reportes',
            'Ver Tickets', 'Crear Tickets',
            'Ver Cronogramas',
            'Ver Dashboard',
        ]);
    }
}
