FROM php:8.2-cli-alpine

RUN apk add --no-cache libpq-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 10000

CMD ["/bin/sh", "/start.sh"]
