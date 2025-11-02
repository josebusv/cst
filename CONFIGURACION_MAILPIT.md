# Configuración de Mailpit para Laravel

## Variables de entorno para desarrollo

Agrega estas líneas a tu archivo `.env` para usar Mailpit en desarrollo:

```bash
# Configuración de correo para Mailpit (desarrollo local)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@cst.local"
MAIL_FROM_NAME="CST Sistema"

# URL del frontend Angular para desarrollo
APP_FRONTEND_URL=http://localhost:4200
```

## Cómo usar Mailpit

### 1. Verificar que Mailpit esté ejecutándose en Laragon
- Mailpit viene incluido en Laragon y se inicia automáticamente
- Puerto SMTP: `1025`
- Puerto Web: `8025`

### 2. Acceder a la interfaz web
- URL: http://localhost:8025
- Aquí verás todos los emails que envíe tu aplicación

### 3. Probar el envío de emails
Ejecuta este comando para probar:

```bash
php artisan tinker
```

Luego en el tinker:
```php
use App\Models\User;
use App\Notifications\CustomResetPassword;

$user = User::first();
$user->notify(new CustomResetPassword('test-token-123'));
```

### 4. Verificar el email
- Ve a http://localhost:8025
- Deberías ver el email de recuperación de contraseña
- El enlace apuntará a: `http://localhost:4200/auth/reset-password?token=test-token-123&email=...`

## Ventajas de Mailpit para desarrollo

✅ **Sin configuración**: No necesitas cuentas de email reales  
✅ **Captura todo**: Todos los emails se guardan localmente  
✅ **Interfaz amigable**: Ve el HTML y texto plano  
✅ **Sin límites**: No hay restricciones de envío  
✅ **Debugging**: Perfecto para probar flujos de email

## Para producción

Cuando despliegues a producción, cambia a un proveedor real como Gmail:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña_de_aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@cst.com"
MAIL_FROM_NAME="CST Sistema"

APP_FRONTEND_URL=https://tu-dominio.com
```
