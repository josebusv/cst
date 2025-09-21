<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Cliente;
use App\Models\Sede;
use App\Models\Accesorio;
use App\Models\TipoEquipo;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class ListaController extends Controller
{
    
    /**
     * Lista Departamentos
     */
    public function listarDepartamentos()
    {
        $departamentos = Departamento::select('id', 'nombre')->get();
        return response()->json($departamentos);
    }

    /**
     * Lista Municipios
     */
    public function listarMunicipios(Departamento $departamento)
    {
        $municipios = $departamento->municipios()->select('id', 'nombre')->get();
        return response()->json($municipios);
    }

    /**
     * Listar Clientes
     */
    public function listarClientes()
    {
        $clientes = Cliente::select('id', 'nombre')->get();
        return response()->json($clientes);
    }

    /**
     * Listar sedes
     */
    public function listarSedes(Cliente $cliente)
    {
        $sedes = $cliente->sedes()->select('id', 'nombre')->get();
        return response()->json($sedes);
    }

    /**
     * Listar Accesorios
     */
    public function listarAccesorios()
    {
        $accesorios = Accesorio::select('id', 'nombre')->get();
        return response()->json($accesorios);
    }

    /**
     * lista de tipos de equipos
     */
    public function listarTiposEquipos()
    {
        $tiposEquipos = \App\Models\TipoEquipo::select('id', 'tipo')->get();
        return response()->json($tiposEquipos);
    }

    /**
     * Listar Roles
     */
    public function listarRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }

    /**
     * Listar Permisos
     */
    public function listarPermisos()
    {
        $permisos = Permission::select('id', 'name')->orderBy('id')->get();
        return response()->json($permisos);
    }
}
