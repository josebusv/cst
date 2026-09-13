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

# Observabilidad (opcional): alertas de error por Slack
LOG_SLACK_WEBHOOK_URL=
LOG_SLACK_LEVEL=critical

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
