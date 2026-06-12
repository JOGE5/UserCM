FROM php:8.3-fpm

# Instalar dependencias del sistema y Node.js para Vite
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    supervisor \
    default-mysql-client \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar todo el código fuente de la aplicación
COPY . .

# Instalar dependencias de PHP (sin dev para producción)
RUN composer install --optimize-autoloader --no-dev

# Compilar assets de frontend (Vite -> public/build)
RUN npm install --legacy-peer-deps && npm run build

# Permisos para storage y cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Configurar Supervisor para manejar PHP-FPM y Reverb (WebSockets)
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer puertos (9000 para PHP-FPM, 8080 para Reverb)
EXPOSE 9000 8080

# Iniciar Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
