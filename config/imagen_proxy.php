<?php

$appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

return [
    /*
    |--------------------------------------------------------------------------
    | Hosts permitidos para el proxy de imágenes externas
    |--------------------------------------------------------------------------
    |
    | El endpoint /api/auth/imagen-proxy sólo servirá archivos del
    | almacenamiento propio o de estos hosts. Cualquier otro host se rechaza
    | para evitar SSRF.
    |
    */
    'allowed_hosts' => array_values(array_filter(array_unique(array_filter([
        $appHost,
        parse_url((string) env('APP_URL'), PHP_URL_HOST),
        parse_url((string) env('APP_FRONTEND_URL', ''), PHP_URL_HOST),
    ])))),
];
