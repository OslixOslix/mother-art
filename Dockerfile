FROM php:8.4-cli-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_sqlite intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

COPY --from=vendor /app/vendor ./vendor

RUN npm run build

FROM base AS runtime

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        storage/app/import-artworks \
        bootstrap/cache \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade --ansi

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["/bin/sh", "-c", "exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
