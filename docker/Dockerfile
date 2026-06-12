FROM php:8.2-fpm

# Instalar Composer copiándolo desde composer:latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20 y dependencias del sistema
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y \
    nodejs \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring xml bcmath zip exif

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar todo el proyecto
COPY . .

# Correr composer install y npm install/build
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Ajustar permisos para carpetas de storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache
