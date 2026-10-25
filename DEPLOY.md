# Despliegue en Hostinger (Laravel + Angular)

Guía para el backend (`cst`) y el frontend (`front_cst`).
Guía específica del frontend: `front_cst/DEPLOY.md`.

> Última revisión: 2026-10-24 (upgrade a Laravel 12 **mergeado y pusheado a `main`**).

## 0. Requisitos y estado de versiones

| Componente | Requisito | Nota |
|---|---|---|
| PHP (servidor) | >= 8.2 | Servidor actual: 8.2.33 ✅ |
| Laravel | 12.x en `main` | Upgrade ejecutado en `chore/laravel-12` y mergeado (fast-forward) a `main` el 2026-10-24 (ver `docs/UPGRADE_LARAVEL_12.md`) |
| Composer | 2.x | En el servidor: `composer install --no-dev --optimize-autoloader` |
| MySQL/MariaDB | Según plan Hostinger | Credenciales en `.env` (`DB_*`) |
| Node.js (build front) | ^20.19.0 \|\| ^22.12.0 \|\| ^24.0.0 | npm >= 9 |
| SSH + rsync | Para los scripts de deploy | `deploy.local.sh` (no versionado) con `SSH_HOST`/`SSH_PATH` |

> Los scripts de deploy actualizan el código con `git pull --ff-only` **sobre la rama que tenga
> checked out el servidor** (normalmente `main`). Verifica la rama antes de desplegar:
> `ssh usuario@host "cd /ruta/cst && git branch --show-current"`.
>
> ⚠️ El **primer deploy de `main` con Laravel 12** en cada servidor (preprod y prod) despliega el
> upgrade completo: confirmar PHP >= 8.2 (`php -v`), hacer **backup de BD** antes del `migrate`,
> y verificar login + `/up` después (secciones 4 y 8).

## 1. Configuración de producción (`.env`)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.cst-colombia.com.co
APP_FRONTEND_URL=https://cst-colombia.com.co
LOG_LEVEL=warning
LOG_CHANNEL=errors
LOG_DAILY_DAYS=20

# Autenticación JWT (OBLIGATORIO: sin JWT_SECRET el login responde 500)
# Generar con: php artisan jwt:secret
JWT_SECRET=

# Base de datos (MySQL del plan Hostinger)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

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
> Correo (reset de contraseña): configurar `MAIL_*` con SMTP real; ver
> `DOCUMENTACION_PASSWORD_RESET.md` (desarrollo local: `CONFIGURACION_MAILPIT.md`).

## 2. Backend (`cst`)

Primera instalación (antes de los comandos de deploy):

```bash
cp .env.example .env              # completar DB_*, MAIL_*, APP_URL, APP_FRONTEND_URL
php artisan key:generate
php artisan jwt:secret            # genera JWT_SECRET (obligatorio para el login)
```

Deploy / actualización:

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

> Guía detallada del frontend (entornos, scripts, `.htaccess`, verificación): `front_cst/DEPLOY.md`.

## 4. Verificación post-despliegue

- `GET https://api…/up` responde 200 (health check definido en `bootstrap/app.php`).
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

- [ ] `APP_DEBUG=false` y `LOG_CHANNEL=errors` (stack que incluye `daily`).
- [ ] `JWT_SECRET` definida (`php artisan jwt:secret`).
- [ ] `php artisan optimize` ejecutado.
- [ ] Backup de BD antes de migrar.
- [ ] `php artisan migrate --force` sin pendientes.
- [ ] `storage:link` presente.
- [ ] Assets del front actualizados (fuentes incluidas).
- [ ] CORS con los orígenes correctos.
- [ ] Monitoreo/alertas de logs activos.

## 7. Alertas de error sin Slack (opcional)

Con `LOG_CHANNEL=errors` los errores se guardan en disco y, ademas, se envian al notificador que configures. Elige UNA via:

- **Telegram** (gratis, sin cuenta de pago): crea un bot con @BotFather, obten el token y tu chat id:
  ```
  LOG_TELEGRAM_BOT_TOKEN=123456:ABC...
  LOG_TELEGRAM_CHAT_ID=987654321
  ```
- **Webhook generico** (Discord, Google Chat, Microsoft Teams o endpoint propio):
  ```
  LOG_WEBHOOK_URL=https://discord.com/api/webhooks/...
  ```
- **Slack** (si algun dia lo usas): `LOG_SLACK_WEBHOOK_URL=...`

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
JWT_SECRET=<php artisan jwt:secret>
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
- Si aparece mixed content o url() en http, confiar el proxy de Hostinger: en
  `bootstrap/app.php` (Laravel 12), `$middleware->trustProxies(at: '*')`.

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

### Catalogos requeridos (endoscopia/electronica)
Ejecutar en el servidor para que aparezcan las unidades de medida y clasificaciones:
php artisan db:seed --class="Database\Seeders\UnidadesTecnicasSeeder" --force
php artisan db:seed --class="Database\Seeders\ClasificacionBiomedicaSeeder" --force

## 10. Fix: la hoja de vida no guardaba las firmas

Dos causas (commit backend `9e854b0` + frontend `374c02f`):

1. El rol **Administrador** solo tenia `Ver/Imprimir Hoja De Vida` (sin `Crear/Editar/Firmar`),
   por lo que el `PUT /hoja-vida` y `POST /hoja-vida/firma` devolvian 403.
2. El frontend solo capturaba el canvas al pulsar "Guardar Firma"; al pulsar "Guardar Hoja de Vida"
   no enviaba la firma dibujada.

Aplicar SOLO el punto 1 en el servidor (el punto 2 va en el build del front):

```bash
php artisan db:seed --class="Database\Seeders\PermissionsDemoSeeder" --force
```

> Scripts incluidos (requieren SSH): `cst/deploy-preprod.sh`, `cst/deploy-prod.sh`,
> `front_cst/deploy-preprod.sh` y `front_cst/deploy-prod.sh`
> (o `npm run deploy:preprod` / `npm run deploy:prod`). Configura `deploy.local.sh` con
> `SSH_HOST` y `SSH_PATH` (ver sección 11).

### Subir por zip: incluir TODAS las carpetas
Un zip incompleto (falta `app/`, `config/`, `routes/`, `bootstrap/`, `vendor/`, etc.) hace que la
API responda **500 en todas las peticiones**, incluso en el preflight CORS OPTIONS. Verifica el
listado de carpetas antes de subir.

No subir desde tu maquina:
- `storage/logs/*` (son locales; confunden el diagnostico, aparecen rutas `C:\...`)
- `bootstrap/cache/*.php` (caches con rutas de Windows)

Despues de subir, en el servidor:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize
```

> `PermissionsDemoSeeder` usa `syncPermissions`, por lo que re-sembrar deja los roles exactamente
> con la lista definida en el seeder. Si en el servidor hay permisos extra agregados a mano al rol
> `Administrador`, respáldalos antes (o agrega desde la UI los permisos `Crear/Editar/Firmar Hoja De Vida`).

### Verificacion del fix (post-deploy)

```bash
# 1) El rol ya tiene los permisos
php artisan tinker --execute "echo Spatie\Permission\Models\Role::findByName('Administrador','api')->permissions()->where('name','like','%Hoja De Vida%')->pluck('name');"
# 2) Login como un usuario Administrador y guardar con firma:
curl -s -X PUT https://apitest.cst-colombia.com.co/api/auth/equipos/<ID>/hoja-vida \
  -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
  -d '{"firma_realizo":"data:image/png;base64,iVBORw0KGgo...","nombre_realizo":"X"}' -w "\n%{http_code}\n"
#   -> 200
```

## 11. Comandos de despliegue (preproduccion y produccion)

> Requiere acceso SSH. En cada repo crea `deploy.local.sh` (no versionado; ignorado vía
> `.gitignore` en ambos repos) con:
> ```bash
> SSH_HOST="u123456789@cst-colombia.com.co"
> SSH_PATH="/home/u123456789/domains/<dominio>/<carpeta>"
> ```
> Los scripts asumen que el codigo se actualiza con `git pull --ff-only` en el servidor.

### 11.1 Backend - PREPRODUCCION (apitest.cst-colombia.com.co)

```bash
cd cst
./deploy-preprod.sh                        # usa deploy.local.sh
./deploy-preprod.sh usuario@host /ruta/apitest.cst-colombia.com.co/cst
```

Manual por SSH:

```bash
ssh usuario@host "cd /ruta/apitest.cst-colombia.com.co/cst && \
  git pull --ff-only && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  php artisan db:seed --class='Database\Seeders\PermissionsDemoSeeder' --force && \
  php artisan optimize"
```

### 11.2 Backend - PRODUCCION (api.cst-colombia.com.co)

```bash
cd cst
./deploy-prod.sh                           # usa deploy.local.sh
./deploy-prod.sh usuario@host /ruta/api.cst-colombia.com.co/cst
```

Manual por SSH:

```bash
ssh usuario@host "cd /ruta/api.cst-colombia.com.co/cst && \
  git pull --ff-only && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  php artisan db:seed --class='Database\Seeders\UnidadesTecnicasSeeder' --force && \
  php artisan db:seed --class='Database\Seeders\ClasificacionBiomedicaSeeder' --force && \
  (php artisan storage:link || true) && \
  php artisan optimize"
```

> Los seeders de catalogos usan `firstOrCreate` (idempotentes). El backend prod **no** re-siembra
> `PermissionsDemoSeeder`; si necesitas tocar roles en prod, aplica la seccion 10.

### 11.3 Frontend - PREPRODUCCION (test.cst-colombia.com.co)

```bash
cd front_cst
npm run deploy:preprod                     # tests + build:staging + rsync
bash deploy-preprod.sh usuario@host /ruta/test.cst-colombia.com.co/public_html
```

Manual:

```bash
npm ci
npx ng test --watch=false --browsers=ChromeHeadless
npm run build:staging
rsync -avz --delete dist/coreui-free-angular-admin-template/browser/ \
  usuario@host:/ruta/test.cst-colombia.com.co/public_html/
```

### 11.4 Frontend - PRODUCCION (cst-colombia.com.co)

```bash
cd front_cst
npm run deploy:prod                        # tests + lint + build:prod + rsync
bash deploy-prod.sh usuario@host /ruta/cst-colombia.com.co/public_html
```

Manual:

```bash
npm ci
npx ng test --watch=false --browsers=ChromeHeadless
npm run lint
npm run build:prod
rsync -avz --delete dist/coreui-free-angular-admin-template/browser/ \
  usuario@host:/ruta/cst-colombia.com.co/public_html/
```

### 11.5 Post-deploy (backend)

Tras subir codigo por zip/rsync o por script, en el servidor:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize
```

Verificacion rapida (ver seccion 4): login POST 200, header `X-Request-Id`, `/artisan` responde 404.

## 12. CI (GitHub Actions)

Ambos repos ejecutan CI en cada push/PR (`.github/workflows/ci.yml`). **Desplegar solo con CI en verde.**

- **Backend** (`cst`): PHP 8.2 + extensiones, `composer install`, `php artisan test`,
  `composer audit --no-dev` (aún con `continue-on-error: true`; con Laravel 12 ya en `main`
  el audit pasa limpio y puede endurecerse a `false`).
- **Frontend** (`front_cst`): Node 20, `npm ci`, lint, build, tests headless
  (`ChromeHeadlessCI` definido en `karma.conf.js`), `npm audit --omit=dev --audit-level=high`.

