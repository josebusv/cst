<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
use App\Models\Empresa;
use App\Models\User;
use App\Models\ClasificacionBiomedica;
use App\Models\Consumible;
use App\Http\Resources\UserResource;

class ListaController extends Controller
{
    /**
     * Catálogos estables (cambian poco): 6 horas.
     */
    private const TTL_CATALOG = 21600;

    /**
     * Datos operativos (cambian con frecuencia): 5 minutos.
     */
    private const TTL_OPERATIVO = 300;

    public function __construct()
    {
        $this->middleware('can:Listar Departamentos')->only('listarDepartamentos');
        $this->middleware('can:Listar Municipios')->only('listarMunicipios');
        $this->middleware('can:Listar Clientes')->only('listarClientes');
        $this->middleware('can:Listar Sedes')->only('listarSedes');
        $this->middleware('can:Listar Accesorios')->only('listarAccesorios');
        $this->middleware('can:Listar Accesorios')->only('listarConsumibles');
        $this->middleware('can:Listar Tipos Equipos')->only('listarTiposEquipos');
        $this->middleware('can:Listar Roles')->only('listarRoles');
        $this->middleware('can:Listar Permisos')->only('listarPermisos');
        $this->middleware('can:Ver Técnicos')->only('listarTecnicos');
        $this->middleware('can:Listar Empresas')->only('listarEmpresas');
    }

    /**
     * Lista Departamentos
     */
    public function listarDepartamentos()
    {
        $departamentos = Cache::remember(
            'lista.departamentos',
            self::TTL_CATALOG,
            fn () => Departamento::all()
        );

        return DepartamentoListResource::collection($departamentos);
    }

    /**
     * Lista Municipios
     */
    public function listarMunicipios(Departamento $departamento)
    {
        $municipios = Cache::remember(
            'lista.municipios.' . $departamento->id,
            self::TTL_CATALOG,
            fn () => $departamento->municipios
        );

        return MunicipioListResource::collection($municipios);
    }

    /**
     * Lista Clientes
     */
    public function listarClientes()
    {
        $clientes = Cache::remember(
            'lista.clientes',
            self::TTL_OPERATIVO,
            fn () => Cliente::orderBy('nombre')->get()
        );

        return ClienteListResource::collection($clientes);
    }

    /**
     * Lista Sedes
     */
    public function listarSedes($empresa)
    {
        $empresa = Empresa::findOrFail($empresa);

        $sedes = Cache::remember(
            'lista.sedes.' . $empresa->id,
            self::TTL_OPERATIVO,
            fn () => $empresa->sedes
        );

        return SedeListResource::collection($sedes);
    }

    /**
     * Lista Accesorios
     */
    public function listarAccesorios()
    {
        $accesorios = Cache::remember(
            'lista.accesorios',
            self::TTL_CATALOG,
            fn () => Accesorio::all()
        );

        return AccesorioListResource::collection($accesorios);
    }

    /**
     * Lista Tipos de Equipos
     */
    public function listarTiposEquipos()
    {
        $tiposEquipos = Cache::remember(
            'lista.tipos_equipos',
            self::TTL_CATALOG,
            fn () => TipoEquipo::all()
        );

        return TipoEquipoListResource::collection($tiposEquipos);
    }

    /**
     * Lista Roles
     */
    public function listarRoles()
    {
        $roles = Cache::remember(
            'lista.roles',
            self::TTL_CATALOG,
            fn () => Role::all()
        );

        return RoleListResource::collection($roles);
    }

    /**
     * Lista Permisos
     */
    public function listarPermisos()
    {
        $permisos = Cache::remember(
            'lista.permisos',
            self::TTL_CATALOG,
            fn () => Permission::orderBy('id')->get()
        );

        return PermissionResource::collection($permisos);
    }

    /**
     * Lista Técnicos (usuarios con rol Operador) + admins de la empresa principal
     */
    public function listarTecnicos()
    {
        $tecnicos = Cache::remember(
            'lista.tecnicos',
            60,
            function () {
                $operadores = User::role('Operador')->with('sede.empresa')->get();

                $principalEmpresaId = Empresa::where('tipo', 'principal')->value('id');

                $adminsPrincipal = collect();
                if ($principalEmpresaId) {
                    $adminsPrincipal = User::role(['Administrador', 'Super-Admin'])
                        ->whereHas('sede', function ($q) use ($principalEmpresaId) {
                            $q->where('empresa_id', $principalEmpresaId);
                        })
                        ->with('sede.empresa')
                        ->get();
                }

                return $operadores->merge($adminsPrincipal)->unique('id')->values();
            }
        );

        return UserResource::collection($tecnicos);
    }

    /**
     * Lista Clasificaciones Biomédicas
     */
    public function listarClasificacionesBiomedicas()
    {
        return Cache::remember(
            'lista.clasificaciones_biomedicas',
            self::TTL_CATALOG,
            fn () => ClasificacionBiomedica::all()
        );
    }

    /**
     * Lista Consumibles
     */
    public function listarConsumibles()
    {
        return Cache::remember(
            'lista.consumibles',
            self::TTL_CATALOG,
            fn () => Consumible::orderBy('nombre')->get()
        );
    }

    /**
     * Lista Empresas (clientes + principal + proveedor)
     */
    public function listarEmpresas()
    {
        return Cache::remember(
            'lista.empresas',
            self::TTL_OPERATIVO,
            fn () => Empresa::all(['id', 'nombre', 'tipo'])
        );
    }
}
