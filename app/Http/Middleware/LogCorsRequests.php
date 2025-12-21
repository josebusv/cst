<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Middleware\HandleCors as Middleware;

class LogCorsRequests extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Log the incoming request to confirm the middleware is running
        Log::info('CORS Middleware Triggered.', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'origin' => $request->header('Origin'),
        ]);

        $response = parent::handle($request, $next);

        // Log the outgoing response to see what headers are being set
        Log::info('CORS Response Processed.', [
            'status' => $response->getStatusCode(),
            'has_acao_header' => $response->headers->has('Access-Control-Allow-Origin'),
            'headers' => $response->headers->all(),
        ]);

        return $response;
    }
}
