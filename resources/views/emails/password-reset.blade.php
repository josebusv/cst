<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restablecimiento de Contraseña - {{ config('app.name') }}</title>
    <div style="display:none; font-size:1px; color:#f4f4f7; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;">
        Solicitaste un restablecimiento de contraseña. Haz clic en el botón a continuación para continuar.
    </div>
</head>
<body bgcolor="#f4f4f7" style="margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f4f7">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="max-width: 600px; border-radius: 8px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 40px; font-family: Arial, sans-serif; color: #333333;">

                            <h1 style="font-size: 24px; color: #1e40af; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eeeeee; padding-bottom: 15px;">
                                🔑 Restablecimiento de Contraseña
                            </h1>

                            <p style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                                Hola <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="font-size: 16px; line-height: 1.5; margin-bottom: 30px;">
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta. Haz clic en el siguiente botón para establecer una nueva contraseña:
                            </p>

                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto 30px auto;">
                                <tr>
                                    <td align="center" bgcolor="#2563eb" style="border-radius: 6px; padding: 12px 25px;">
                                        <a href="{{ $url }}" target="_blank" style="font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; display: inline-block; font-family: Arial, sans-serif;">
                                            Restablecer Contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #555555; margin-bottom: 15px; text-align: center;">
                                Este enlace expirará en <strong>{{ $expiration }} minutos</strong>.
                            </p>

                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0; padding: 10px 20px; border-left: 4px solid #f97316; background-color: #fef3c7; width: 100%;">
                                <tr>
                                    <td>
                                        <p style="font-size: 14px; line-height: 1.5; color: #9a3412; margin: 0;">
                                            <strong>¿No solicitaste este cambio?</strong> Puedes ignorar este email. Tu contraseña actual no se cambiará.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; line-height: 1.5; margin-top: 30px;">
                                Si tienes problemas con el botón, copia y pega este enlace en tu navegador:
                            </p>
                            <p style="font-size: 14px; line-height: 1.5;">
                                <a href="{{ $url }}" target="_blank" style="color: #2563eb; text-decoration: underline;">
                                    {{ $url }}
                                </a>
                            </p>

                            <p style="font-size: 16px; line-height: 1.5; margin-top: 40px;">
                                Saludos,<br>
                                <strong>El Equipo de {{ config('app.name') }}</strong>
                            </p>

                            <hr style="border: none; border-top: 1px solid #eeeeee; margin-top: 30px; margin-bottom: 15px;">

                            <p style="font-size: 12px; color: #999999; text-align: center; margin-top: 15px;">
                                Recibiste este correo electrónico porque se solicitó un restablecimiento de contraseña para la cuenta <strong>{{ $user->email }}</strong> en {{ config('app.name') }}.
                            </p>

                        </td>
                    </tr>
                </table>
                </td>
        </tr>
    </table>
    </body>
</html>
