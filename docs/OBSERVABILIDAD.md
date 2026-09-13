# Observabilidad: comandos de diagnóstico con Tinker

Todos los ejemplos usan `php artisan tinker --execute "..."` (una línea, sin entrar al modo interactivo).

## Notación de comillas según la terminal

- **PowerShell (Windows):** comillas dobles por fuera y **simples** por dentro. Si el código PHP
  contiene `$` (p. ej. en `fn($l) => ...`), escápalo con backtick: `` `$ ``.
- **Bash / Linux (Hostinger SSH):** puedes usar comillas simples por fuera y dobles por dentro,
  sin escapar `$`.

---

## 1. Ver la configuración activa

```bash
php artisan tinker --execute "echo 'canal='.config('logging.default').' nivel='.config('logging.channels.daily.level').' dias='.config('logging.channels.daily.days');"
```

```bash
php artisan tinker --execute "echo 'notificadores='.implode(', ', config('logging.channels.errors.channels'));"
```

## 2. Escribir logs de prueba (por nivel)

```bash
php artisan tinker --execute "Log::debug('prueba debug');"
php artisan tinker --execute "Log::info('prueba info');"
php artisan tinker --execute "Log::warning('prueba warning');"
php artisan tinker --execute "Log::error('prueba error');"
php artisan tinker --execute "Log::critical('prueba critical');"
```

## 3. Reportar una excepción (simula un error no capturado)

```bash
php artisan tinker --execute "report(new \Exception('excepcion de prueba desde tinker'));"
```

## 4. Probar los canales de alerta

```bash
# Stack completo: diario + notificadores configurados (Slack/Telegram/Webhook)
php artisan tinker --execute "Log::channel('errors')->error('alerta de prueba', ['origen' => 'tinker']);"

# Solo Telegram
php artisan tinker --execute "Log::channel('telegram')->error('prueba telegram');"

# Solo webhook genérico (Discord/Teams/Google Chat/endpoint propio)
php artisan tinker --execute "Log::channel('webhook')->error('prueba webhook');"

# Solo Slack
php artisan tinker --execute "Log::channel('slack')->error('prueba slack');"
```

> Si un canal no está configurado (sin token/URL), no envía nada y no falla.

## 5. Leer y filtrar el log

```bash
# Últimas 20 líneas del archivo de hoy
php artisan tinker --execute "echo collect(file(storage_path('logs/laravel-'.date('Y-m-d').'.log')))->take(-20)->implode('');"
```

```bash
# Contar líneas con un nivel en el día de hoy (ej. ERROR)
php artisan tinker --execute "echo collect(file(storage_path('logs/laravel-'.date('Y-m-d').'.log')))->filter(fn(`$l) => str_contains(`$l, 'ERROR'))->count();"
```
*(En Bash usa `fn($l)` sin backtick.)*

```bash
# Ver todas las líneas de un request_id concreto (correlación)
php artisan tinker --execute "echo collect(file(storage_path('logs/laravel-'.date('Y-m-d').'.log')))->filter(fn(`$l) => str_contains(`$l, 'PEGA-AQUI-EL-REQUEST-ID'))->implode('');"
```

```bash
# Listar archivos de log con su tamaño (KB)
php artisan tinker --execute "collect(glob(storage_path('logs/*.log')))->each(fn(`$f) => print(basename(`$f).' '.round(filesize(`$f)/1024, 1).' KB'.PHP_EOL));"
```

```bash
# Ver el error de cliente más reciente (enviado por el frontend)
php artisan tinker --execute "echo collect(file(storage_path('logs/laravel-'.date('Y-m-d').'.log')))->filter(fn(`$l) => str_contains(`$l, 'client.error'))->take(-1)->implode('');"
```

## 6. Verificar y limpiar cachés de configuración

```bash
# ¿está cacheada la config?
php artisan tinker --execute "echo app()->configurationIsCached() ? 'config cacheada' : 'config sin cachear';"

php artisan config:clear
php artisan optimize     # re-cachea config, rutas y vistas
```

## 7. Referencia rápida

| Qué | Comando |
|---|---|
| Canal y retención | `config('logging.default')`, `config('logging.channels.daily.days')` |
| Notificadores activos | `config('logging.channels.errors.channels')` |
| Probar escritura | `Log::info('...')`, `Log::error('...')` |
| Simular error grave | `report(new \Exception('...'))` |
| Probar alerta | `Log::channel('errors')->error('...')` |
| Archivo de hoy | `storage/logs/laravel-'.date('Y-m-d').'.log` |
| Correlación | filtrar por `request_id` (header `X-Request-Id`) |

## Notas

- Cada línea incluye `request_id`, `method`, `path`, `ip`, `user_id`, `status` y `duration_ms`
  (lo agrega el middleware `AddRequestId`), así que puedes correlacionar toda una petición.
- `report()` registra en el canal por defecto; el `Handler` registra automáticamente las
  excepciones no capturadas.
- Los canales de alerta **nunca lanzan errores**: un fallo al notificar se ignora.
- En producción usa `LOG_LEVEL=warning` para evitar el ruido de `debug/info`.
