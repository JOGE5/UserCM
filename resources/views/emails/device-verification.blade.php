<!DOCTYPE html>
<html>
<head>
    <title>Código de Verificación de Dispositivo</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #111111; color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
        <h2 style="color: #4f46e5; text-align: center;">Acceso desde un dispositivo nuevo</h2>
        <p style="color: #dddddd; font-size: 16px;">Hemos detectado un intento de inicio de sesión desde un dispositivo o navegador no reconocido. Por tu seguridad, por favor autoriza este acceso con el siguiente código numérico de 6 dígitos:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="display: inline-block; padding: 15px 30px; font-size: 32px; font-weight: bold; background-color: #222222; border: 1px solid #4f46e5; color: #ffffff; border-radius: 8px; letter-spacing: 8px;">
                {{ $code }}
            </span>
        </div>
        
        <div style="background-color: #1a1a1a; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="margin: 0; color: #aaaaaa; font-size: 14px;"><strong>Dispositivo o Navegador:</strong> {{ $deviceInfo }}</p>
        </div>

        <p style="color: #dddddd; text-align: center; font-size: 14px;">Este código expirará en 10 minutos.</p>
        <p style="color: #777777; font-size: 12px; text-align: center; margin-top: 40px;">Si tú no intentaste iniciar sesión en Campus Market, te recomendamos que cambies tu contraseña inmediatamente.</p>
    </div>
</body>
</html>
