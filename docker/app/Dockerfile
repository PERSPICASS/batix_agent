FROM composer:2.8 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.4-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev libonig-dev libcurl4-openssl-dev unzip \
    && docker-php-ext-install pdo_pgsql pcntl mbstring bcmath curl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php-fpm"]
