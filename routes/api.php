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
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmpresaDataController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HojaVidaController;
use App\Http\Controllers\ClientLogController;
use App\Http\Controllers\ImportController;

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

    // Rutas públicas para recuperación de contraseña
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email')
        ->middleware('throttle:5,1'); // Máximo 5 intentos por minuto

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.reset')
        ->middleware('throttle:5,1');

    Route::post('/validate-token', [PasswordResetController::class, 'validateToken'])
        ->name('password.validate')
        ->middleware('throttle:10,1');

    // Rutas que solo requieren autenticación
    Route::middleware('auth:api')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/me', [AuthController::class, 'me'])->name('me');

        // Reporte de errores del frontend (observabilidad)
        Route::post('/logs/client', [ClientLogController::class, 'store'])->middleware('throttle:30,1');
    });

    // Rutas protegidas por permisos específicos
    Route::middleware('auth:api')->group(function () {
        // Registro de usuarios - solo Super-Admin
        Route::middleware('role:Super-Admin')->post('/register', [AuthController::class, 'register'])->name('register');

        // Dashboard
        Route::get('dashboard/admin', [DashboardController::class, 'admin'])->middleware('permission:Ver Dashboard');
        Route::get('dashboard/cliente', [DashboardController::class, 'cliente'])->middleware('permission:Ver Dashboard');

        // Importación masiva
        Route::post('importar', [ImportController::class, 'importar']);

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
        Route::patch('reportes/{reporte}/firma-tecnico', [ReporteController::class, 'updateFirmaTecnico'])->name('reportes.firma-tecnico');
        Route::patch('reportes/{reporte}/firma-cliente', [ReporteController::class, 'updateFirmaCliente'])->name('reportes.firma-cliente');

        // Tickets
        Route::apiResource('tickets', TicketController::class);
        Route::get('tickets/empresa/{empresaId}', [TicketController::class, 'ticketsPorEmpresa']);
        Route::get('tickets/tecnico/{userId}', [TicketController::class, 'ticketsPorTecnico']);
        Route::patch('tickets/{id}/estado', [TicketController::class, 'cambiarEstado']);

        // Cronogramas
Route::get('cronogramas/calendario', [CronogramaController::class, 'calendario']);
Route::get('cronogramas/empresa/{empresaId}/anual', [CronogramaController::class, 'anual']);
Route::post('cronogramas/generar', [CronogramaController::class, 'generar']);
        Route::apiResource('cronogramas', CronogramaController::class);
        Route::get('cronogramas/empresa/{empresaId}', [CronogramaController::class, 'cronogramasPorEmpresa']);
        Route::get('cronogramas/equipo/{equipoId}', [CronogramaController::class, 'cronogramasPorEquipo']);
        Route::get('cronogramas/tecnico/{userId}', [CronogramaController::class, 'cronogramasPorTecnico']);

        // Hoja de Vida
        Route::get('equipos/{id}/hoja-vida', [EquipoController::class, 'hojaVida']);
        Route::get('equipos/{equipoId}/hoja-vida-detalle', [HojaVidaController::class, 'show']);
        Route::put('equipos/{equipoId}/hoja-vida', [HojaVidaController::class, 'update']);
        Route::post('equipos/{equipoId}/hoja-vida/firma', [HojaVidaController::class, 'guardarFirma']);
        Route::post('equipos/{equipoId}/hoja-vida/imagen', [HojaVidaController::class, 'uploadImagen']);
        Route::get('unidades-tecnicas', [HojaVidaController::class, 'unidadesTecnicas']);

        // Técnicos / Operadores
        Route::get('clientes/{clienteId}/tecnicos', [ClienteController::class, 'tecnicos']);
        Route::post('clientes/{clienteId}/tecnicos', [ClienteController::class, 'asignarTecnico']);
        Route::delete('clientes/{clienteId}/tecnicos/{userId}', [ClienteController::class, 'removerTecnico']);
        Route::get('lista/tecnicos', [App\Http\Controllers\ListaController::class, 'listarTecnicos']);
        Route::get('lista/clasificaciones-biomedicas', [App\Http\Controllers\ListaController::class, 'listarClasificacionesBiomedicas']);
        Route::get('lista/empresas', [App\Http\Controllers\ListaController::class, 'listarEmpresas']);

        // Listas
        Route::get('lista/departamentos', [App\Http\Controllers\ListaController::class, 'listarDepartamentos']);
        Route::get('lista/municipios/{departamento}', [App\Http\Controllers\ListaController::class, 'listarMunicipios']);
        Route::get('lista/clientes', [App\Http\Controllers\ListaController::class, 'listarClientes']);
        Route::get('lista/sedes/{cliente}', [App\Http\Controllers\ListaController::class, 'listarSedes']);
        Route::get('lista/accesorios', [App\Http\Controllers\ListaController::class, 'listarAccesorios']);
        Route::get('lista/consumibles', [App\Http\Controllers\ListaController::class, 'listarConsumibles']);
        Route::get('lista/tipos-equipos', [App\Http\Controllers\ListaController::class, 'listarTiposEquipos']);
        Route::get('lista/roles', [App\Http\Controllers\ListaController::class, 'listarRoles']);
        Route::get('lista/permisos', [App\Http\Controllers\ListaController::class, 'listarPermisos']);

        // Datos de la empresa del usuario logueado
        Route::get('mi-empresa/usuarios', [App\Http\Controllers\EmpresaDataController::class, 'usuariosEmpresa']);
        Route::get('mi-empresa/sedes', [App\Http\Controllers\EmpresaDataController::class, 'sedesEmpresa']);
        Route::get('mi-empresa/equipos', [App\Http\Controllers\EmpresaDataController::class, 'equiposEmpresa']);
        Route::get('mi-empresa/info', [App\Http\Controllers\EmpresaDataController::class, 'infoEmpresa']);
    });
    
    // Ruta pública de impresión (requiere token por query param)
    Route::get('equipos/{equipoId}/hoja-vida/print', [HojaVidaController::class, 'print']);
});

// Proxy de imágenes (público, con CORS)
Route::get('imagen-proxy', [HojaVidaController::class, 'proxyImagen']);
