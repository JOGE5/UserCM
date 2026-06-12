FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    libicu-dev libjpeg-dev libfreetype6-dev zip unzip supervisor \
    default-mysql-client \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN printf 'VITE_REVERB_APP_KEY=campus-market-key\nVITE_REVERB_HOST=campus-market.lat\nVITE_REVERB_PORT=443\nVITE_REVERB_SCHEME=https\nVITE_APP_NAME="Campus Market"\n' > .env

RUN npm install --legacy-peer-deps && npm run build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]