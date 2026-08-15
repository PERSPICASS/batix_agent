FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY public ./public
COPY vite.config.js tsconfig.json ./
RUN npm run build

FROM composer:2.8 AS vendor
WORKDIR /app
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.4-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev libonig-dev libcurl4-openssl-dev unzip \
    && docker-php-ext-install pdo_pgsql pcntl mbstring bcmath curl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && cp -a /var/www/html/public /opt/batix-public \
    && chown -R www-data:www-data storage bootstrap/cache /var/www/html/public /opt/batix-public
COPY docker/app/entrypoint.sh /usr/local/bin/batix-entrypoint
RUN chmod +x /usr/local/bin/batix-entrypoint
USER www-data
ENTRYPOINT ["batix-entrypoint"]
CMD ["php-fpm"]
