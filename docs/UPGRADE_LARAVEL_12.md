# Upgrade Laravel 10 -> 12 LTS

## Por que
Laravel 10 esta **EOL** (sin parches de seguridad) y sigue aportando 3 avisos de
`composer audit` que no se pueden corregir sin cambiar de version mayor.

## Prerrequisitos
- PHP >= 8.2 (servidor actual: 8.2.33 ✅). Recomendado 8.3.
- Ventana de mantenimiento: es un cambio estructural, no hotfix.

## Compatibilidad de dependencias (verificado)

| Paquete | Actual | Destino | Nota |
|---|---|---|---|
| laravel/framework | 10.50.3 | ^12.0 | |
| spatie/laravel-permission | 5.11.1 | ^6.0 | **breaking**: cache/`PermissionRegistrar`, `guard_name` |
| laravel/sanctum | 3.3.3 | ^4.0 o **eliminar** | la app usa JWT; Sanctum esta comentado |
| tymon/jwt-auth | 2.3.0 | 2.3.0 | ya soporta ^12 ✅ |
| laravel/tinker | 2.10 | ^2.10 | |
| nunomaduro/collision | 7.x | ^8.0 | |
| phpunit/phpunit | 10.x | ^11.0 | |
| larastan/larastan | 2.x | ^3.0 | opcional (analisis) |
| spatie/laravel-ignition | 2.9 | ^2.9 | |

## Cambios estructurales (Laravel 11+)

1. **`bootstrap/app.php`**: pasa a ser el punto de configuracion.
   ```php
   return Application::configure(basePath: dirname(__DIR__))
       ->withRouting(
           api: __DIR__.'/../routes/api.php',
           apiPrefix: 'api',
           commands: __DIR__.'/../routes/console.php',
           health: '/up',
       )
       ->withMiddleware(function (Middleware $middleware) {
           $middleware->trustProxies(at: '*');
           $middleware->prepend(\App\Http\Middleware\SecurityHeaders::class);
           $middleware->append(\App\Http\Middleware\AddRequestId::class);
           $middleware->api(prepend: [
               \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
               \Illuminate\Routing\Middleware\SubstituteBindings::class,
           ]);
           $middleware->alias([
               'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
               'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
               'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
           ]);
       })
       ->withExceptions(function (Exceptions $exceptions) {
           // Portar App\Exceptions\Handler::render() aqui:
           $exceptions->render(function (Throwable $e, $request) {
               if ($request->is('api/*') || $request->expectsJson()) {
                   // AuthenticationException 401, ValidationException 422,
                   // ModelNotFound 404, HttpException status, etc.
               }
           });
       })->create();
   ```
2. **`app/Http/Kernel.php`**: eliminar (su contenido pasa a `withMiddleware`).
3. **`app/Console/Kernel.php`**: eliminar (usar `routes/console.php`).
4. **`app/Exceptions/Handler.php`**: el render pasa a `withExceptions` (o mantener
   la clase y registrarla explicitamente; por defecto L11/12 no la usa).
5. **`bootstrap/providers.php`** (nuevo): `[App\Providers\AppServiceProvider::class,
   App\Providers\AuthServiceProvider::class, App\Providers\EventServiceProvider::class]`.
   El `RouteServiceProvider` deja de ser necesario si se usa `withRouting`;
   mover los `RateLimiter` (`api`, `login`) a `AppServiceProvider`.
6. **`config/app.php`** ya no lista providers (L11+ usa `bootstrap/providers.php`).

## Breaking changes a revisar
- `Password::defaults()` / reglas: sin cambios.
- `$table->enum(...)`: sin cambios.
- `Str::`, `Carbon`: sin cambios relevantes.
- Spatie Permission v6:
  - `middlewares` cambian de namespace (`Spatie\Permission\Middlewares\*`).
  - Cache de permisos: revisar `forgetCachedPermissions` en tests.
  - `PermissionRegistrar` ahora resuelve por `$middleware->alias`.
- Sanctum: si no se usa, **quitarlo** del `composer.json` (menos superficie).
- `Dotenv`/`phpdotenv` y `config:cache`: re-cachear.

## Pasos
```bash
git checkout -b chore/laravel-12
composer config audit.block-insecure false        # temporal
composer require laravel/framework:^12.0 spatie/laravel-permission:^6.0 \
    nunomaduro/collision:^8.0 --no-interaction --with-all-dependencies
composer remove laravel/sanctum --no-interaction  # opcional (no se usa)
composer require --dev phpunit/phpunit:^11.0 larastan/larastan:^3.0 \
    --no-interaction --with-all-dependencies
# editar bootstrap/app.php, bootstrap/providers.php, rutas y exceptions
php artisan optimize:clear
php artisan test
composer audit --no-dev
```

## Verificacion
- [ ] `php artisan test` verde (37 tests).
- [ ] `composer audit --no-dev` sin avisos (o allowlist documentada).
- [ ] Login/logout/refresh JWT.
- [ ] Rutas con `can:`/`role:` responden 403 (no 500).
- [ ] `php artisan route:list` sin errores.
- [ ] Frontend (staging) autentica y CRUD de equipos/reportes/hoja de vida.

## Rollback
- `git checkout feature/portal-cliente` + `composer install` + `php artisan optimize`.
- Es un cambio de solo codigo (sin migraciones de BD nuevas), por lo que el
  rollback es inmediato.

## Alternativa de menor riesgo
Si no hay ventana para L12: subir a **Laravel 11** primero (mismo salto
estructural) o mantener L10 aceptando los 3 avisos del framework con
`config.audit.ignore` documentado y fecha de revision.
