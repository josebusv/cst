<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $exception)
    {
        // Forzar JSON si la ruta comienza con /api o si el cliente espera JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($exception instanceof AuthenticationException) {
                return $this->errorResponse('No autenticado.', 'UNAUTHENTICATED', 401, $exception->getMessage());
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Datos inválidos',
                    'code' => 'VALIDATION_ERROR',
                    'error' => 'Datos inválidos',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof ModelNotFoundException) {
                return $this->errorResponse('Recurso no encontrado', 'NOT_FOUND', 404, $exception->getMessage());
            }

            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse('Ruta no encontrada', 'NOT_FOUND', 404, $exception->getMessage());
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse('Método HTTP no permitido', 'METHOD_NOT_ALLOWED', 405, $exception->getMessage());
            }

            if ($exception instanceof AuthorizationException || $exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                return $this->errorResponse(
                    'No tienes los permisos necesarios para acceder a este recurso.',
                    'FORBIDDEN',
                    403,
                    $exception->getMessage()
                );
            }

            // Cualquier otra HttpException (403 de abort(), 429, 400, etc.) conserva su codigo.
            if ($exception instanceof HttpException) {
                $status = $exception->getStatusCode();

                return $this->errorResponse(
                    $exception->getMessage() ?: 'Solicitud rechazada',
                    $this->codeForStatus($status),
                    $status,
                    $exception->getMessage(),
                    $exception->getHeaders()
                );
            }

            return $this->errorResponse(
                'Error interno del servidor',
                'SERVER_ERROR',
                500,
                config('app.debug') ? $exception->getMessage() : null
            );
        }

        return parent::render($request, $exception);
    }

    /**
     * Cuerpo de error uniforme: { message, code, error } (+ errors en validacion).
     */
    private function errorResponse(string $message, string $code, int $status, ?string $error = null, array $headers = [])
    {
        return response()->json([
            'message' => $message,
            'code' => $code,
            'error' => $error,
        ], $status, $headers);
    }

    private function codeForStatus(int $status): string
    {
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
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
