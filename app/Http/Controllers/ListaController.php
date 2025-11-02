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
use App\Http\Resources\DepartamentoListResource;
use App\Http\Resources\MunicipioListResource;
use App\Http\Resources\ClienteListResource;
use App\Http\Resources\SedeListResource;
use App\Http\Resources\AccesorioListResource;
use App\Http\Resources\TipoEquipoListResource;
use App\Http\Resources\RoleListResource;
use App\Http\Resources\PermissionResource;

class ListaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Listar Departamentos')->only('listarDepartamentos');
        $this->middleware('can:Listar Municipios')->only('listarMunicipios');
        $this->middleware('can:Listar Clientes')->only('listarClientes');
        $this->middleware('can:Listar Sedes')->only('listarSedes');
        $this->middleware('can:Listar Accesorios')->only('listarAccesorios');
        $this->middleware('can:Listar Tipos Equipos')->only('listarTiposEquipos');
        $this->middleware('can:Listar Roles')->only('listarRoles');
        $this->middleware('can:Listar Permisos')->only('listarPermisos');
    }

    /**
     * Lista Departamentos
     */
    public function listarDepartamentos()
    {
        $departamentos = Departamento::all();
        return DepartamentoListResource::collection($departamentos);
    }

    /**
     * Lista Municipios
     */
    public function listarMunicipios(Departamento $departamento)
    {
        $municipios = $departamento->municipios;
        return MunicipioListResource::collection($municipios);
    }

    /**
     * Lista Clientes
     */
    public function listarClientes()
    {
        $clientes = Cliente::all();
        return ClienteListResource::collection($clientes);
    }

    /**
     * Lista Sedes
     */
    public function listarSedes(Cliente $cliente)
    {
        $sedes = $cliente->sedes;
        return SedeListResource::collection($sedes);
    }

    /**
     * Lista Accesorios
     */
    public function listarAccesorios()
    {
        $accesorios = Accesorio::all();
        return AccesorioListResource::collection($accesorios);
    }

    /**
     * Lista Tipos de Equipos
     */
    public function listarTiposEquipos()
    {
        $tiposEquipos = TipoEquipo::all();
        return TipoEquipoListResource::collection($tiposEquipos);
    }

    /**
     * Lista Roles
     */
    public function listarRoles()
    {
        $roles = Role::all();
        return RoleListResource::collection($roles);
    }

    /**
     * Lista Permisos
     */
    public function listarPermisos()
    {
        $permisos = Permission::orderBy('id')->get();
        return PermissionResource::collection($permisos);
    }
}
