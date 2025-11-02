# API de Recuperación de Contraseña

## Endpoints

### 1. Solicitar enlace de recuperación
**POST** `/api/auth/forgot-password`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "email": "usuario@ejemplo.com"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Si el email está registrado, recibirás un enlace de recuperación.",
    "status": "success"
}
```

**Nota de seguridad**: El endpoint siempre retorna el mismo mensaje exitoso, independientemente de si el email existe o no. Esto previene que atacantes puedan identificar emails válidos registrados en el sistema.

---

### 2. Validar token de recuperación
**POST** `/api/auth/validate-token`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "token": "token_del_email",
    "email": "usuario@ejemplo.com"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Token válido.",
    "status": "success"
}
```

**Respuesta de error (400):**
```json
{
    "message": "Token no válido o expirado.",
    "status": "error"
}
```

**Nota de seguridad**: Los mensajes de error son genéricos para no revelar información específica sobre la existencia de usuarios.

---

### 3. Restablecer contraseña
**POST** `/api/auth/reset-password`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "token": "token_del_email",
    "email": "usuario@ejemplo.com",
    "password": "nueva_contraseña",
    "password_confirmation": "nueva_contraseña"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Contraseña restablecida exitosamente.",
    "status": "success"
}
```

**Respuesta de error (400):**
```json
{
    "message": "Token de recuperación inválido o expirado.",
    "status": "error"
}
```

**Nota de seguridad**: Los mensajes de error son genéricos y no revelan si el problema es el token, el email, o si el usuario existe.

## Configuración requerida

### Variables de entorno (.env)

**Para desarrollo local con Mailpit:**
```bash
# Configuración de correo para Mailpit (desarrollo)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@cst.local"
MAIL_FROM_NAME="CST Sistema"

# URL del frontend Angular
APP_FRONTEND_URL=http://localhost:4200
```

**Para producción con Gmail:**
```bash
# Configuración de correo para Gmail (producción)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña_de_aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@cst.com"
MAIL_FROM_NAME="CST Sistema"

# URL del frontend Angular (producción)
APP_FRONTEND_URL=https://tu-dominio.com
```

### Configuración de Mailpit (Desarrollo)
1. **Instalación**: Mailpit viene incluido en Laragon por defecto
2. **Acceso**: http://localhost:8025 para ver los emails
3. **Puerto SMTP**: 1025 (sin autenticación)
4. **Ventajas**: 
   - No necesita configuración de email real
   - Todos los emails se capturan localmente
   - Interfaz web para ver emails enviados

### Configuración de Gmail (Producción)
1. Habilitar verificación en 2 pasos
2. Generar contraseña de aplicación
3. Usar la contraseña de aplicación en `MAIL_PASSWORD`

## Flujo de recuperación de contraseña

1. **Usuario solicita recuperación**: Envía email a `/forgot-password`
2. **Sistema envía email**: Con token y enlace de recuperación **al frontend Angular**
3. **Usuario hace clic en enlace**: Va a la página del frontend Angular
4. **Frontend valida token**: Llama a `/validate-token` (API backend)
5. **Usuario ingresa nueva contraseña**: Frontend envía datos a `/reset-password` (API backend)
6. **Sistema actualiza contraseña**: Y confirma el cambio

### Configuración del Frontend

El sistema está configurado para enviar enlaces al frontend Angular. La URL se construye de la siguiente manera:

**Formato del enlace:**
```
{APP_FRONTEND_URL}/auth/reset-password?token={token}&email={email}
```

**Ejemplo de desarrollo:**
```
http://localhost:4200/auth/reset-password?token=abc123&email=user@example.com
```

**Ejemplo de producción:**
```
https://tu-dominio.com/auth/reset-password?token=abc123&email=user@example.com
```

**El frontend Angular debe:**
1. Recibir los parámetros `token` y `email` de la URL
2. Mostrar un formulario para la nueva contraseña
3. Validar el token llamando a `POST /api/auth/validate-token`
4. Enviar la nueva contraseña a `POST /api/auth/reset-password`

## Seguridad

### Protección contra enumeración de usuarios
- **Respuestas consistentes**: Todos los endpoints retornan mensajes genéricos que no revelan si un email existe en el sistema
- **Sin validación de existencia**: Los requests no validan si el email existe durante la validación inicial
- **Mensajes unificados**: Errores de token inválido, usuario inexistente o token expirado retornan el mismo mensaje

### Medidas de seguridad implementadas
- **Rate limiting**: Máximo 5 intentos por minuto para recuperación y reset
- **Token expiration**: Los tokens expiran en 60 minutos  
- **Token único**: Cada token se puede usar solo una vez
- **Hash seguro**: Los tokens se almacenan hasheados en la base de datos
- **Validación robusta**: Verificación de formato de email sin revelar existencia
- **Logging seguro**: No se logean emails o tokens en logs de error

## Throttling configurado

- `forgot-password`: 5 intentos por minuto
- `reset-password`: 5 intentos por minuto  
- `validate-token`: 10 intentos por minuto
