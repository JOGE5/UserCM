<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>¡Vuelve a CampusMarket!</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 20px; color: #18181b; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #4f46e5; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .content { padding: 30px; }
        .content p { font-size: 16px; line-height: 1.6; color: #3f3f46; margin-bottom: 20px; }
        .products { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .product { border: 1px solid #e4e4e7; border-radius: 8px; padding: 15px; text-align: center; }
        .product h3 { font-size: 14px; margin: 0 0 10px 0; color: #18181b; }
        .product p { font-size: 16px; font-weight: bold; color: #4f46e5; margin: 0; }
        .btn-wrapper { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; padding: 14px 28px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .footer { background-color: #18181b; color: #a1a1aa; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Hola {{ $userName }}, te extrañamos!</h1>
        </div>
        <div class="content">
            <p>Hemos notado que llevas un tiempo sin visitar <strong>CampusMarket</strong>. La comunidad universitaria extraña a usuarios estrella como tú.</p>
            <p>Mientras no estabas, han publicado artículos geniales que creemos que podrían interesarte para tu carrera:</p>
            
            @if(count($products) > 0)
                <div class="products">
                    @foreach($products as $product)
                        <div class="product">
                            <h3>{{ $product->Titulo_Publicacion }}</h3>
                            <p>${{ number_format($product->Precio_Publicacion, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="btn-wrapper">
                <a href="{{ url('/') }}" class="btn">Explorar CampusMarket</a>
            </div>
        </div>
        <div class="footer">
            <p>CampusMarket - La plataforma de compraventa universitaria exclusiva.</p>
        </div>
    </div>
</body>
</html>
