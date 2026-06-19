<x-mail::message>
# ¡Bienvenido a Campus Market!

Hola **{{ $user->name }}**,

Un administrador ha creado una cuenta para ti en la plataforma **Campus Market**.

Tus credenciales de acceso son las siguientes:

- **Correo electrónico:** {{ $user->email }}
- **Contraseña temporal:** `{{ $password }}`

Por razones de seguridad, el sistema te pedirá que cambies tu contraseña la primera vez que inicies sesión.

<x-mail::button :url="url('/login')">
Iniciar Sesión
</x-mail::button>

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
