<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cabeceras de seguridad (CSP, nosniff, etc.) y X-Request-Id en todas las peticiones.
        $middleware->prepend(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\AddRequestId::class);

        // No recortar espacios en los campos de contraseña.
        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
        ]);

        // Grupo "api": aplica el limiter "api" (throttle:api) + SubstituteBindings.
        // Equivale al antiguo grupo 'api' del Http\Kernel.
        $middleware->throttleApi();

        // Alias propios + Spatie Permission v6 (namespace singular "Middleware").
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Contrato de error uniforme para la API: { message, code, error } (+ errors en validación).
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $json = function (string $message, string $code, int $status, ?string $error = null, array $headers = []) {
                return response()->json([
                    'message' => $message,
                    'code' => $code,
                    'error' => $error,
                ], $status, $headers);
            };

            $codeForStatus = function (int $status): string {
                return match ($status) {
                    400 => 'BAD_REQUEST',
                    401 => 'UNAUTHENTICATED',
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    409 => 'CONFLICT',
                    422 => 'VALIDATION_ERROR',
                    429 => 'TOO_MANY_REQUESTS',
                    default => $status >= 500 ? 'SERVER_ERROR' : 'HTTP_ERROR',
                };
            };

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return $json('No autenticado.', 'UNAUTHENTICATED', 401, $e->getMessage());
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'Datos inválidos',
                    'code' => 'VALIDATION_ERROR',
                    'error' => 'Datos inválidos',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $json('Recurso no encontrado', 'NOT_FOUND', 404, null);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return $json('Ruta no encontrada', 'NOT_FOUND', 404, null);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return $json('Método HTTP no permitido', 'METHOD_NOT_ALLOWED', 405, $e->getMessage());
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException
                || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                return $json(
                    'No tienes los permisos necesarios para acceder a este recurso.',
                    'FORBIDDEN',
                    403,
                    $e->getMessage()
                );
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();

                return $json(
                    $e->getMessage() ?: 'Solicitud rechazada',
                    $codeForStatus($status),
                    $status,
                    $e->getMessage(),
                    $e->getHeaders()
                );
            }

            return $json(
                'Error interno del servidor',
                'SERVER_ERROR',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        });
    })->create();
