<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Datos inválidos',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Recurso no encontrado',
                ], 404);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Ruta no encontrada',
                ], 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'message' => 'Método HTTP no permitido',
                ], 405);
            }

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }

        return parent::render($request, $exception);
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
