# Contrato de API (respuestas, errores y paginación)

Todas las rutas cuelgan de `/api`. Salvo `imagen-proxy`, el resto vive bajo el prefijo `/api/auth`.

## Códigos de error

| Situación | HTTP | Cuerpo |
|---|---|---|
| No autenticado (sin token / expirado) | 401 | `{ "message": "No autenticado." }` |
| Sin permisos | 403 | `{ "message": "No tienes los permisos necesarios para acceder a este recurso." }` |
| Validación | 422 | `{ "message": "Datos inválidos", "errors": { "campo": ["..."] } }` |
| No encontrado | 404 | `{ "message": "Recurso no encontrado" }` |
| Método no permitido | 405 | `{ "message": "Método HTTP no permitido" }` |
| Error interno | 500 | `{ "message": "Error interno del servidor" }` (sin detalle con `APP_DEBUG=false`) |

El detalle interno nunca se expone al cliente en producción; los 500 se registran con `report()`.

## Paginación

Los listados devuelven `data` + `meta` (`current_page`, `last_page`, `per_page`, `total`).

Ejemplo: `GET /api/auth/equipos/empresa/{id}?page=1&per_page=50` (tope de `per_page`: 100).

## Sesión / tokens

- Login: `POST /api/auth/login` → `access_token`, `token_type`, `expires_in`, `user`, `empresa_tipo`, `empresa_id`.
- Ante un 401 de un endpoint protegido, el frontend limpia la sesión y redirige al login (`TokenExpirationInterceptor`).
- Logout: `POST /api/auth/logout` invalida/blacklistea el token.

## Proxy de imágenes

`GET /api/imagen-proxy?url=...` (público). Solo sirve archivos del almacenamiento propio
(`storage/app/public`, `public/`) o de hosts permitidos en `config/imagen_proxy.php`.
Rechaza path traversal y hosts no autorizados (404).
