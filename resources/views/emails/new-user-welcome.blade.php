<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenido a Campus Market</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #10b981; margin: 0;">Campus Market</h1>
        </div>
        
        <h2 style="color: #111827;">¡Hola, {{ $nombre }}!</h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Bienvenido a Campus Market. Un administrador ha creado una cuenta para ti.
        </p>
        
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 25px 0;">
            <h3 style="margin-top: 0; color: #374151;">Tus credenciales de acceso:</h3>
            <p style="margin-bottom: 5px; color: #4b5563;"><strong>Email:</strong> {{ $email }}</p>
            <p style="margin-top: 0; color: #4b5563;"><strong>Contraseña temporal:</strong> <span style="background-color: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ $password }}</span></p>
        </div>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Te recomendamos iniciar sesión y cambiar tu contraseña lo antes posible desde los ajustes de tu perfil.
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/login') }}" style="background-color: #10b981; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                Iniciar Sesión
            </a>
        </div>
    </div>
</body>
</html>
