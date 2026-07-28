FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates \
        libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql zip gd bcmath pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --no-scripts \
        --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Dummy values only for asset generation during image build
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:dGVtcG9yYXJ5LWtleS1mb3ItYnVpbGQtb25seTEyMzQ1Ng== \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    SESSION_DRIVER=array \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && php artisan wayfinder:generate --with-form \
    && npm run build \
    && rm -rf node_modules \
    && chmod +x /var/www/html/docker/start.sh

EXPOSE 8000

CMD ["/var/www/html/docker/start.sh"]
