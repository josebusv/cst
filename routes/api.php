<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

    // Rutas que solo requieren autenticación
    Route::middleware('auth:api')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/me', [AuthController::class, 'me'])->name('me');
    });

    // Rutas protegidas por permisos específicos
    Route::middleware('auth:api')->group(function () {
        // Registro de usuarios - solo Super-Admin
        Route::middleware('role:Super-Admin')->post('/register', [AuthController::class, 'register'])->name('register');

        // Usuarios
        Route::apiResource('users', UserController::class);
        Route::get('users/empresa/{empresaId}', [UserController::class, 'usuariosPorEmpresa']);

        // Clientes
        Route::apiResource('clientes', ClienteController::class);

        // Sedes
        Route::apiResource('sedes', SedeController::class);

        // Roles (solo Super-Admin puede gestionar roles)
        Route::middleware('role:Super-Admin')->apiResource('roles', RoleController::class);

        // Equipos
        Route::apiResource('equipos', EquipoController::class);
        Route::get('equipos/empresa/{empresaId}', [EquipoController::class, 'equiposPorEmpresa']);

        // Reportes
        Route::apiResource('reportes', ReporteController::class);
        Route::get('reportes/equipo/{equipoId}', [ReporteController::class, 'reportesPorEquipo']);

        // Listas
        Route::get('lista/departamentos', [App\Http\Controllers\ListaController::class, 'listarDepartamentos']);
        Route::get('lista/municipios/{departamento}', [App\Http\Controllers\ListaController::class, 'listarMunicipios']);
        Route::get('lista/clientes', [App\Http\Controllers\ListaController::class, 'listarClientes']);
        Route::get('lista/sedes/{cliente}', [App\Http\Controllers\ListaController::class, 'listarSedes']);
        Route::get('lista/accesorios', [App\Http\Controllers\ListaController::class, 'listarAccesorios']);
        Route::get('lista/tipos-equipos', [App\Http\Controllers\ListaController::class, 'listarTiposEquipos']);
        Route::get('lista/roles', [App\Http\Controllers\ListaController::class, 'listarRoles']);
        Route::get('lista/permisos', [App\Http\Controllers\ListaController::class, 'listarPermisos']);
    });
});
