# Campus Market — E-Commerce Universitario

Plataforma web para compra, venta, intercambio y foros de discusión diseñada para la comunidad universitaria (UNIFRANZ).

## Arquitectura

       INTERNET
           │
┌──────────▼──────────┐
│      FRONTEND       │
│    Vue 3 + Inertia  │
│   NGINX puerto 80   │
│   campusmarket.com  │
└──────────┬──────────┘
           │ HTTPS / WebSockets
┌──────────▼──────────┐
│      BACKEND        │
│    Laravel 12       │
│  PHP-FPM + Reverb   │
└──────────┬──────────┘
           │ MySQL
┌──────────▼──────────┐
│      DATABASE       │
│     MySQL 8.0       │
└─────────────────────┘

## URLs de producción

- **Aplicación web:** `https://[tu-dominio].com`
- **Servidor WebSockets (Reverb):** `wss://[tu-dominio].com:8080`

## Requisitos para entorno local

- PHP 8.2+
- Node.js 18+
- Composer
- MySQL 8.0 / MariaDB

## Levantar en local

```bash
git clone https://github.com/[usuario]/[repo].git
cd UserCM
cp .env.example .env

# Instalar dependencias
composer install
npm install

# Generar llave
php artisan key:generate

# Configurar base de datos en .env y luego ejecutar:
php artisan migrate:fresh --seed

# Compilar frontend
npm run dev

# Levantar backend y websockets (en terminales separadas)
php artisan serve
php artisan reverb:start
```

Aplicación disponible en: `http://localhost:8000`

## Usuarios de prueba (Seeder)

| Rol | Email | Contraseña |
|-----|-------|------------|
| SuperAdministrador | agustinapaza1817@gmail.com | password123 |
| Estudiante | (Crear nueva o Login con Google) | N/A |

*Nota: Una vez inicies sesión, puedes configurar el Google Authenticator desde el Perfil para activar el 2FA.*

## Versiones

| Tag | Estado | Descripción |
|-----|--------|-------------|
| v1.2.0 | ✅ Producción | Seguridad robusta: TOTP, Google Auth, Correcciones CSRF, Listos para VPS |
| v1.1.0 | ✅ Estable | WebSockets (Reverb), Chat y Gamificación / XP de usuarios |
| v1.0.0 | ✅ Estable | Publicaciones, Catálogo de productos y Foros base |

Para desplegar en VPS (Hostinger):
```bash
git checkout v1.2.0
# Instalar dependencias de prod y optimizar
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Checklist de funcionalidades

- [x] Registro de usuario y Asistente de Perfil.
- [x] Login seguro (Email/Password y Laravel Fortify).
- [x] Verificación en 2 pasos (TOTP / Google Authenticator).
- [x] Opción "Confiar en este dispositivo" (7 días sin pedir TOTP).
- [x] Login con cuenta de Google (Socialite).
- [x] Roles y permisos: SuperAdministrador, Moderador, Estudiante.
- [x] Módulo de Publicaciones (compras, ventas, estado de producto).
- [x] Foros Universitarios (Salas abiertas y exclusivas por carrera).
- [x] Chat en tiempo real con Laravel Reverb (WebSockets).
- [x] Sistema de gamificación y reputación entre usuarios.
- [x] Reportes y moderación automatizada.
- [x] Preparado para deploy en VPS con HTTPS y dominio.

## Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Frontend | Vue 3, Inertia.js, Tailwind CSS |
| Backend | Laravel 12, PHP 8+ |
| Base de datos | MySQL 8.0 |
| Real-time | Laravel Reverb |
| Autenticación | Fortify + Socialite + TOTP 2FA |
| Deploy | VPS Hostinger, NGINX / Apache |
