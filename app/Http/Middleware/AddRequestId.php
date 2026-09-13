<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Genera/propaga un X-Request-Id y agrega contexto a todos los logs del request,
 * de modo que se puedan correlacionar las líneas de log de una misma petición.
 */
class AddRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);
        $startedAt = microtime(true);

        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        $response = $next($request);

        $status = $response->getStatusCode();
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $userId = optional($request->user())->id;

        Log::withContext([
            'user_id' => $userId,
            'status' => $status,
            'duration_ms' => $durationMs,
        ]);

        if ($status >= 500 || $durationMs > 1000) {
            Log::warning('http.request', ['status' => $status, 'duration_ms' => $durationMs]);
        } else {
            Log::debug('http.request');
        }

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
