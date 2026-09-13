# Despliegue en Hostinger (Laravel + Angular)

Guía para el backend (`cst`) y el frontend (`front_cst`).

## 1. Configuración de producción (`.env`)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.cst-colombia.com.co
APP_FRONTEND_URL=https://cst-colombia.com.co
LOG_LEVEL=warning
LOG_CHANNEL=errors
LOG_DAILY_DAYS=20

# Observabilidad: alertas de error (configura UNO de estos; opcional)
LOG_ALERT_LEVEL=error
LOG_SLACK_WEBHOOK_URL=
LOG_TELEGRAM_BOT_TOKEN=
LOG_TELEGRAM_CHAT_ID=
LOG_WEBHOOK_URL=

# Hosting compartido (sin Redis)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

> Si el plan es VPS/Cloud con Redis, usar `redis` en los tres.
> `config/imagen_proxy.php` solo permite hosts derivados de `APP_URL`/`APP_FRONTEND_URL`.

## 2. Backend (`cst`)

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link          # solo la primera vez
php artisan optimize              # config + rutas + vistas
```

Permisos de escritura:

```
storage/framework/{cache,sessions,views}
storage/logs
bootstrap/cache
```

Si cambias el `.env`: `php artisan config:clear` y de nuevo `php artisan optimize`.

## 3. Frontend (`front_cst`)

```bash
npm ci
npm run build
# subir dist/coreui-free-angular-admin-template/browser/ al public_html del front
```

O usar el script existente: `bash deploy.sh <usuario@host> <ruta_public_html>`.

## 4. Verificación post-despliegue

- `GET https://api…/api/auth/login` responde (POST con credenciales válidas → 200).
- El front carga y autentica; `assets/fonts/*.woff2` responden 200.
- Export a PDF de una hoja de vida larga (varias páginas legibles).
- `?url=/storage/../../../.env` en `imagen-proxy` responde 404.
- Todas las respuestas traen el header `X-Request-Id` (usar para correlacionar logs).

## 5. Rollback

- Código: `git checkout <tag/commit anterior>` + `composer install` + `php artisan optimize`.
- Base de datos: `php artisan migrate:rollback --step=1` (revisar antes).
- Frontend: restaurar el build anterior del `dist`.

## 6. Checklist pre-producción

- [ ] `APP_DEBUG=false` y `LOG_CHANNEL=daily`.
- [ ] `php artisan optimize` ejecutado.
- [ ] Backup de BD antes de migrar.
- [ ] `php artisan migrate --force` sin pendientes.
- [ ] `storage:link` presente.
- [ ] Assets del front actualizados (fuentes incluidas).
- [ ] CORS con los orígenes correctos.
- [ ] Monitoreo/alertas de logs activos.

## 7. Alertas de error sin Slack (opcional)

Con `LOG_CHANNEL=errors` los errores se guardan en disco y, ademas, se envian al notificador que configures. Elige UNA via:`r

- **Telegram** (gratis, sin cuenta de pago): crea un bot con @BotFather, obten el token y tu chat id:`r
  ``
  LOG_TELEGRAM_BOT_TOKEN=123456:ABC...`r
  LOG_TELEGRAM_CHAT_ID=987654321`r
  ``
- **Webhook generico** (Discord, Google Chat, Microsoft Teams o endpoint propio):`r
  ``
  LOG_WEBHOOK_URL=https://discord.com/api/webhooks/...`r
  ``
- **Slack** (si algun dia lo usas): `LOG_SLACK_WEBHOOK_URL=...`r

`LOG_ALERT_LEVEL` define el nivel minimo (por defecto `error`). Los notificadores nunca lanzan errores si el envio falla.


> Diagnostico y pruebas de logs/alertas con tinker: ver `docs/OBSERVABILIDAD.md`.


## 8. Preproduccion (test / apitest)

Dominios:
- Front: https://test.cst-colombia.com.co
- API:   https://apitest.cst-colombia.com.co

### Backend (.env en el servidor)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://apitest.cst-colombia.com.co
APP_FRONTEND_URL=https://test.cst-colombia.com.co
LOG_CHANNEL=errors
LOG_LEVEL=warning
LOG_DAILY_DAYS=20
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

Luego:
php artisan migrate --force
php artisan storage:link
php artisan optimize

CORS ya permite https://test.cst-colombia.com.co (config/cors.php) y el proxy de imagenes
deriva su host permitido de APP_URL (config/imagen_proxy.php).

### Frontend
npm ci
npm run build:staging      # usa environment.staging.ts -> https://apitest.cst-colombia.com.co/api/auth/
# Subir dist/coreui-free-angular-admin-template/browser/ al public_html de test.cst-colombia.com.co
# (o: bash deploy.sh <usuario@host> <ruta_public_html>)

### Verificacion
- Login en https://test... responde y guarda el token.
- En Network, las llamadas van a https://apitest.cst-colombia.com.co/api/...
- Respuesta CORS con Access-Control-Allow-Origin: https://test.cst-colombia.com.co
- Si aparece mixed content o url() en http, configurar TrustProxies ($proxies = '*') por el proxy de Hostinger.

## 9. Document root y seguridad (IMPORTANTE)

La raiz web de Laravel debe ser la carpeta **public/**, nunca la raiz del proyecto.
Si pegas todo el proyecto dentro de public_html, quedan expuestos por HTTP:
`/artisan`, `/composer.json`, `/composer.lock`, `/config/*`, `/vendor/*`, etc.

### Correcto
Document Root del (sub)dominio -> `.../cst/public`

### Si no puedes cambiar el Document Root
Sube el archivo `.htaccess` de la raiz del proyecto (incluido en este repo) y
verifica que redirige todo a `public/`. Comprueba con:
`curl.exe -s -o NUL -w "%{http_code}" https://tu-api/artisan`  -> debe dar 404
