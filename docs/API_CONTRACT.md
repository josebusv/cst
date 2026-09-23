# Contrato de API

Todas las rutas cuelgan de `/api`. Salvo `imagen-proxy`, el resto vive bajo el prefijo
`/api/auth` (prefijo historico; no implica que todo sea autenticacion).

## Formato de error (uniforme)

Todo error JSON tiene la misma forma:

```json
{ "message": "Texto humano", "code": "CODIGO_MAQUINA", "error": "detalle o null" }
```

- Validacion (422) agrega `errors`: `{ "campo": ["mensaje"] }`.
- `error` es un detalle (mensaje de la excepcion) o `null`; con `APP_DEBUG=false`
  nunca se expone detalle en 500.

| Situacion | HTTP | `code` |
|---|---|---|
| No autenticado | 401 | `UNAUTHENTICATED` |
| Sin permisos | 403 | `FORBIDDEN` |
| No encontrado | 404 | `NOT_FOUND` |
| Metodo no permitido | 405 | `METHOD_NOT_ALLOWED` |
| Validacion | 422 | `VALIDATION_ERROR` |
| Demasiadas peticiones | 429 | `TOO_MANY_REQUESTS` |
| Error interno | 500 | `SERVER_ERROR` |

## Exito

- **Lecturas**: `{ "data": ... }` (via `JsonResource`; los listados agregan `links` y `meta`).
- **Escrituras**: `{ "message": "...", "data": {...} }`.
- **Eliminaciones**: `{ "message": "..." }` (HTTP 200).

## Paginacion

Los listados devuelven `data` + `meta` (`current_page`, `last_page`, `per_page`, `total`).

- `?per_page=N` en todos los listados. Por defecto **15** (reportes: 10), tope **100**.
- `GET /api/auth/equipos/empresa/{id}?page=1&per_page=50`

## Convenciones

- Nombres en **`snake_case`** (alineados con la base de datos); se mantiene por compatibilidad.
- Fechas en ISO-8601 (`YYYY-MM-DD` para fechas).
- Multi-tenant: el rol `Cliente` (o empresa tipo `cliente`) solo accede a su empresa.

## Endpoints principales

Prefijo `/api/auth` (salvo indicacion), todos requieren token `auth:api`.

| Recurso | Rutas |
|---|---|
| Sesion | `POST /login` (publico), `POST /logout`, `POST /refresh`, `POST /me` |
| Password | `POST /forgot-password`, `POST /reset-password`, `POST /validate-token` (publicos) |
| Dashboard | `GET /dashboard/admin`, `GET /dashboard/cliente` |
| Usuarios | `apiResource /users`, `GET /users/empresa/{id}`, `POST /register` (Super-Admin) |
| Clientes | `apiResource /clientes`, `GET\|POST /clientes/{id}/tecnicos`, `DELETE /clientes/{id}/tecnicos/{userId}` |
| Sedes | `apiResource /sedes` |
| Roles | `apiResource /roles` (Super-Admin) |
| Equipos | `apiResource /equipos`, `GET /equipos/empresa/{id}`, `GET /equipos/{id}/hoja-vida` |
| Hoja de vida | `GET /equipos/{id}/hoja-vida-detalle`, `PUT /equipos/{id}/hoja-vida`, `POST /equipos/{id}/hoja-vida/firma`, `POST /equipos/{id}/hoja-vida/imagen` |
| Reportes | `apiResource /reportes`, `GET /reportes/equipo/{id}`, `PATCH /reportes/{id}/firma-tecnico`, `PATCH /reportes/{id}/firma-cliente` |
| Tickets | `apiResource /tickets`, `GET /tickets/empresa/{id}`, `GET /tickets/tecnico/{id}`, `PATCH /tickets/{id}/estado` |
| Cronogramas | `apiResource /cronogramas`, `GET /cronogramas/calendario`, `GET /cronogramas/empresa/{id}/anual`, `GET /cronogramas/empresa\|equipo\|tecnico/{id}`, `POST /cronogramas/generacion` |
| Listas | `GET /lista/{departamentos,municipios/{dep},clientes,sedes/{empresa},accesorios,consumibles,tipos-equipos,roles,permisos,tecnicos,empresas,clasificaciones-biomedicas}` |
| Mi empresa | `GET /mi-empresa/{usuarios,sedes,equipos,info}` |
| Importacion | `POST /importaciones` |
| Logs | `POST /logs/client` (throttle 30/min) |
| Proxy imagenes | `GET /api/imagen-proxy?url=...` (publico, sin prefijo `auth`) |

### Rutas deprecadas (se mantienen por compatibilidad)

- `POST /api/auth/importar` (usar `/importaciones`)
- `POST /api/auth/cronogramas/generar` (usar `/cronogramas/generacion`)

## Sesion / tokens

- Login: `POST /api/auth/login` -> `access_token`, `token_type`, `expires_in`, `user`, `empresa_tipo`, `empresa_id`.
- Throttle de login: 5/min por cuenta (email+IP) y 20/min por IP.
- Ante 401 de un endpoint protegido, el frontend limpia la sesion y redirige al login.
- Logout blacklistea el token (JWT).

## Proxy de imagenes

`GET /api/imagen-proxy?url=...` (publico). Solo sirve archivos del almacenamiento propio
(`storage/app/public`, `public/`) o de hosts permitidos en `config/imagen_proxy.php`.
Rechaza path traversal y hosts no autorizados (404).
