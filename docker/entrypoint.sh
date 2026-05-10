#!/bin/bash
set -e

# Asegurar que el script se ejecuta en /var/www/html
cd /var/www/html

# Dar permisos a storage y bootstrap/cache (solucionar problemas de host en Windows)
echo "Ajustando permisos de storage y bootstrap/cache..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

echo "Esperando a que MySQL inicie..."
while ! mysqladmin ping -h"mysql" -u"campus_user" -p"campus_password" --skip-ssl --connect-timeout=3 --silent 2>/dev/null; do
    sleep 2
done

if [ ! -d "vendor" ]; then
    echo "Instalando dependencias de Composer..."
    composer install --no-interaction --no-progress
fi

if [ -z "$(grep APP_KEY .env.docker | cut -d '=' -f2)" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --no-interaction --env=docker || true
fi

# Copiamos el env docker si no existe el env local (por si acaso el dev lo levanta asi)
if [ ! -f ".env" ]; then
    cp .env.docker .env
fi

echo "Ejecutando migraciones y seeders..."
php artisan migrate --force
php artisan db:seed --force

echo "Creando enlace simbólico de storage..."
php artisan storage:link || true

echo "Iniciando PHP-FPM..."
exec "$@"
