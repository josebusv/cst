<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restablecimiento de Contraseña - {{ config('app.name') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, p, a { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; -webkit-text-size-adjust: 100%;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <div style="width: 120px; display: inline-block;">
                                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="width: 100%; height: auto; display: block;">
                            </div>
                            <h1 style="margin: 24px 0 0 0; font-size: 24px; font-weight: 700; color: #111827; text-align: center;">
                                Restablecer Contraseña
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 20px 40px 40px 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6; color: #374151;">
                                Hola <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #4b5563;">
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>{{ config('app.name') }}</strong>. No te preocupes, es un proceso sencillo.
                            </p>

                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <a href="{{ $url }}" target="_blank" style="background-color: #2563eb; color: #ffffff; padding: 14px 32px; font-size: 16px; font-weight: 600; text-decoration: none; border-radius: 8px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                                            Cambiar Contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiration Info -->
                            <p style="margin: 0 0 24px 0; font-size: 14px; color: #6b7280; text-align: center;">
                                Este enlace es válido por <strong>{{ $expiration }} minutos</strong> por motivos de seguridad.
                            </p>

                            <!-- Security Notice -->
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 4px; margin-bottom: 30px;">
                                <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #92400e;">
                                    <strong>¿No solicitaste este cambio?</strong> Si no fuiste tú, puedes ignorar este mensaje de forma segura. Tu contraseña actual permanecerá sin cambios.
                                </p>
                            </div>

                            <!-- Link Fallback -->
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #9ca3af;">
                                Si tienes problemas con el botón, copia y pega esta dirección en tu navegador:
                            </p>
                            <p style="margin: 0; font-size: 13px; word-break: break-all;">
                                <a href="{{ $url }}" target="_blank" style="color: #2563eb; text-decoration: underline;">
                                    {{ $url }}
                                </a>
                            </p>

                            <p style="margin: 40px 0 0 0; font-size: 15px; line-height: 1.6; color: #4b5563;">
                                Saludos,<br>
                                <strong>El equipo de {{ config('app.name') }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f9fafb; border-top: 1px solid #f3f4f6;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af; text-align: center;">
                                Has recibido este correo porque se solicitó un cambio de contraseña para la cuenta <strong>{{ $user->email }}</strong>.
                                <br><br>
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
