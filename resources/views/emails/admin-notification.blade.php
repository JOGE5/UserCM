<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #10b981; margin: 0;">Campus Market</h1>
        </div>
        
        <h2 style="color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">{{ $titulo }}</h2>
        
        <div style="color: #4b5563; font-size: 16px; line-height: 1.5; margin-top: 20px; white-space: pre-wrap;">
            {{ $mensaje }}
        </div>
        
        @if($imagen)
            <div style="margin-top: 30px; text-align: center;">
                <img src="{{ $message->embed(storage_path('app/public/' . $imagen)) }}" alt="Imagen adjunta" style="max-width: 100%; border-radius: 8px;">
            </div>
        @endif
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
            <p>Este es un correo automático de Campus Market. Por favor no respondas a esta dirección.</p>
        </div>
    </div>
</body>
</html>
