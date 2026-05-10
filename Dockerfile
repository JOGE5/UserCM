FROM php:8.2-fpm

# Dependencias del sistema necesarias para las extensiones PHP
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
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar GD con soporte para JPEG y FreeType
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instalar las extensiones PHP (ahora sí, con las libs disponibles)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Ajustar permisos iniciales de forma preventiva
# RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]
