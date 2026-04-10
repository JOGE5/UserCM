<!DOCTYPE html>
<html>
<head>
    <title>Código de Recuperación de Contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #333333; text-align: center;">Recuperación de Contraseña</h2>
        <p style="color: #555555; font-size: 16px;">Has solicitado restablecer tu contraseña. Utiliza el siguiente código alfanumérico para continuar con el proceso:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="display: inline-block; padding: 15px 25px; font-size: 24px; font-weight: bold; background-color: #4f46e5; color: #ffffff; border-radius: 8px; letter-spacing: 5px;">
                {{ $code }}
            </span>
        </div>
        
        <p style="color: #555555; text-align: center; font-size: 14px;">Este código expirará en 15 minutos.</p>
        <p style="color: #999999; font-size: 12px; text-align: center; margin-top: 40px;">Si no solicitaste este código, puedes ignorar este correo con seguridad.</p>
    </div>
</body>
</html>
