<x-mail::message>
# ¡Logrado! Bienvenido a Campus Market, {{ $user->name }}

Estamos muy felices de que te unas a nuestra comunidad. Hemos creado exitosamente tu perfil.

En Campus Market podrás encontrar productos estudiantiles de forma fácil, segura y rápida. Al entrar a tu cuenta, te recomendamos revisar el foro de publicaciones y explorar lo que los demás tienen para ti o animarte a publicar tus propios productos.

<x-mail::button :url="config('app.url') . '/dashboard'">
Ingresar a mi Cuenta
</x-mail::button>

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
</x-mail::message>
