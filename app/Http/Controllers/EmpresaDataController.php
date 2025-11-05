<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sede;
use App\Models\Equipo;
use App\Http\Resources\UserResource;
use App\Http\Resources\SedeResource;
use App\Http\Resources\EquipoResource;

class EmpresaDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Obtener todos los usuarios de la empresa del usuario logueado
     */
    public function usuariosEmpresa()
    {
        $user = auth()->user();

        // Verificar que el usuario tenga empresa asociada
        if (!$user->sede || !$user->sede->empresa_id) {
            return response()->json([
                'message' => 'Usuario no tiene empresa asociada',
                'data' => []
            ], 200);
        }

        $empresaId = $user->sede->empresa_id;

        // Obtener todos los usuarios de la misma empresa
        $usuarios = User::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
            ->with(['sede.empresa', 'roles'])
            ->get();

        return response()->json([
            'message' => 'Usuarios de la empresa obtenidos exitosamente',
            'data' => UserResource::collection($usuarios),
            'empresa_id' => $empresaId
        ]);
    }

    /**
     * Obtener todas las sedes de la empresa del usuario logueado
     */
    public function sedesEmpresa()
    {
        $user = auth()->user();

        // Verificar que el usuario tenga empresa asociada
        if (!$user->sede || !$user->sede->empresa_id) {
            return response()->json([
                'message' => 'Usuario no tiene empresa asociada',
                'data' => []
            ], 200);
        }

        $empresaId = $user->sede->empresa_id;

        // Obtener todas las sedes de la empresa
        $sedes = Sede::where('empresa_id', $empresaId)
            ->with(['empresa', 'departamento', 'municipio'])
            ->get();

        return response()->json([
            'message' => 'Sedes de la empresa obtenidas exitosamente',
            'data' => SedeResource::collection($sedes),
            'empresa_id' => $empresaId
        ]);
    }

    /**
     * Obtener todos los equipos de la empresa del usuario logueado
     */
    public function equiposEmpresa()
    {
        $user = auth()->user();

        // Verificar que el usuario tenga empresa asociada
        if (!$user->sede || !$user->sede->empresa_id) {
            return response()->json([
                'message' => 'Usuario no tiene empresa asociada',
                'data' => []
            ], 200);
        }

        $empresaId = $user->sede->empresa_id;

        // Obtener todos los equipos de las sedes de la empresa
        $equipos = Equipo::whereHas('sede', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
            ->with(['sede.empresa', 'accesorios'])
            ->get();

        return response()->json([
            'message' => 'Equipos de la empresa obtenidos exitosamente',
            'data' => EquipoResource::collection($equipos),
            'empresa_id' => $empresaId
        ]);
    }

    /**
     * Obtener información general de la empresa del usuario logueado
     */
    public function infoEmpresa()
    {
        $user = auth()->user();

        // Verificar que el usuario tenga empresa asociada
        if (!$user->sede || !$user->sede->empresa_id) {
            return response()->json([
                'message' => 'Usuario no tiene empresa asociada',
                'data' => null
            ], 200);
        }

        $empresa = $user->sede->empresa;

        // Contar estadísticas de la empresa
        $estadisticas = [
            'total_usuarios' => User::whereHas('sede', function ($query) use ($empresa) {
                $query->where('empresa_id', $empresa->id);
            })->count(),

            'total_sedes' => Sede::where('empresa_id', $empresa->id)->count(),

            'total_equipos' => Equipo::whereHas('sede', function ($query) use ($empresa) {
                $query->where('empresa_id', $empresa->id);
            })->count(),
        ];

        return response()->json([
            'message' => 'Información de la empresa obtenida exitosamente',
            'data' => [
                'empresa' => [
                    'id' => $empresa->id,
                    'nombre' => $empresa->nombre,
                    'nit' => $empresa->nit,
                    'logo' => $empresa->logo,
                    'logo_url' => $empresa->logo_url,
                    'tipo' => $empresa->tipo
                ],
                'estadisticas' => $estadisticas
            ]
        ]);
    }
}
