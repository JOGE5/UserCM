#!/bin/bash
set -e

echo "🚀 Iniciando despliegue en Producción..."

# 1. Traer los últimos cambios de la rama dock
echo "⬇️ Descargando cambios desde GitHub..."
git pull origin dock

# 2. Instalar dependencias de PHP (sin paquetes de desarrollo)
echo "📦 Instalando dependencias de Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 3. Limpiar y cachear configuraciones para máxima velocidad
echo "⚡ Optimizando Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 4. Ejecutar migraciones (si hay nuevas tablas)
echo "🗄️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 5. Compilar assets de frontend (Vite/Vue)
echo "🎨 Compilando Assets (Vite / Tailwind / Vue)..."
npm install
npm run build

# 6. Reiniciar colas (para que tomen los nuevos cambios de código)
echo "🔄 Reiniciando workers de colas..."
php artisan queue:restart

echo "✅ ¡Despliegue completado con éxito!"
